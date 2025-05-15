<?php

namespace App\Jobs;

use App\Models\QAD\Sales;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSalesDashboardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $startDate, $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function handle()
    {
        $qxUrl = 'http://smii.qad:25079/wsa/smiiwsa';
        $timeout = 10;
        $domain = 'SMII';
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $totalNewItems = 0;
        $batchSize = 1000;
        $offset = 0;
        $hasMore = true;

        while ($hasMore) {
            $qdocRequest =
                '<Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                <Body>
                    <getsalesdashboard xmlns="urn:services-qad-com:smiiwsa:0001:smiiwsa">
                        <ip_domain>' . $domain . '</ip_domain>
                        <ip_batch_size>' . $batchSize . '</ip_batch_size>
                        <ip_offset>' . $offset . '</ip_offset>
                        <ip_start_date>' . $startDate . '</ip_start_date>
                        <ip_end_date>' . $endDate . '</ip_end_date>
                    </getsalesdashboard>
                </Body>
            </Envelope>';

            $curlOptions = array(
                CURLOPT_URL => $qxUrl,
                CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_TIMEOUT => $timeout + 5,
                CURLOPT_HTTPHEADER => [
                    'Content-type: text/xml;charset="utf-8"',
                    'Accept: text/xml',
                    'Cache-Control: no-cache',
                    'Pragma: no-cache',
                    'SOAPAction: ""',
                    'Content-length: ' . strlen(preg_replace("/\s+/", " ", $qdocRequest))
                ],
                CURLOPT_POSTFIELDS => preg_replace("/\s+/", " ", $qdocRequest),
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            );

            $curl = curl_init();
            if ($curl) {
                curl_setopt_array($curl, $curlOptions);
                $qdocResponse = curl_exec($curl);
                curl_close($curl);
            } else {
                Log::error('Gagal menghubungi server.');
                return;
            }

            if (!$qdocResponse) {
                Log::error('Tidak ada respons dari server.');
                return;
            }

            $xmlResp = simplexml_load_string($qdocResponse);
            $xmlResp->registerXPathNamespace('ns', 'urn:services-qad-com:smiiwsa:0001:smiiwsa');

            $qdocResult = $xmlResp->xpath('//ns:getsalesdashboardResponse/ns:opOk');
            $opOk = ($qdocResult && isset($qdocResult[0])) ? (string)$qdocResult[0] : 'true';

            $invoices = $xmlResp->xpath('//ns:getsalesdashboardResponse/ns:ttTable/ns:ttTableRow');
            $jumlahItemBaru = 0;

            if ($opOk == 'true' && $invoices) {
                foreach ($invoices as $item) {
                    $tr_trnbr = (string) $item->tt_tr_trnbr;
                    $tr_effdate = (string) $item->tt_tr_effdate;
                    $tr_ton = (string) $item->tt_ton;
                    $cm_region = (string) $item->tt_cm_region;
                    $cm_rmks = (string) $item->tt_cm_rmks;
                    $code_cmmt = (string) $item->tt_code_cmmt;
                    $pt_desc1 = (string) $item->tt_pt_desc1;
                    $pt_prod_line = (string) $item->tt_pt_prod_line;
                    $pl_desc = (string) $item->tt_pl_desc;
                    $tr_addr = (string) $item->tt_tr_addr;
                    $ad_name = (string) $item->tt_ad_name;

                    // Handle multiple tt_tr_slspsn nodes
                    $tr_slspsn_nodes = $item->xpath('tt_tr_slspsn');
                    $tr_slspsn = '';
                    if ($tr_slspsn_nodes && count($tr_slspsn_nodes) > 0) {
                        // Concatenate all values, separated by comma
                        $tr_slspsn = implode(',', array_map('strval', $tr_slspsn_nodes));
                    }

                    $sales_name = (string) $item->tt_sales_name;
                    $pt_part = (string) $item->tt_pt_part;
                    $pt_draw = (string) $item->tt_pt_draw;
                    $margin = number_format((float) str_replace(',', '', str_replace('.', '', trim((string) $item->tt_margin))), 3, '.', '');
                    $value = number_format((float) str_replace(',', '', str_replace('.', '', trim((string) $item->tt_value))), 3, '.', '');

                    // Update jika tr_trnbr sudah ada, jika tidak create
                    Sales::updateOrCreate(
                        ['tr_trnbr' => $tr_trnbr],
                        [
                            'tr_effdate' => $tr_effdate,
                            'tr_ton' => $tr_ton,
                            'cm_region' => $cm_region,
                            'cm_rmks' => $cm_rmks,
                            'code_cmmt' => $code_cmmt,
                            'margin' => $margin,
                            'value' => $value,
                            'pt_desc1' => $pt_desc1,
                            'pt_prod_line' => $pt_prod_line,
                            'pl_desc' => $pl_desc,
                            'tr_addr' => $tr_addr,
                            'ad_name' => $ad_name,
                            'tr_slspsn' => $tr_slspsn,
                            'sales_name' => $sales_name,
                            'pt_part' => $pt_part,
                            'pt_draw' => $pt_draw,
                        ]
                    );
                    $jumlahItemBaru++;
                }
                $totalNewItems += $jumlahItemBaru;

                // If less than batch size, no more data
                if (count($invoices) < $batchSize) {
                    $hasMore = false;
                } else {
                    $offset += $batchSize;
                }
            } else {
                $hasMore = false;
                if ($opOk != 'true') {
                    Log::error('Gagal mengambil data dari server.');
                }
            }
        }

        Log::info('Job SalesDashboard selesai. Jumlah item baru: ' . $totalNewItems);
    }
}
