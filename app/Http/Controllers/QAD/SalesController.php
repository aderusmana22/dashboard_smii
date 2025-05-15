<?php

namespace App\Http\Controllers\QAD;

use App\Models\QAD\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessSalesDashboardJob;
use App\Models\QAD\StandardShipment;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Sales::query();
            return DataTables::of($data)->make(true);
        }
        return view('page.dataDashboard.sales-index');
    }

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

    // ============================================Sales====================================================


    public function getSalesDashboard()
    {
        $startDate = '2024-01-10';
        $endDate = '2024-05-01';
        ProcessSalesDashboardJob::dispatch($startDate, $endDate);

        session(['toastMessage' => 'Proses pengambilan data sales dashboard sedang berjalan di background.', 'toastType' => 'info']);
        return redirect()->back();
    }

    public function dashboardSales()
    {
        return \view('dashboard.dashboardSales');
    }


    // ============================================StandardShipment====================================================

    public function getShipment()
    {
        $qxUrl = 'http://smii.qad:24079/wsa/smiiwsa';
        $timeout = 10;
        $domain = 'SMII';
        $totalNewItems = 0;
        $batchSize = 2000; // Ukuran batch
        $offset = 0;
        $startDate = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d');

        $dataProcessed = false; // Tambahkan flag untuk melacak apakah ada data yang diproses

        do {
            $qdocRequest =
                '<Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                    <Body>
                        <getshipment xmlns="urn:services-qad-com:smiiwsa:0001:smiiwsa">
                            <tr_domain>' . $domain . '</tr_domain>
                            <ip_start_date>' . $startDate . '</ip_start_date>
                            <ip_end_date>' . $endDate . '</ip_end_date>
                            <ip_batch_size>' . $batchSize . '</ip_batch_size>
                            <ip_offset>' . $offset . '</ip_offset>
                        </getshipment>
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
                return redirect()->back()->with('error', 'Gagal menghubungi server.');
            }

            if (!$qdocResponse) {
                Log::error('Tidak ada respons dari server.');

                return redirect()->back()->with('error', 'Tidak ada respons dari server.');
            }

            $xmlResp = simplexml_load_string($qdocResponse);
            $xmlResp->registerXPathNamespace('ns', 'urn:services-qad-com:smiiwsa:0001:smiiwsa');

            $qdocResult = (string) $xmlResp->xpath('//ns:opOk')[0];
            Log::info('Hasil dari opOk: ' . $qdocResult);


            $invoices = $xmlResp->xpath('//ns:getshipmentResponse/ns:ttTrData/ns:ttTrDataRow');
            Log::info('Jumlah invoices: ' . count($invoices));

            $jumlahItemBaru = 0;
            $jumlahItemUpdate = 0;
            $totalUpdateItems = 0; // Inisialisasi total item yang diupdate

            if ($qdocResult == 'true') {
                foreach ($invoices as $item) {
                    $tr_effdate = $item->tr_effdate; // Ambil elemen yang sesuai
                    $tr_ton = $item->tr_ton; // Ambil elemen yang sesuai
                    $tr_trnbr = $item->tr_trnbr; // Ambil elemen tr_trnbr yang baru ditambahkan

                    // Cek apakah tr_trnbr sudah ada dalam basis data
                    $existingInvoice = StandardShipment::where('tr_trnbr', $tr_trnbr)->first();
                    if ($existingInvoice) {
                        // Jika sudah ada, maka update data
                        $existingInvoice->date_shipment = $tr_effdate;
                        $existingInvoice->ton = $tr_ton;
                        $existingInvoice->save();
                        $jumlahItemUpdate++;
                    } else {
                        // Jika tidak ada, maka create data baru
                        $newInvoice = new StandardShipment();
                        $newInvoice->date_shipment = $tr_effdate;
                        $newInvoice->ton = $tr_ton;
                        $newInvoice->tr_trnbr = $tr_trnbr; // Simpan tr_trnbr ke basis data
                        $newInvoice->save();
                        $jumlahItemBaru++;
                    }
                }
                $totalNewItems += $jumlahItemBaru;
                $totalUpdateItems += $jumlahItemUpdate;
                $dataProcessed = true; // Set flag jika ada data yang diproses
            } else {
                Log::error('Gagal mengambil data dari server. Respons: ' . $qdocResponse);
                if (!$dataProcessed) { // Hanya kembalikan jika tidak ada data yang diproses
                    session(['toastMessage' => 'Gagal mengambil data dari server.', 'toastType' => 'error']);
                    return redirect()->back();
                }
            }

            $offset += $batchSize; // Tambahkan offset untuk iterasi berikutnya
        } while ($qdocResult == 'true' && count($invoices) > 0); // Ubah kondisi untuk memastikan loop berjalan sampai semua data diambil

        if ($dataProcessed) {
            session(['toastMessage' => 'Data berhasil disimpan. Jumlah item baru: ' . $totalNewItems . ', Jumlah item yang diupdate: ' . $totalUpdateItems, 'toastType' => 'success']);
        }
        return redirect()->back();
    }

    public function shipmentindex()
    {
        $standardShipment = StandardShipment::all();
        return \view('page.standard.shipment-index', \compact('standardShipment'));
    }

    public function shipmentstore(Request $request)
    {
        $standardShipment = new StandardShipment();
        $standardShipment->date_shipment = $request->date_shipment;
        $standardShipment->ton = $request->ton;
        $standardShipment->save();
        Alert::toast('Standard Shipment Created Successfully', 'success');
        return  \view('page.standard.shipment-index');
    }

    public function shipmentupdate(Request $request, StandardShipment $standardShipment)
    {
        $standardShipment->date_shipment = $request->date_shipment;
        $standardShipment->ton = $request->ton;
        $standardShipment->save();
        Alert::toast('Standard Shipment Updated Successfully', 'success');
        return  \view('page.standard.shipment-index');
    }

    public function shipmentdelete(StandardShipment $standardShipment)
    {
        $standardShipment->delete();
        Alert::toast('Standard Shipment Deleted Successfully', 'success');
        return  \view('page.standard.shipment-index');
    }
}
