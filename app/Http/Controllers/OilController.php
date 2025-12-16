<?php

namespace App\Http\Controllers;

use App\Models\Tank;
use App\Models\OilStockReading;
use App\Models\ProductionTank;
use App\Models\ProductionTankReading;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\FatBlendTank;
use App\Models\FatBlendTankReading;
use App\Models\Yard1tTank;
use App\Models\Yard1tTankReading;
use App\Models\BleachedOilTank;
use App\Models\BleachedOilTankReading;
use App\Models\PackingTank;
use App\Models\PackingTankReading;
use App\Models\UtilityGasReading;

class OilController extends Controller
{
    public function index()
    {
        return view('oil.index');
    }

    public function loadComponent($componentName)
    {
        // Pastikan nama komponen konsisten. Kita gunakan 'tank_yard_bdt'
        if ($componentName === 'tank_yard_80t') {
            $tanks = Tank::orderBy('tank_code')->get();
            // Sesuaikan path view ke lokasi komponen Anda
            return view('oil.partials.' . $componentName, compact('tanks'));
        }
        if ($componentName === 'batch_refinery') {
            return view('oil.partials.' . $componentName);
        }
        if ($componentName === 'fat_blend_tank') {
            return view('oil.partials.' . $componentName);
        }
        if ($componentName === 'tank_yard_1t') {
            return view('oil.partials.' . $componentName);
        }
        if ($componentName === 'bleached_oil_tank') {
            return view('oil.partials.' . $componentName);
        }
        if ($componentName === 'packing_room') {
            return view('oil.partials.' . $componentName);
        }
        if ($componentName === 'current_oil_stock') {
            return view('oil.partials.' . $componentName);
        }
        if ($componentName === 'hydrogen_nitrogen_ammonia') {
            return view('oil.partials.' . $componentName);
        }

        abort(404);
    }

    public function getTankData(Request $request)
    {
        $validated = $request->validate([
            'tank_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $tankId = $validated['tank_id'];

        // Data untuk Grafik
        $query = OilStockReading::with('tank')
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date');

        if ($tankId !== 'ALL')
            $query->where('tank_id', $tankId);

        $readings = $query->get();
        $labels = $readings->pluck('reading_date')->unique()->map(fn($date) => $date->format('d M'))->values();
        $datasets = $readings->groupBy('tank.tank_code')->map(function ($readingsByTank) {
            $tank = $readingsByTank->first()->tank;
            return ['label' => $tank->tank_code . ' - ' . $tank->description, 'data' => $readingsByTank->pluck('current_value_kg'), 'borderColor' => $tank->color_hex, 'backgroundColor' => $tank->color_hex . '33', 'borderWidth' => 2, 'pointRadius' => 0, 'pointHoverRadius' => 6, 'tension' => 0.4, 'fill' => false,];
        })->values();

        // Data untuk Tabel (data terbaru pada atau sebelum end_date)
        $latestReadingsQuery = Tank::with(['oilStockReadings' => fn($q) => $q->whereDate('reading_date', '<=', $endDate)->latest('reading_date')]);

        if ($tankId !== 'ALL')
            $latestReadingsQuery->where('id', $tankId);

        $tableData = $latestReadingsQuery->get()->map(function ($tank) {
            $latest = $tank->oilStockReadings->first();
            return [
                'tank_code' => $tank->tank_code,
                'capacity_kg' => number_format($tank->capacity_kg),
                'oil_code' => $tank->oil_code,
                'description' => $tank->description,
                'gauge_board_meter' => $latest ? number_format($latest->gauge_board_meter, 2) : '-',
                'temperature_celsius' => $latest ? number_format($latest->temperature_celsius, 2) : '-',
                'current_value_kg' => $latest ? number_format($latest->current_value_kg, 2) : 'No Data',
            ];
        });

        return response()->json(['labels' => $labels, 'datasets' => $datasets, 'tableData' => $tableData]);
    }

    public function getRefineryData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // --- DATA UNTUK TABEL & BAR CHART (Snapshot di Tanggal Akhir) ---
        $latestReadingIds = ProductionTankReading::select(DB::raw('max(id) as id'))
            ->whereDate('reading_date', '<=', $endDate)
            ->groupBy('production_tank_id');

        $latestReadings = ProductionTankReading::with('productionTank')
            ->whereIn('id', $latestReadingIds)
            ->get();

        $tableData = $latestReadings->map(fn($reading) => [
            'name' => $reading->productionTank->name,
            'capacity_kg' => number_format($reading->productionTank->capacity_kg),
            'status' => $reading->status,
            'group' => $reading->productionTank->group_name,
        ]);

        // --- DATA BARU: Siapkan data ringkasan untuk Bar Chart ---
        $summaryDataForBarChart = $latestReadings->groupBy('productionTank.group_name')
            ->map(fn($groupReadings) => $groupReadings->sum('current_value_kg'));


        // --- DATA UNTUK GRAFIK GARIS & TOOLTIP (Data Selama Rentang Waktu) ---
        $rangeReadings = ProductionTankReading::with('productionTank')
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date')
            ->get();

        $chartDetailData = $rangeReadings->groupBy(fn($item) => $item->reading_date->format('Y-m-d'))
            ->map(
                fn($readingsOnDate) => $readingsOnDate->groupBy('productionTank.group_name')
                    ->map(
                        fn($readingsInGroup) => $readingsInGroup->map(fn($reading) => [
                            'name' => $reading->productionTank->name,
                            'value' => $reading->current_value_kg
                        ])
                    )
            );

        return response()->json([
            'tableData' => $tableData,
            'chartDetailData' => $chartDetailData,
            'summaryData' => $summaryDataForBarChart // <-- KIRIM DATA BARU INI
        ]);
    }

    public function getFatBlendData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // --- DATA UNTUK TABEL (Snapshot di Tanggal Akhir) ---
        $latestReadingIds = FatBlendTankReading::select(DB::raw('max(id) as id'))
            ->whereDate('reading_date', '<=', $endDate)
            ->groupBy('fat_blend_tank_id');

        $latestReadingsForTable = FatBlendTankReading::with('fatBlendTank')
            ->whereIn('id', $latestReadingIds)
            ->get();

        $tableData = FatBlendTank::orderBy('id')->get()->map(function ($tank) use ($latestReadingsForTable) {
            $reading = $latestReadingsForTable->firstWhere('fat_blend_tank_id', $tank->id);
            return [
                'name' => $tank->name,
                'capacity_kg' => number_format($tank->capacity_kg),
                'source_type' => $tank->source_type,
            ];
        });

        // --- DATA UNTUK GRAFIK GARIS (Data Selama Rentang Waktu) ---
        $rangeReadings = FatBlendTankReading::with('fatBlendTank')
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date')
            ->get();

        // Buat dataset untuk setiap tangki
        $datasets = $rangeReadings->groupBy('fatBlendTank.name')
            ->map(function ($readingsForTank, $tankName) {
                // Generate warna acak untuk setiap garis
                $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                return [
                    'label' => $tankName,
                    'data' => $readingsForTank->pluck('current_value_kg'),
                    'borderColor' => $color,
                    'backgroundColor' => $color . '33', // Warna dengan transparansi
                    'tension' => 0.3,
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'fill' => true,
                ];
            })->values();

        // Siapkan label (tanggal) untuk sumbu X
        $labels = $rangeReadings->pluck('reading_date')->unique()->map(fn($date) => $date->format('Y-m-d'))->sort()->values();

        return response()->json([
            'tableData' => $tableData,
            'chartData' => [
                'labels' => $labels,
                'datasets' => $datasets
            ],
        ]);
    }

    public function getYard1tData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // --- DATA UNTUK TABEL (Snapshot di Tanggal Akhir) ---
        $latestReadingIds = Yard1tTankReading::select(DB::raw('max(id) as id'))
            ->whereDate('reading_date', '<=', $endDate)
            ->groupBy('yard1t_tank_id');

        $latestReadingsForTable = Yard1tTankReading::with('yard1tTank')
            ->whereIn('id', $latestReadingIds)
            ->get();

        $tableData = $latestReadingsForTable->map(fn($r) => [
            'tank_code' => $r->yard1tTank->tank_code,
            'capacity_kg' => number_format($r->yard1tTank->capacity_kg),
            'oil_code' => $r->oil_code,
            'description' => $r->description,
        ]);

        // --- DATA UNTUK STACKED BAR CHART (Data Selama Rentang Waktu) ---
        $rangeReadings = Yard1tTankReading::whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date')
            ->get();

        // Logika pengelompokan untuk chart
        $getOilCategory = function ($description) {
            if (str_contains($description, 'PSS'))
                return 'PSS';
            if (str_contains($description, 'PO'))
                return 'PO / PO (T)';
            if (in_array($description, ['PKO', 'CNO', 'RBD PKS']))
                return 'PKO / CNO';
            if ($description === 'SBO')
                return 'SBO';
            if ($description === 'Available')
                return 'Available';
            return 'Others';
        };

        // Kelompokkan data berdasarkan tanggal, lalu berdasarkan kategori minyak
        $dailyGroupedData = $rangeReadings->groupBy(fn($item) => $item->reading_date->format('Y-m-d'))
            ->map(
                fn($readingsOnDate) => $readingsOnDate->groupBy(fn($r) => $getOilCategory($r->description))
                    ->map(fn($group) => $group->sum('current_value_kg'))
            );

        return response()->json([
            'tableData' => $tableData,
            'chartData' => $dailyGroupedData,
        ]);
    }

    public function getBleachedOilData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // --- DATA UNTUK TABEL (Snapshot di Tanggal Akhir) ---
        $latestReadingIds = BleachedOilTankReading::select(DB::raw('max(id) as id'))
            ->whereDate('reading_date', '<=', $endDate)
            ->groupBy('bleached_oil_tank_id');

        $latestReadingsForTable = BleachedOilTankReading::with('bleachedOilTank')
            ->whereIn('id', $latestReadingIds)
            ->get();

        $tableData = $latestReadingsForTable->sortBy('bleachedOilTank.id')->map(fn($r) => [
            'tank_code' => $r->bleachedOilTank->tank_code,
            'capacity_kg' => number_format($r->bleachedOilTank->capacity_kg),
            'oil_code' => $r->oil_code,
            'description' => $r->description,
        ]);

        // --- DATA UNTUK GRAFIK GARIS (Data Selama Rentang Waktu) ---
        $rangeReadings = BleachedOilTankReading::with('bleachedOilTank')
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date')
            ->get();

        // Buat dataset untuk setiap tangki
        $datasets = $rangeReadings->groupBy('bleachedOilTank.tank_code')
            ->map(function ($readingsForTank, $tankCode) {
                // Beri warna khusus untuk tangki besar
                $color = ($tankCode === '6T15') ? '#4f46e5' : sprintf('#%06X', crc32($tankCode) & 0xFFFFFF);

                return [
                    'label' => $tankCode,
                    'data' => $readingsForTank->pluck('current_value_kg'),
                    'borderColor' => $color,
                    'backgroundColor' => $color . '1A', // Warna transparan
                    'tension' => 0.3,
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'fill' => false, // Set false agar tidak terlalu ramai
                ];
            })->sortBy('label')->values();

        // Siapkan label (tanggal) untuk sumbu X
        $labels = $rangeReadings->pluck('reading_date')->unique()->map(fn($date) => $date->format('Y-m-d'))->sort()->values();

        return response()->json([
            'tableData' => $tableData,
            'chartData' => [
                'labels' => $labels,
                'datasets' => $datasets
            ],
        ]);
    }

    public function getPackingData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // --- DATA UNTUK TABEL (Snapshot di Tanggal Akhir) ---
        $latestReadingIds = PackingTankReading::select(DB::raw('max(id) as id'))
            ->whereDate('reading_date', '<=', $endDate)
            ->groupBy('packing_tank_id');

        $latestReadingsForTable = PackingTankReading::with('packingTank')
            ->whereIn('id', $latestReadingIds)
            ->get();

        $tableData = $latestReadingsForTable->sortBy('packingTank.id')->map(fn($r) => [
            'tank_code' => $r->packingTank->tank_code,
            'capacity_kg' => number_format($r->packingTank->capacity_kg),
            'status' => $r->status,
        ]);

        // --- DATA UNTUK GRAFIK GARIS (Data Selama Rentang Waktu) ---
        $rangeReadings = PackingTankReading::with('packingTank')
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date')
            ->get();

        // Buat dataset untuk setiap tangki
        $datasets = $rangeReadings->groupBy('packingTank.tank_code')
            ->map(function ($readingsForTank, $tankCode) {
                // Generate warna unik yang konsisten berdasarkan nama tangki
                $color = sprintf('#%06X', crc32($tankCode) & 0xFFFFFF);

                return [
                    'label' => $tankCode,
                    'data' => $readingsForTank->pluck('current_value_kg'),
                    'borderColor' => $color,
                    'backgroundColor' => $color . '33', // Warna transparan
                    'tension' => 0.3,
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'fill' => true,
                ];
            })->sortBy('label')->values();

        // Siapkan label (tanggal) untuk sumbu X
        $labels = $rangeReadings->pluck('reading_date')->unique()->map(fn($date) => $date->format('Y-m-d'))->sort()->values();

        return response()->json([
            'tableData' => $tableData,
            'chartData' => [
                'labels' => $labels,
                'datasets' => $datasets
            ],
        ]);
    }
    public function getCurrentStockData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // --- DATA UNTUK TABEL (Snapshot di Tanggal Akhir) ---
        $allReadingsForTable = collect();

        $latest80T = OilStockReading::whereIn('id', fn($q) => $q->select(DB::raw('max(id)'))->from('oil_stock_readings')->whereDate('reading_date', '<=', $endDate)->groupBy('tank_id'))->get();
        foreach ($latest80T as $r)
            if ($r->oil_code)
                $allReadingsForTable->push($r);

        $latest1T = Yard1tTankReading::whereIn('id', fn($q) => $q->select(DB::raw('max(id)'))->from('yard1t_tank_readings')->whereDate('reading_date', '<=', $endDate)->groupBy('yard1t_tank_id'))->get();
        foreach ($latest1T as $r)
            if ($r->oil_code)
                $allReadingsForTable->push($r);

        $latestBleached = BleachedOilTankReading::whereIn('id', fn($q) => $q->select(DB::raw('max(id)'))->from('bleached_oil_tank_readings')->whereDate('reading_date', '<=', $endDate)->groupBy('bleached_oil_tank_id'))->get();
        foreach ($latestBleached as $r)
            if ($r->oil_code)
                $allReadingsForTable->push($r);

        $tableData = $allReadingsForTable->groupBy('oil_code')->map(fn($group) => [
            'oil_code' => $group->first()->oil_code,
            'description' => $group->first()->description ?: 'N/A',
            'current_value' => $group->sum('current_value_kg'),
        ])->sortByDesc('current_value')->values();


        // --- DATA UNTUK GRAFIK (Data Selama Rentang Waktu) ---
        $allRangeReadings = collect();

        $range80T = OilStockReading::whereBetween('reading_date', [$startDate, $endDate])->get();
        foreach ($range80T as $r)
            if ($r->oil_code)
                $allRangeReadings->push($r);

        $range1T = Yard1tTankReading::whereBetween('reading_date', [$startDate, $endDate])->get();
        foreach ($range1T as $r)
            if ($r->oil_code)
                $allRangeReadings->push($r);

        $rangeBleached = BleachedOilTankReading::whereBetween('reading_date', [$startDate, $endDate])->get();
        foreach ($rangeBleached as $r)
            if ($r->oil_code)
                $allRangeReadings->push($r);

        $chartData = $allRangeReadings->groupBy(fn($item) => $item->reading_date->format('Y-m-d'))
            ->map(
                fn($readingsOnDate) => $readingsOnDate->groupBy('oil_code')
                    ->map(fn($group) => $group->sum('current_value_kg'))
            )->sortKeys();

        return response()->json([
            'tableData' => $tableData,
            'chartData' => $chartData
        ]);
    }

    public function getUtilityGasData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // --- 1. DATA UNTUK SNAPSHOT (di Tanggal Akhir) ---
        $snapshotReadings = UtilityGasReading::whereIn('id', function ($query) use ($endDate) {
            $query->select(DB::raw('max(id)'))
                ->from('utility_gas_readings')
                ->whereDate('reading_date', '<=', $endDate)
                ->groupBy('gas_type', 'unit_name');
        })->get();

        $snapshotData = [
            'hydrogen' => $snapshotReadings->where('gas_type', 'HYDROGEN')->values(),
            'nitrogen' => $snapshotReadings->firstWhere('gas_type', 'NITROGEN'),
            'ammonia' => $snapshotReadings->where('gas_type', 'AMMONIA')->values(),
        ];


        // --- 2. DATA UNTUK GRAFIK TREN (Selama Rentang Waktu) ---
        $rangeReadings = UtilityGasReading::whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date')
            ->get();

        $labels = $rangeReadings->pluck('reading_date')->unique()->map(fn($date) => $date->format('Y-m-d'))->sort()->values();

        $prepareChartData = function ($gasType, $unitName = null) use ($rangeReadings, $labels) {
            $query = $rangeReadings->where('gas_type', $gasType);
            if ($unitName)
                $query = $query->where('unit_name', $unitName);
            $dataMap = $query->keyBy(fn($item) => $item->reading_date->format('Y-m-d'));
            return $labels->map(fn($date) => $dataMap[$date]->value ?? null)->all();
        };

        $trendData = [
            'labels' => $labels,
            'hydrogen' => [
                ['label' => 'Torpedo #04', 'data' => $prepareChartData('HYDROGEN', 'Torpedo #04')],
                ['label' => 'Torpedo #05', 'data' => $prepareChartData('HYDROGEN', 'Torpedo #05')],
            ],
            'nitrogen' => [
                ['label' => 'Liquid Tank', 'data' => $prepareChartData('NITROGEN', 'Liquid Tank')],
            ],
            'ammonia' => [
                ['label' => 'Full', 'data' => $prepareChartData('AMMONIA', 'Full Cylinders')],
                ['label' => 'Empty', 'data' => $prepareChartData('AMMONIA', 'Empty Cylinders')],
            ],
        ];

        return response()->json([
            'snapshot' => $snapshotData,
            'trend' => $trendData,
        ]);
    }
}