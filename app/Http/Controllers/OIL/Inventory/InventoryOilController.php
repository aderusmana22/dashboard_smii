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

            $namespaces = $xml->getNamespaces(true);
            foreach ($namespaces as $prefix => $ns) {
                $xml->registerXPathNamespace($prefix ?: 'ns', $ns);
            }

            $datas = $xml->xpath('//qdoc:tr_hist');

            if (!$datas || count($datas) === 0) {
                throw new \Exception('No tr_hist data found in XML. Check if namespace qdoc is correct.');
            }

            foreach ($datas as $data) {
                $this->saveToDatabase($data, $namespaces);
            }

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
        $qdoc = $data->children($namespaces['qdoc']);

        $tr_trnbr = trim((string) $qdoc->trTrnbr);
        $tr_part = trim((string) $qdoc->trPart);
        $tr_addr = trim((string) $qdoc->trAddr);
        $tr_qty_raw = trim((string) $qdoc->trQtyLoc);
        $tr_um = trim((string) $qdoc->trUm);
        $tr_effdate = trim((string) $qdoc->trEffdate);

        $tr_date = !empty($tr_effdate) ? date('Y-m-d', strtotime($tr_effdate)) : null;
        $type = empty($tr_addr) ? 'OUT' : 'IN';

        $tr_addr_name = !empty($tr_addr) ? Supplier::where('vd_addr', $tr_addr)->value('vd_sort') : null;
        $tr_part_name = !empty($tr_part) ? Item::where('pt_part', $tr_part)->value('pt_desc1') : null;

        InventoryOilInOut::updateOrCreate(
            ['tr_trnbr' => $tr_trnbr],
            [
                'tr_part' => $tr_part,
                'tr_part_name' => $tr_part_name,
                'tr_addr' => !empty($tr_addr) ? $tr_addr : null,
                'tr_addr_name' => $tr_addr_name,
                'tr_date' => $tr_date,
                'tr_qty_chg' => $tr_qty_raw,
                'tr_um' => $tr_um,
                'type' => $type,
            ]
        );
    }

    public function getOilStock()
    {
        $qxUrl = 'http://smii.qad:25079/wsa/smiiwsa';
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
            return back()->with(['toastMessage' => 'Gagal inisialisasi cURL.', 'toastType' => 'error']);
        }

        curl_setopt_array($curl, $curlOptions);
        $qdocResponse = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            return back()->with(['toastMessage' => 'cURL Error: ' . $curlError, 'toastType' => 'error']);
        }

        if (!$qdocResponse) {
            return back()->with(['toastMessage' => 'Tidak ada respons dari QAD.', 'toastType' => 'error']);
        }

        try {
            $xmlResp = simplexml_load_string($qdocResponse);
            if ($xmlResp === false) {
                throw new \Exception("XML tidak valid.");
            }

            $rows = $xmlResp->xpath('//*[local-name()="ttInvProdLineRow"]');

            if (count($rows) === 0) {
                return back()->with(['toastMessage' => 'Data dari QAD kosong.', 'toastType' => 'warning']);
            }

            $records = [];
            $now = now();

            foreach ($rows as $item) {
                $ld_part = trim((string) $item->ld_part);
                if ($ld_part === '')
                    continue;

                $records[] = [
                    'ld_part' => $ld_part,
                    'pt_desc1' => trim((string) $item->pt_desc1),
                    'ld_qty_oh' => (float) trim((string) $item->ld_qty_oh),
                    'pt_um' => trim((string) $item->pt_um),
                    'pt_prod_line' => trim((string) $item->pt_prod_line),
                    'ld_loc' => isset($item->ld_loc) ? trim((string) $item->ld_loc) : null,
                    'ld_date' => isset($item->ld_date) ? trim((string) $item->ld_date) : null,
                    'aging' => isset($item->aging) ? (int) trim((string) $item->aging) : 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            try {
                DB::connection('mysql_oil')->statement('SET FOREIGN_KEY_CHECKS=0;');
                InventoryOilStock::truncate();
                DB::connection('mysql_oil')->statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $e) {
                Log::channel('invoil')->error('Truncate gagal: ' . $e->getMessage());
            }

            try {
                InventoryOilStock::insert($records);
            } catch (\Exception $e) {
                return back()->with(['toastMessage' => 'Insert DB gagal: ' . $e->getMessage(), 'toastType' => 'error']);
            }

            return back()->with(['toastMessage' => 'Inventory diperbarui. Total: ' . count($records), 'toastType' => 'success']);
        } catch (\Exception $e) {
            return back()->with(['toastMessage' => 'Error: ' . $e->getMessage(), 'toastType' => 'error']);
        }
    }

    /**
     * Entry point untuk Dashboard & Analytics UI
     */
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        $search = $request->get('search');

        $colors = ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];

        // =========================================
        // 1. DATA TANGKI VIRTUAL (AGREGASI PER GROUP)
        // =========================================
        $tanksData = DB::connection('mysql_oil')->table('master_oil_tank')->get();
        $stocksData = DB::connection('mysql_oil')->table('inventory_oil_stocks')->get();

        $groupedTanks = [];

        foreach ($tanksData->groupBy('group') as $groupName => $tanks) {
            $groupName = empty($groupName) ? 'UNGROUPED' : $groupName;
            $tankNamesInGroup = $tanks->pluck('tank_name')->toArray();
            $totalCapacity = $tanks->sum('capacity');

            $groupStocks = $stocksData->whereIn('ld_loc', $tankNamesInGroup);
            $totalQty = $groupStocks->sum('ld_qty_oh');

            $items = [];
            $colorIndex = 0;

            $uniqueStocks = $groupStocks->groupBy('ld_part');

            foreach ($uniqueStocks as $part => $stocksList) {
                $qtySum = $stocksList->sum('ld_qty_oh');
                if ($qtySum > 0) {
                    $items[] = [
                        'part' => $part,
                        'qty' => (float) $qtySum,
                        'color' => $colors[$colorIndex % count($colors)]
                    ];
                    $colorIndex++;
                }
            }

            $groupedTanks[] = [
                'group_name' => $groupName,
                'capacity' => (float) $totalCapacity,
                'total_qty' => $totalQty,
                'item_list' => $uniqueStocks->keys()->implode(', '),
                'items' => $items,
                'tanks_included' => implode(', ', $tankNamesInGroup)
            ];
        }

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

        $outgoingData = $outQuery->select('tr_part_name', DB::raw('SUM(tr_qty_chg * -1) as total'))
            ->groupBy('tr_part_name')
            ->orderBy('total', 'desc')
            ->get();

        $outLabels = $outgoingData->pluck('tr_part_name')->toArray();
        $outValues = $outgoingData->pluck('total')->toArray();

        // =========================================
        // 4. DATA TABEL AGING (TENGAH) - REVISI PENGURUTAN (SORTING)
        // =========================================
        $agingTable = DB::connection('mysql_oil')->table('inventory_oil_stocks')
            ->where('ld_qty_oh', '>', 0)
            ->orderBy('aging', 'desc')       // 1. Urutkan dari Aging terlama (Paling Atas)
            ->orderBy('ld_qty_oh', 'desc')   // 2. Secondary Sort: Jika Aging sama, ambil QTY terbanyak
            ->take(5)
            ->get()
            ->map(function ($item) {
                $item->qty_formatted = number_format($item->ld_qty_oh, 2);
                return $item;
            });

        // =========================================
        // 5. RESPONSE HANDLER
        // =========================================
        $responseData = [
            'groupedTanks' => $groupedTanks,
            'inLabels' => $inSuppliers,
            'inDatasets' => $inDatasets,
            'outLabels' => $outLabels,
            'outValues' => $outValues,
            'agingTable' => $agingTable,
            'selectedYear' => $year,
            'selectedMonth' => $month
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($responseData);
        }

        return view('rbd.index', $responseData);
    }
}