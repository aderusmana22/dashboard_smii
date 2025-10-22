<?php

namespace App\Http\Controllers\PPIC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MPSController extends Controller
{
    private function httpHeader($req)
    {
        return array(
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: ""',        // jika tidak pakai SOAPAction, isinya harus ada tanda petik 2 --> ""
            'Content-length: ' . strlen(preg_replace("/\s+/", " ", $req))
        );
    }

    public function getMPS()
    {
        // Step 1: Fetch data from QAD server (getItemAutoNaim)
        $qxUrl = 'http://smii.qad:25079/wsa/smiiwsa';
        $timeout = 10;
        $domain = 'SMII';
        // Always use start of month until today
        $now = Carbon::now();
        $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $now->format('Y-m-d');
        $qdocRequest =
            '<Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                <Body>
                    <getItemAutoNaim xmlns="urn:services-qad-com:smiiwsa:0001:smiiwsa">
                        <ipDomain>' . $domain . '</ipDomain>
                        <ipStartDate>' . $startDate . '</ipStartDate>
                        <ipEndDate>' . $endDate . '</ipEndDate>
                    </getItemAutoNaim>
                </Body>
            </Envelope>';

        $curlOptions = array(
            CURLOPT_URL => $qxUrl,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout + 5,
            CURLOPT_HTTPHEADER => $this->httpHeader($qdocRequest),
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
            return 1;
        }

        if (!$qdocResponse) {
            Log::error('Tidak ada respons dari server.');
            return 1;
        }

        $xmlResp = simplexml_load_string($qdocResponse);
        $xmlResp->registerXPathNamespace('ns', 'urn:services-qad-com:smiiwsa:0001:smiiwsa');

        $items = $xmlResp->xpath('//ns:getItemAutoNaimResponse/ns:ttItemSummary/ns:ttItemSummaryRow');
        if (!$items) {
            Log::error('Tidak ada data ttItemSummaryRow pada response.');
            return 1;
        }

        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'Item Number' => (string) $item->pt_part,
                'Description' => (string) $item->pt_desc1,
                'UOM' => (string) $item->pt_um,
                'Net Weight' => (string) $item->pt_net_wt,
                'Month' => (string) $item->bulan,
                'Year' => (string) $item->tahun,
                'Inventory Qty' => (string) $item->inventory_qty,
                'Dispatch Qty' => (string) $item->dispatch_qty,
                'Allocated Qty' => (string) $item->allocated_qty,
                'SO Outstanding' => (string) $item->so_outstanding,
                'MPS Qty' => (string) $item->mps_qty,
            ];
        }

        // Step 2: Log the parsed data
        Log::info('MPS Data:', $data);
        return 0;
    }
}
