<?php

namespace App\Http\Controllers\QAD;

use Illuminate\Http\Request;
use App\Models\QAD\Inventory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\QAD\Production;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\QAD\StandardWarehouseProduction;
use App\Models\QAD\StandardShipment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Inventory::query();
            return DataTables::of($data)->make(true);
        }
        return view('page.dataDashboard.inventory-index');
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


    public function getDashboardInventory()
    {
        $qxUrl = 'http://smii.qad:24079/wsa/smiiwsa';
        $timeout = 10;
        $domain = 'SMII';
        $qdocRequest = '
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
            <Body>
                <getDashboardInventory xmlns="urn:services-qad-com:smiiwsa:0001:smiiwsa">
                    <ip_domain>' . $domain . '</ip_domain>
                </getDashboardInventory>
            </Body>
        </Envelope>';

        $curlOptions = [
            CURLOPT_URL => $qxUrl,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout + 5,
            CURLOPT_HTTPHEADER => $this->httpHeader($qdocRequest),
            CURLOPT_POSTFIELDS => preg_replace("/\s+/", " ", $qdocRequest),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $curlOptions);
        $qdocResponse = curl_exec($curl);
        curl_close($curl);

        if (!$qdocResponse) {
            Log::error('Tidak ada respons dari server.');
            return redirect()->back()->with('error', 'Tidak ada respons dari server.');
        }

        $xmlResp = simplexml_load_string($qdocResponse);
        $xmlResp->registerXPathNamespace('ns', 'urn:services-qad-com:smiiwsa:0001:smiiwsa');

        $qdocResult = (string) $xmlResp->xpath('//ns:getDashboardInventoryResponse/ns:opOk')[0];

        if ($qdocResult !== 'true') {
            Alert::error('Error', 'Gagal mengambil data dari server.');
            Log::error('Gagal mengambil data dari server.');
            return redirect()->back();
        }

        $inventoryItems = $xmlResp->xpath('//ns:getDashboardInventoryResponse/ns:ttTable/ns:ttTableRow');

        if (empty($inventoryItems)) {
            Log::error('Tidak ada data inventory yang diterima dari server.');
            return redirect()->back()->with('error', 'Tidak ada data inventory yang diterima dari server.');
        }

        // Simpan data ke cache untuk sementara
        $temporaryData = [];
        foreach ($inventoryItems as $item) {
            $temporaryData[] = [
                'ld_part'    => (string) $item->ld_part,
                'pt_desc1'   => (string) $item->pt_desc1,
                'ld_status'  => (string) $item->ld_status,
                'ld_qty_oh'  => (string) $item->ld_qty_oh,
                'pt_um'      => (string) $item->pt_um,
                'ld_date'    => (string) $item->ld_date,
                'ld_loc'     => strtoupper((string) $item->ld_loc),
                'ld_lot'     => (string) $item->ld_lot,
                'aging_days' => (int) $item->aging_days,
                'ld_expire'  => (string) $item->ld_expire,
                'ton'        => (string) $item->ton,
                'scan_code'  => (string) $item->scan_code,
                'qtypallet'  => (string) $item->qtypallet,
                'total_pallet' => (string) $item->total_pallet
            ];
        }

        // Simpan sementara di cache selama 10 menit
        Cache::put('temporary_inventory_data', $temporaryData, now()->addMinutes(10));

        // Panggil fungsi untuk menyimpan data dari cache ke database
        $this->saveCachedInventoryData();

        Alert::success('Success', 'Data berhasil disimpan di cache. Siap untuk dimasukkan ke database.');
        return redirect()->back();
    }

    // Fungsi untuk menyimpan data dari cache ke database
    public function saveCachedInventoryData()
    {
        // Ambil data dari cache
        $cachedData = Cache::get('temporary_inventory_data');

        if (!$cachedData) {
            return redirect()->back()->with('error', 'Tidak ada data dalam cache untuk disimpan.');
        }

        // Hapus data lama dari database
        Inventory::truncate();

        // Insert data dari cache ke database dalam batch yang lebih kecil
        $batchSize = 1000; // Sesuaikan ukuran batch sesuai kebutuhan
        foreach (array_chunk($cachedData, $batchSize) as $batch) {
            Inventory::insert($batch);
        }

        // Hapus data dari cache setelah dimasukkan ke database
        Cache::forget('temporary_inventory_data');

        Alert::success('Success', 'Data berhasil disimpan ke database.');
        return redirect()->back();
    }


    // =========================================================StandardWarehouse=====================================================

    public function warehouseindex()
    {
        $standardWarehouse = StandardWarehouseProduction::all();
        return \view('page.standard.warehouse-index', \compact('standardWarehouse'));
    }



    public function warehousestore(Request $request)
    {
        $standardWarehouse = new StandardWarehouseProduction();
        $standardWarehouse->location = $request->location;
        $standardWarehouse->rack = $request->rack;
        $standardWarehouse->temperature = $request->temperature;
        $standardWarehouse->pallet_rack = $request->pallet_rack;
        $standardWarehouse->estimated_tonnage = $request->estimated_tonnage;
        $standardWarehouse->save();
        Alert::toast('Standard Warehouse Production successfully added', 'success');
        return redirect()->back();
    }

    public function warehouseupdate(Request $request, $id)
    {
        $standardWarehouse = StandardWarehouseProduction::findOrFail($id);
        $standardWarehouse->location = $request->location;
        $standardWarehouse->rack = $request->rack;
        $standardWarehouse->temperature = $request->temperature;
        $standardWarehouse->pallet_rack = $request->pallet_rack;
        $standardWarehouse->estimated_tonnage = $request->estimated_tonnage;
        $standardWarehouse->save();
        Alert::toast('Standard Warehouse Production successfully updated', 'success');
        return redirect()->back();
    }

    public function warehousedelete($id)
    {
        $standardWarehouse = StandardWarehouseProduction::findOrFail($id);
        $standardWarehouse->delete();
        Alert::toast('Standard Production successfully deleted', 'success');
        return redirect()->back();
    }

    public function getWarehouseDataWithTemperature()
    {
        // Login ke API untuk mendapatkan token
        $response = Http::post('https://api.sinar-meadow.universal-iot.com/rest-uiot/v-1/internal/auth/login', [
            'email' => 'sinar-meadow@universal-iot.com',
            'password' => 'user123!'
        ]);

        if ($response->successful()) {
            $token = $response->json()['data']['token'];

            // Mengambil data suhu menggunakan token
            $temperatureResponse = Http::withToken($token)->get('https://api.sinar-meadow.universal-iot.com/rest-uiot/v-1/internal/dashboard/temperature');

            if ($temperatureResponse->successful()) {
                $deviceList = $temperatureResponse->json()['data']['device_list'];

                // Kelompokkan data berdasarkan area_name
                $groupedData = collect($deviceList)->groupBy(function ($item) {
                    switch ($item['area_name']) {
                        case "Warehouse G2 25'C":
                            return 'G2 25 C';
                        case "Warehouse G2A 16'C":
                            return 'G2 16 C';
                        case "Warehouse G3 25'C":
                            return 'G3 25 C';
                        case "Warehouse G3 Bawah":
                        case "Warehouse G3 Tengah":
                        case "Warehouse G3 Atas":
                            return 'G3 Ambience';
                        default:
                            return 'Unknown';
                    }
                })->toArray();

                // Lakukan sesuatu dengan $groupedData, misalnya mengembalikan sebagai JSON
                return response()->json(['data' => $groupedData]);
            } else {
                return response()->json(['error' => 'Gagal mengambil data suhu'], 500);
            }
        } else {
            return response()->json(['error' => 'Gagal login ke API'], 500);
        }
    }

    // =========================================================StandardShipment=====================================================

    public function warehouseFilterData(Request $request)
    {
        $date = $request->input('date');

        if (!$date) {
            return ['data' => []]; // Kembalikan array kosong jika tanggal tidak valid
        }

        $data = DB::table('standard_warehouse_productions')
            ->whereDate('ld_date', $date)
            ->leftJoin('inventories', 'standard_warehouse_productions.rack', '=', DB::raw('LEFT(inventories.ld_loc, 3)'))
            ->select('standard_warehouse_productions.temperature as temperature', DB::raw('SUM(DISTINCT standard_warehouse_productions.pallet_rack) as pallet'), DB::raw('sum(ld_qty_oh) as quantity'), DB::raw('format(sum(ld_qty_oh)/sum(DISTINCT standard_warehouse_productions.pallet_rack)*100, 3)as percentage'))
            ->groupBy('standard_warehouse_productions.temperature')
            ->orderByRaw("
                CASE
                    WHEN standard_warehouse_productions.temperature = 'Ambient' THEN 1
                    WHEN standard_warehouse_productions.temperature = '25 Degree' THEN 2
                    WHEN standard_warehouse_productions.temperature = '16 Degree' THEN 3
                    ELSE 4
                END
            ")
            ->get(); // Pastikan ini mengembalikan koleksi

        // Kembalikan data sebagai array
        return ['data' => $data];
    }

    public function getAreaData(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;

        $query = StandardShipment::query();

        // Menggunakan whereYear hanya jika tahun diberikan
        if ($year) {
            $query->whereYear('date_shipment', $year);
        } else {
            $year = $currentYear; // Set tahun ke tahun saat ini jika tidak ada
            $query->whereYear('date_shipment', $year);
        }

        // Mengambil data bulanan untuk dispatch
        $areaDataMonthly = StandardShipment::select(
            DB::raw("DATE_FORMAT(date_shipment, '%b') AS months"),
            DB::raw("SUM(ton) AS total_dispatch")
        )
            ->whereYear('date_shipment', $year)
            ->groupBy(DB::raw("MONTH(date_shipment), DATE_FORMAT(date_shipment, '%b')"))
            ->orderBy(DB::raw("MONTH(date_shipment)"), 'asc')
            ->get();

        // Mengambil data bulanan untuk production
        $productionDataMonthly = Production::select(
            DB::raw("DATE_FORMAT(tr_effdate, '%b') AS months"),
            DB::raw("(SUM(Weight_in_KG) / 1000) AS total_production")
        )
            ->whereYear('tr_effdate', $year)
            ->groupBy(DB::raw("MONTH(tr_effdate), DATE_FORMAT(tr_effdate, '%b')"))
            ->orderBy(DB::raw("MONTH(tr_effdate)"), 'asc')
            ->get();

        // Menggunakan whereMonth hanya jika bulan diberikan
        if ($month) {
            $query->whereMonth('date_shipment', $month);
        } else {
            $month = $currentMonth; // Set bulan ke bulan saat ini jika tidak ada
            $query->whereMonth('date_shipment', $month);
        }

        // Mengambil data harian
        $areaData = $query->select(
            DB::raw('DAY(date_shipment) AS days'),
            DB::raw("FORMAT(SUM(ton), 3, 'id_ID') AS tons")
        )
            ->groupBy('date_shipment')
            ->orderBy('date_shipment', 'asc')
            ->get();

        // Mengambil data tahunan
        $areaDataYearly = StandardShipment::select(
            DB::raw("YEAR(date_shipment) AS years"),
            DB::raw("FORMAT(SUM(ton), 3, 'id_ID') AS tons")
        )
            ->groupBy("years")
            ->orderBy('date_shipment', 'asc')
            ->get();

        // Gabungkan data dispatch dan production berdasarkan bulan
        $combinedMonthlyData = [];
        foreach (range(1, 12) as $monthIndex) {
            $monthName = date('M', mktime(0, 0, 0, $monthIndex, 1));
            $dispatch = $areaDataMonthly->firstWhere('months', $monthName);
            $production = $productionDataMonthly->firstWhere('months', $monthName);

            $combinedMonthlyData[] = [
                'month' => $monthName,
                'total_dispatch' => $dispatch ? $dispatch->total_dispatch : 0,
                'total_production' => $production ? $production->total_production : 0,
            ];
        }

        return response()->json([
            'tons' => $areaData->pluck('tons'),
            'labels' => $areaData->pluck('days'),
            'monthlyData' => $combinedMonthlyData, // Data bulanan untuk dispatch dan production
            'yearlyTons' => $areaDataYearly->pluck('tons'),
            'yearlyLabels' => $areaDataYearly->pluck('years'),
        ]);
    }


    public function warehouseAreaDispatch(Request $request)
    {
        $date = $request->input('date');
        $month = $request->input('month');
        $year = $request->input('year');

        if (!$date) {
            return response()->json(['daily' => [], 'monthly' => []]); // Kembalikan array kosong jika tanggal tidak valid
        }

        // Mengambil data harian
        $daily = DB::table('standard_shipments')
            ->select(DB::raw("FORMAT(SUM(ton), 1, 'id_ID') AS tons"))
            ->whereDate('date_shipment', $date)
            ->groupBy('date_shipment')
            ->get();

        // Mengambil data bulanan
        $monthly = DB::table('standard_shipments')
            ->select(DB::raw("FORMAT(SUM(ton), 1, 'id_ID') AS tons"))
            ->when($month, function ($query) use ($month, $year) {
                return $query->whereMonth('date_shipment', $month)
                    ->whereYear('date_shipment', $year);
            })
            ->groupBy(DB::raw("MONTH(date_shipment)"))
            ->get();

        // Kembalikan data sebagai array
        return response()->json([
            'daily' => $daily,
            'monthly' => $monthly
        ]);
    }

    public function getWarehouseDataCombined()
    {
        // Ambil data dari tabel standard_warehouse_productions
        $standardWarehouse = DB::table('standard_warehouse_productions')
            ->select(
                DB::raw("CASE
                WHEN LEFT(rack, 4) = 'WH01' THEN 'WH01'
                ELSE LEFT(rack, 3)
            END as rack_prefix"),
                DB::raw('SUM(pallet_rack) as total_pallet_rack'),
                DB::raw('SUM(estimated_tonnage) as total_estimated_tonnage')
            )
            ->groupBy('rack_prefix')
            ->get();

        // Ambil data dari tabel inventories
        $inventories = DB::table('inventories')
            ->select(
                DB::raw("CASE
                WHEN LEFT(ld_loc, 4) = 'WH01' THEN 'WH01'
                WHEN LEFT(ld_loc, 2) = 'WH' THEN LEFT(ld_loc, 2)
                ELSE LEFT(ld_loc, 3)
            END as loc_prefix"),
                DB::raw('SUM(ton) as total_ton'),
                DB::raw('SUM(total_pallet) as total_pallet') // Tambahkan sum untuk total_pallet
            )
            ->groupBy('loc_prefix')
            ->get()
            ->keyBy('loc_prefix');

        // Define a mapping for rack prefixes to groups
        $rackGroupMapping = [
            'R2M' => 'G2 25 C',
            'R2N' => 'G2 25 C',
            'R1K' => 'G2 16 C',
            'R2L' => 'G2 16 C',
            'G3R' => 'G3 25 C',
            'S3R' => 'G3 25 C',
        ];

        // Function to determine the group based on rack prefix
        function determineGroup($rackPrefix, $mapping)
        {
            if ($rackPrefix === 'WH01') {
                return 'G1 Ambience'; // Special case for WH01
            } elseif (isset($mapping[$rackPrefix])) {
                return $mapping[$rackPrefix];
            } elseif (strpos($rackPrefix, 'G1') === 0) {
                return 'G1 Ambience';
            } else {
                return 'G3 Ambience';
            }
        }

        // Process and group the data
        $groupedData = $standardWarehouse->map(function ($item) use ($inventories, $rackGroupMapping) {
            $rackPrefix = strtoupper(trim($item->rack_prefix)); // Pastikan format konsisten

            // Assign group based on rack prefix
            $item->group = determineGroup($rackPrefix, $rackGroupMapping);

            // Calculate total tonnage and total pallet used
            $usedTonnage = optional($inventories->get($rackPrefix))->total_ton ?? 0;
            $usedPallet = optional($inventories->get($rackPrefix))->total_pallet ?? 0;

            $item->occupancy = ($item->total_estimated_tonnage > 0) ? ($usedTonnage / $item->total_estimated_tonnage) * 100 : 0;
            $item->used_pallet = $usedPallet;

            return $item;
        });

        // Agregasi data berdasarkan kategori
        // Agregasi data berdasarkan kategori
        $finalData = $groupedData->groupBy('group')->map(function ($group, $groupName) use ($inventories) {
            $totalTon = $group->sum(function ($item) use ($inventories) {
                return optional($inventories->get(strtoupper(trim($item->rack_prefix))))->total_ton ?? 0;
            });

            $totalPallet = $group->sum(function ($item) use ($inventories) {
                return optional($inventories->get(strtoupper(trim($item->rack_prefix))))->total_pallet ?? 0;
            });

            $totalEstimatedTonnage = $group->sum('total_estimated_tonnage');
            $totalPalletRack = $group->sum('total_pallet_rack');

            return [
                'group_name' => $groupName,
                'total_ton' => $totalTon,
                'total_pallet' => $totalPallet,
                'total_estimated_tonnage' => $totalEstimatedTonnage,
                'total_pallet_rack' => $totalPalletRack,
                'ton_to_estimated_ratio' => ($totalEstimatedTonnage > 0) ? ($totalTon / $totalEstimatedTonnage) : 0,
                'pallet_occupancy' => ($totalPalletRack > 0) ? ($totalPallet / $totalPalletRack) * 100 : 0, // << Tambahan ini
            ];
        });

        // Ensure all groups exist with default values if not present
        $defaultGroups = ['G1 Ambience', 'G2 16 C', 'G2 25 C', 'G3 25 C', 'G3 Ambience'];
        foreach ($defaultGroups as $groupName) {
            if (!$finalData->has($groupName)) {
                $finalData->put($groupName, [
                    'group_name' => $groupName,
                    'total_ton' => 0,
                    'total_pallet' => 0,
                    'total_estimated_tonnage' => 0,
                    'total_pallet_rack' => 0,
                    'ton_to_estimated_ratio' => 0,
                    'pallet_occupancy' => 0,
                ]);
            }
        }

        // Login ke API untuk mendapatkan token
        $response = Http::post('https://api.sinar-meadow.universal-iot.com/rest-uiot/v-1/internal/auth/login', [
            'email' => 'sinar-meadow@universal-iot.com',
            'password' => 'user123!'
        ]);

        if ($response->successful()) {
            $token = $response->json()['data']['token'];

            // Mengambil data suhu menggunakan token
            $temperatureResponse = Http::withToken($token)->get('https://api.sinar-meadow.universal-iot.com/rest-uiot/v-1/internal/dashboard/temperature');

            if ($temperatureResponse->successful()) {
                $deviceList = $temperatureResponse->json()['data']['device_list'];

                // Kelompokkan data berdasarkan area_name
                $temperatureData = collect($deviceList)->groupBy(function ($item) {
                    switch ($item['area_name']) {
                        case "Warehouse G2 25'C":
                            return 'G2 25 C';
                        case "Warehouse G2A 16'C":
                            return 'G2 16 C';
                        case "Warehouse G3 25'C":
                            return 'G3 25 C';
                        case "Warehouse G3 Bawah":
                        case "Warehouse G3 Tengah":
                        case "Warehouse G3 Atas":
                            return 'G3 Ambience';
                        default:
                            return 'Unknown';
                    }
                });

                // Return data warehouse dan temperature
                return response()->json(['warehouse_data' => $finalData, 'temperature_data' => $temperatureData]);
            } else {
                return response()->json(['error' => 'Gagal mengambil data suhu'], 500);
            }
        } else {
            return response()->json(['error' => 'Gagal login ke API'], 500);
        }
    }
}
