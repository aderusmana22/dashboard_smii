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
use App\Models\OilBatchRefineryTank;
use App\Models\OilBatchRefineryReading;

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
            $groups = OilBatchRefineryTank::select('group_name')->distinct()->orderBy('group_name')->pluck('group_name');
            return view('oil.partials.' . $componentName, compact('groups'));
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

    public function getTanksByGroup($group)
    {
        $tanks = OilBatchRefineryTank::where('group_name', $group)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($tanks);
    }

    public function getRefineryData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group' => 'nullable|string',
            'tank_id' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $filterGroup = $request->group && $request->group !== 'ALL' ? $request->group : null;
        $filterTank = $request->tank_id && $request->tank_id !== 'ALL' ? $request->tank_id : null;

        $tankQuery = OilBatchRefineryTank::query()->orderBy('sort_order');
        if ($filterGroup)
            $tankQuery->where('group_name', $filterGroup);
        if ($filterTank)
            $tankQuery->where('id', $filterTank);

        $tanks = $tankQuery->with(['readings' => fn($q) => $q->whereDate('reading_date', '<=', $endDate)->orderBy('reading_date', 'desc')->orderBy('shift', 'desc')->limit(1)])->get();

        $tableData = $tanks->map(function ($tank) {
            $reading = $tank->readings->first();
            $currentVal = $reading ? $reading->current_value_kg : 0;
            $percent = $tank->capacity_kg > 0 ? ($currentVal / $tank->capacity_kg) * 100 : 0;
            return ['id' => $tank->id, 'name' => $tank->name, 'current_value' => number_format($currentVal), 'raw_value' => $currentVal, 'capacity_kg' => number_format($tank->capacity_kg), 'fill_percent' => number_format($percent, 1), 'status' => $reading ? $reading->status : 'N/A', 'description' => $tank->description];
        });

        $avgQuery = OilBatchRefineryReading::query()->join('oil_batch_refinery_tanks', 'oil_batch_refinery_readings.tank_id', '=', 'oil_batch_refinery_tanks.id')->whereBetween('reading_date', [$startDate, $endDate])->select('oil_batch_refinery_tanks.group_name', DB::raw('AVG(oil_batch_refinery_readings.current_value_kg) as average_stock'))->groupBy('oil_batch_refinery_tanks.group_name');
        if ($filterGroup)
            $avgQuery->where('oil_batch_refinery_tanks.group_name', $filterGroup);
        if ($filterTank)
            $avgQuery->where('oil_batch_refinery_tanks.id', $filterTank);
        $averageSummary = $avgQuery->pluck('average_stock', 'group_name');

        $readingQuery = OilBatchRefineryReading::with('tank')->whereBetween('reading_date', [$startDate, $endDate])->orderBy('reading_date');
        if ($filterGroup)
            $readingQuery->whereHas('tank', fn($q) => $q->where('group_name', $filterGroup));
        if ($filterTank)
            $readingQuery->where('tank_id', $filterTank);
        $rangeReadings = $readingQuery->get();
        $chartDetailData = $rangeReadings->groupBy(fn($item) => $item->reading_date->format('Y-m-d'))->map(fn($readingsOnDate) => $readingsOnDate->groupBy('tank.group_name')->map(fn($groupReadings) => $groupReadings->map(fn($r) => ['name' => $r->tank->name, 'value' => $r->current_value_kg])));

        return response()->json(['tableData' => $tableData, 'chartDetailData' => $chartDetailData, 'averageSummary' => $averageSummary]);
    }

    // --- REVISI TOTAL METHOD EKSPOR ---
    // --- REVISI FINAL METHOD EKSPOR ---
    public function exportRefineryData(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $exportType = $request->input('export_type', 'daily');

        $tanks = OilBatchRefineryTank::orderBy('group_name')->orderBy('sort_order')->get();
        $dateRange = Carbon::parse($startDate)->toPeriod($endDate);

        // --- OPTIMISASI: Ambil semua data yang relevan dalam satu query ---
        $allReadingsQuery = OilBatchRefineryReading::whereBetween('reading_date', [$startDate, $endDate]);

        // Jika tipe ekspor adalah shift spesifik, kita bisa memfilter lebih awal
        if (str_starts_with($exportType, 'shift_')) {
            $shiftNumber = str_replace('shift_', '', $exportType);
            $allReadingsQuery->where('shift', $shiftNumber);
        }

        // Ambil data dan kelompokkan dalam format yang mudah diakses: 'YYYY-MM-DD-tank_id'
        $readingsCollection = $allReadingsQuery->get()->groupBy(function ($item) {
            return $item->reading_date->format('Y-m-d') . '-' . $item->tank_id;
        });

        // --- Tentukan Nama File dan Header berdasarkan Tipe Ekspor ---
        $fileName = 'Refinery_Report.csv';
        $reportTitle = 'REFINERY REPORT';
        $headers = ['Date', 'Group', 'Tank Name', 'Oil Code', 'Stock (Kg)', 'Status'];

        if ($exportType === 'daily') {
            $fileName = 'Refinery_Daily_Report_' . $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd') . '.csv';
            $reportTitle = 'DAILY REPORT (LAST SHIFT)';
            $headers = ['Date', 'Group', 'Tank Name', 'Oil Code', 'Stock (Kg)', 'Status', 'Last Shift'];
        } elseif (str_starts_with($exportType, 'shift_')) {
            $shiftNumber = str_replace('shift_', '', $exportType);
            $fileName = 'Refinery_Shift_' . $shiftNumber . '_Report_' . $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd') . '.csv';
            $reportTitle = 'SHIFT ' . $shiftNumber . ' REPORT';
        }

        // --- Mulai Proses Pembuatan CSV ---
        $callback = function () use ($tanks, $dateRange, $readingsCollection, $exportType, $reportTitle, $headers, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [$reportTitle]);
            fputcsv($file, ['Period: ' . $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y')]);
            fputcsv($file, []);
            fputcsv($file, $headers);

            foreach ($dateRange as $date) {
                $dateString = $date->format('Y-m-d');
                foreach ($tanks as $tank) {
                    $key = $dateString . '-' . $tank->id;
                    $readingsForDayAndTank = $readingsCollection->get($key);
                    $reading = null;

                    if ($readingsForDayAndTank) {
                        if ($exportType === 'daily') {
                            // Ambil yang shiftnya paling besar
                            $reading = $readingsForDayAndTank->sortByDesc('shift')->first();
                        } else {
                            // Karena sudah difilter di query awal, ambil saja yang pertama
                            $reading = $readingsForDayAndTank->first();
                        }
                    }

                    // Tulis data ke file CSV
                    if ($reading) {
                        $rowData = [
                            $dateString,
                            $tank->group_name,
                            $tank->name,
                            $reading->oil_code,
                            $reading->current_value_kg,
                            $reading->status,
                        ];
                        if ($exportType === 'daily') {
                            $rowData[] = $reading->shift; // Tambah kolom shift untuk laporan harian
                        }
                        fputcsv($file, $rowData);
                    } else {
                        // Tulis baris kosong jika tidak ada data
                        $rowData = [$dateString, $tank->group_name, $tank->name, '-', '0', 'No Data'];
                        if ($exportType === 'daily') {
                            $rowData[] = '-';
                        }
                        fputcsv($file, $rowData);
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
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
        // Validasi input tanggal
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);

        // --- 1. SNAPSHOT DATA (Visualisasi Tabung/Torpedo) ---
        // Ambil data terakhir (Max ID) pada atau sebelum end_date
        $snapshotReadings = \App\Models\OilUtilityGasReading::with('master')
            ->whereIn('id', function ($q) use ($endDate) {
                $q->select(DB::raw('max(id)'))->from('oil_stock_utility_gas_readings')
                    ->whereDate('reading_date', '<=', $endDate)
                    ->groupBy('master_id');
            })->get();

        // --- TAMBAHAN BARU: AMBIL TANGGAL TERAKHIR (GLOBAL) ---
        // Kita cari 1 record dengan reading_date paling baru di database
        $lastRecord = \App\Models\OilUtilityGasReading::latest('reading_date')->first();

        // Format tanggalnya (Contoh: "Monday, 03 Feb 2026")
        $lastUpdateText = $lastRecord
            ? \Carbon\Carbon::parse($lastRecord->reading_date)->format('l, d F Y')
            : '-';

        $snapshotData = [
            'hydrogen' => $snapshotReadings->where('master.gas_type', 'HYDROGEN')->map(fn($r) => [
                'unit_name' => $r->master->name,
                'value' => $r->value
            ])->values(),
            // Ambil Nitrogen pertama yang ketemu (biasanya cuma 1 tank)
            'nitrogen' => $snapshotReadings->where('master.gas_type', 'NITROGEN')->map(fn($r) => [
                'unit_name' => $r->master->name,
                'value' => $r->value
            ])->first(),
            'ammonia' => $snapshotReadings->where('master.gas_type', 'AMMONIA')->map(fn($r) => [
                'unit_name' => $r->master->name,
                'value' => $r->value
            ])->values(),
        ];

        // --- 2. TREND DATA (Grafik Garis) ---
        $rangeReadings = \App\Models\OilUtilityGasReading::with('master')
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date')
            ->get();

        // FIX ERROR: Pastikan tanggal diparsing ke Carbon sebelum di-format
        $labels = $rangeReadings->pluck('reading_date')
            ->unique()
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->sort()
            ->values();

        // Helper function untuk memetakan data berdasarkan tanggal
        // Kita gunakan $searchName (opsional) untuk filter nama spesifik
        $prepareData = function ($gasType, $searchName = null) use ($rangeReadings, $labels) {
            $q = $rangeReadings->where('master.gas_type', $gasType);

            // Filter nama jika ada (Loose search menggunakan str_contains PHP)
            if ($searchName) {
                $q = $q->filter(function ($item) use ($searchName) {
                    return stripos($item->master->name, $searchName) !== false;
                });
            }

            // FIX ERROR: KeyBy menggunakan parsing Carbon yang aman
            $map = $q->keyBy(fn($i) => \Carbon\Carbon::parse($i->reading_date)->format('Y-m-d'));

            // Mapping value sesuai urutan label tanggal
            return $labels->map(fn($d) => $map[$d]->value ?? null)->all();
        };

        // --- Dynamic Series Generation ---

        // A. Hydrogen (Semua item HYDROGEN jadi series terpisah)
        $h2Masters = \App\Models\OilUtilityGasMaster::where('gas_type', 'HYDROGEN')->where('is_active', 1)->get();
        $h2Trends = [];
        foreach ($h2Masters as $master) {
            // Kita ambil data spesifik berdasarkan nama master persis
            $data = $rangeReadings->where('master_id', $master->id)
                ->keyBy(fn($i) => \Carbon\Carbon::parse($i->reading_date)->format('Y-m-d'));

            $h2Trends[] = [
                'label' => $master->name,
                'data' => $labels->map(fn($d) => $data[$d]->value ?? null)->all()
            ];
        }

        // B. Nitrogen (Ambil item pertama NITROGEN)
        // Kita cari master data Nitrogen yang aktif
        $n2Master = \App\Models\OilUtilityGasMaster::where('gas_type', 'NITROGEN')->where('is_active', 1)->first();
        $n2Data = [];
        if ($n2Master) {
            $q = $rangeReadings->where('master_id', $n2Master->id)
                ->keyBy(fn($i) => \Carbon\Carbon::parse($i->reading_date)->format('Y-m-d'));
            $n2Data = $labels->map(fn($d) => $q[$d]->value ?? null)->all();
        }

        // C. Ammonia (Cari yang namanya mengandung 'Full' dan 'Empty')
        // Ini agar jika nama di DB "Ammonia Full" atau "Full Cylinders", tetap terbaca
        $ammoniaFullData = $prepareData('AMMONIA', 'Full');
        $ammoniaEmptyData = $prepareData('AMMONIA', 'Empty');

        $trendData = [
            'labels' => $labels,
            'hydrogen' => $h2Trends,
            'nitrogen' => [
                [
                    'label' => $n2Master ? $n2Master->name : 'Nitrogen Level',
                    'data' => $n2Data
                ]
            ],
            'ammonia' => [
                ['label' => 'Full Cylinders', 'data' => $ammoniaFullData],
                ['label' => 'Empty Cylinders', 'data' => $ammoniaEmptyData]
            ]
        ];

        return response()->json([
            'snapshot' => $snapshotData,
            'trend' => $trendData,
            'last_update_label' => $lastUpdateText // <--- KITA KIRIM INI KE VIEW
        ]);
    }
}