<?php

namespace App\Http\Controllers\OIL\Inventory;

use App\Http\Controllers\Controller;
use App\Models\OIL\Inventory\InventoryOilInOut;
use App\Models\OIL\Inventory\InventoryOilStock;
use App\Models\QAD\Item;
use App\Models\QAD\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class InventoryOilController extends Controller
{
    /**
     * Entry point untuk Minyak Keluar
     */
    public function OilOutOutbound(Request $request)
    {
        return $this->handleXmlProcess($request, 'OUT');
    }

    /**
     * Entry point untuk Minyak Masuk
     */
    public function OilInOutbound(Request $request)
    {
        return $this->handleXmlProcess($request, 'IN');
    }

    /**
     * Logika utama pemrosesan XML
     */
    private function handleXmlProcess(Request $request, $label)
    {
        $content = $request->getContent();
        Log::channel('invoil')->info("Received XML Content $label: " . $content);

        try {
            $xml = simplexml_load_string($content);
            if ($xml === false) {
                throw new \Exception('Failed to parse XML');
            }

            // Daftarkan semua namespace yang ada di XML SOAP
            $namespaces = $xml->getNamespaces(true);
            foreach ($namespaces as $prefix => $ns) {
                $xml->registerXPathNamespace($prefix ?: 'ns', $ns);
            }

            // Cari elemen tr_hist di mana pun dia berada dalam SOAP Body
            $datas = $xml->xpath('//qdoc:tr_hist');

            if (!$datas || count($datas) === 0) {
                throw new \Exception('No tr_hist data found in XML. Check if namespace qdoc is correct.');
            }

            foreach ($datas as $data) {
                $this->saveToDatabase($data, $namespaces);
            }

            // Setelah memproses tr_hist (IN/OUT), jalankan sinkronisasi stok minyak
            try {
                $this->getOilStock();
            } catch (\Exception $e) {
                Log::channel('invoil')->error('Auto-sync getOilStock failed: ' . $e->getMessage());
            }

            return response()->json(['message' => 'Data processed successfully'], 200);
        } catch (\Exception $e) {
            Log::channel('invoil')->error("Error processing $label: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function saveToDatabase(\SimpleXMLElement $data, array $namespaces)
    {
        // Pastikan kita mengakses elemen di bawah namespace qdoc
        $qdoc = $data->children($namespaces['qdoc']);

        // 1. Ekstraksi Data dengan Casting String dan Trim (untuk hapus spasi kosong)
        $tr_trnbr = trim((string) $qdoc->trTrnbr);
        $tr_part = trim((string) $qdoc->trPart);
        $tr_addr = trim((string) $qdoc->trAddr);
        $tr_qty_raw = trim((string) $qdoc->trQtyLoc);
        $tr_um = trim((string) $qdoc->trUm);
        $tr_effdate = trim((string) $qdoc->trEffdate);

        // 2. Format Tanggal
        $tr_date = !empty($tr_effdate) ? date('Y-m-d', strtotime($tr_effdate)) : null;

        // 3. Logika Tipe
        $type = empty($tr_addr) ? 'OUT' : 'IN';

        // 4. Lookup Supplier & Item
        $tr_addr_name = !empty($tr_addr) ? Supplier::where('vd_addr', $tr_addr)->value('vd_sort') : null;
        $tr_part_name = !empty($tr_part) ? Item::where('pt_part', $tr_part)->value('pt_desc1') : null;

        // 5. Update atau Create
        InventoryOilInOut::updateOrCreate(
            ['tr_trnbr' => $tr_trnbr],
            [
                'tr_part' => $tr_part,
                'tr_part_name' => $tr_part_name,
                'tr_addr' => !empty($tr_addr) ? $tr_addr : null,
                'tr_addr_name' => $tr_addr_name,
                'tr_date' => $tr_date,
                'tr_qty_chg' => $tr_qty_raw,
                'tr_um' => $tr_um, // Nilai KG akan masuk ke sini
                'type' => $type,
            ]
        );
    }


    public function getOilStock()
    {
        $qxUrl = 'http://smii.qad:29079/wsa/smiiwsa';
        $timeout = 10;

        $qdocRequest =
            '<Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
            <Body>
                <getInvOil xmlns="urn:services-qad-com:smiiwsa:0001:smiiwsa"/>
            </Body>
        </Envelope>';

        $curlOptions = [
            CURLOPT_URL => $qxUrl,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout + 5,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml;charset=UTF-8',
                'SOAPAction: ""'
            ],
            CURLOPT_POSTFIELDS => preg_replace("/\s+/", " ", $qdocRequest),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];

        $curl = curl_init();

        if (!$curl) {
            Log::channel('invoil')->error('Gagal inisialisasi cURL.');
            return back()->with([
                'toastMessage' => 'Gagal inisialisasi cURL.',
                'toastType' => 'error'
            ]);
        }

        curl_setopt_array($curl, $curlOptions);

        $qdocResponse = curl_exec($curl);
        $curlError = curl_error($curl);

        curl_close($curl);

        if ($curlError) {
            Log::channel('invoil')->error('cURL Error: ' . $curlError);
            return back()->with([
                'toastMessage' => 'cURL Error: ' . $curlError,
                'toastType' => 'error'
            ]);
        }

        if (!$qdocResponse) {
            Log::channel('invoil')->error('Response kosong dari QAD.');
            return back()->with([
                'toastMessage' => 'Tidak ada respons dari server QAD.',
                'toastType' => 'error'
            ]);
        }

        try {
            $xmlResp = simplexml_load_string($qdocResponse);

            if ($xmlResp === false) {
                throw new \Exception("XML tidak valid.");
            }

            // ✅ XPath aman (tidak tergantung namespace)
            $rows = $xmlResp->xpath('//*[local-name()="ttInvProdLineRow"]');

            Log::channel('invoil')->info('Total rows XML: ' . count($rows));

            if (count($rows) === 0) {
                Log::channel('invoil')->warning('Data kosong dari QAD.');
                return back()->with([
                    'toastMessage' => 'Data dari QAD kosong.',
                    'toastType' => 'warning'
                ]);
            }

            // ✅ GROUPING: aggregate per item AND per location (ld_loc)
            $grouped = [];

            foreach ($rows as $item) {
                $ld_part = trim((string) $item->ld_part);
                $ld_loc = trim((string) $item->ld_loc);

                if ($ld_part === '') {
                    continue;
                }

                $qty = (float) trim((string) $item->ld_qty_oh);

                // key includes location so totals are per-item-per-location
                $key = $ld_part . '|' . $ld_loc;

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'ld_part' => $ld_part,
                        'pt_desc1' => trim((string) $item->pt_desc1),
                        'ld_qty_oh' => $qty,
                        'pt_um' => trim((string) $item->pt_um),
                        'pt_prod_line' => trim((string) $item->pt_prod_line),
                        'ld_loc' => $ld_loc,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    $grouped[$key]['ld_qty_oh'] += $qty;
                }
            }

            if (count($grouped) === 0) {
                Log::channel('invoil')->error('Grouping gagal, tidak ada data valid.');
                return back()->with([
                    'toastMessage' => 'Grouping gagal (data kosong)',
                    'toastType' => 'error'
                ]);
            }

            // ✅ TRUNCATE AMAN (hindari FK error)
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                InventoryOilStock::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $e) {
                Log::channel('invoil')->error('Truncate gagal: ' . $e->getMessage());
            }

            // ✅ INSERT DATA (bulk biar cepat)
            try {
                InventoryOilStock::insert(array_values($grouped));
                Log::channel('invoil')->info('Insert berhasil: ' . count($grouped) . ' item.');
            } catch (\Exception $e) {
                Log::channel('invoil')->error('Insert gagal: ' . $e->getMessage());

                return back()->with([
                    'toastMessage' => 'Insert DB gagal: ' . $e->getMessage(),
                    'toastType' => 'error'
                ]);
            }

            $msg = 'Inventory Oil Stock berhasil diperbarui. Total unik: ' . count($grouped);

            Log::channel('invoil')->info($msg);

            return back()->with([
                'toastMessage' => $msg,
                'toastType' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::channel('invoil')->error('Error proses: ' . $e->getMessage());

            return back()->with([
                'toastMessage' => 'Error: ' . $e->getMessage(),
                'toastType' => 'error'
            ]);
        }
    }

    /**
     * Entry point untuk Dashboard & Analytics UI
     */
    /**
     * Entry point untuk Dashboard & Analytics UI
     */
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        $search = $request->get('search');

        // Palet warna global (digunakan untuk grafik dan tangki agar sinkron)
        $colors = ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];

        // =========================================
        // 1. DATA TANGKI (MULTIPLE OIL SUPPORT)
        // =========================================
        $tanksData = DB::connection('mysql_oil')->table('master_oil_tank')->get();
        $stocksData = DB::connection('mysql_oil')->table('inventory_oil_stocks')->get();

        $tanks = $tanksData->map(function ($tank) use ($stocksData, $colors) {
            $tankStocks = $stocksData->where('ld_loc', $tank->tank_name);
            $totalQty = $tankStocks->sum('ld_qty_oh');

            $items = [];
            $colorIndex = 0;
            foreach ($tankStocks as $stock) {
                if ($stock->ld_qty_oh > 0) {
                    $items[] = [
                        'part' => $stock->ld_part,
                        'qty' => (float) $stock->ld_qty_oh,
                        'color' => $colors[$colorIndex % count($colors)] // Assign warna untuk tangki
                    ];
                    $colorIndex++;
                }
            }

            return [
                'tank_name' => $tank->tank_name,
                'capacity' => (float) $tank->capacity,
                'total_qty' => $totalQty,
                'item_list' => $tankStocks->pluck('ld_part')->unique()->implode(', '),
                'items' => $items // Array multi-minyak
            ];
        });

        // =========================================
        // 2. DATA CHART INCOMING (KIRI)
        // =========================================
        $inQuery = InventoryOilInOut::where('type', 'IN')
            ->whereYear('tr_date', $year)
            ->whereMonth('tr_date', $month);

        if ($search) {
            $inQuery->where(function ($q) use ($search) {
                $q->where('tr_part', 'like', "%$search%")
                    ->orWhere('tr_part_name', 'like', "%$search%");
            });
        }

        $incomingData = $inQuery->select('tr_addr_name', 'tr_part', DB::raw('SUM(tr_qty_chg) as total'))
            ->groupBy('tr_addr_name', 'tr_part')
            ->orderBy('total', 'desc')
            ->get();

        $inSuppliers = $incomingData->pluck('tr_addr_name')->unique()->values()->toArray();
        $inOilTypes = $incomingData->pluck('tr_part')->unique()->values()->toArray();

        $inDatasets = [];
        foreach ($inOilTypes as $index => $type) {
            $dataPoint = [];
            foreach ($inSuppliers as $sup) {
                $val = $incomingData->where('tr_addr_name', $sup)->where('tr_part', $type)->first();
                $dataPoint[] = $val ? (float) $val->total : 0;
            }
            $inDatasets[] = [
                'label' => $type,
                'data' => $dataPoint,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderRadius' => 2
            ];
        }

        // =========================================
        // 3. DATA CHART OUTGOING (KANAN)
        // =========================================
        $outQuery = InventoryOilInOut::where('type', 'OUT')
            ->whereYear('tr_date', $year)
            ->whereMonth('tr_date', $month);

        if ($search) {
            $outQuery->where(function ($q) use ($search) {
                $q->where('tr_part', 'like', "%$search%")
                    ->orWhere('tr_part_name', 'like', "%$search%");
            });
        }

        $outgoingData = $outQuery->select('tr_part_name', DB::raw('SUM(tr_qty_chg) as total'))
            ->groupBy('tr_part_name')
            ->orderBy('total', 'desc')
            ->get();

        $outLabels = $outgoingData->pluck('tr_part_name')->toArray();
        $outValues = $outgoingData->pluck('total')->toArray();

        // =========================================
        // 4. DATA TABEL LOGISTIK (Disiapkan agar ramah JSON)
        // =========================================
        $incomingTable = InventoryOilInOut::where('type', 'IN')->orderBy('created_at', 'desc')->take(20)->get()->map(function ($item) {
            $item->date_formatted = \Carbon\Carbon::parse($item->created_at)->format('d-M H:i');
            $item->qty_formatted = number_format($item->tr_qty_chg, 2);
            return $item;
        });

        $outgoingTable = InventoryOilInOut::where('type', 'OUT')->orderBy('created_at', 'desc')->take(20)->get()->map(function ($item) {
            $item->qty_formatted = number_format($item->tr_qty_chg, 2);
            return $item;
        });

        // =========================================
        // 5. RESPONSE HANDLER (HTML vs AJAX JSON)
        // =========================================
        $responseData = [
            'tanks' => $tanks,
            'inLabels' => $inSuppliers,
            'inDatasets' => $inDatasets,
            'outLabels' => $outLabels,
            'outValues' => $outValues,
            'incomingTable' => $incomingTable,
            'outgoingTable' => $outgoingTable,
            'selectedYear' => $year,
            'selectedMonth' => $month
        ];

        // Jika Request dari Javascript (Tanpa Reload), kembalikan JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($responseData);
        }

        // Jika Load awal, kembalikan HTML Blade
        return view('rbd.index', $responseData);
    }
}