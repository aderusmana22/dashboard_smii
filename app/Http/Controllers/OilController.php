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
use App\Models\OilBatchRefineryTank;
use App\Models\OilBatchRefineryReading;
use App\Models\OilUtilityGasReading;
use App\Models\OilUtilityGasMaster;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

    // --- PERBAIKAN: GET REFINERY DATA (DASHBOARD) ---
    public function getRefineryData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group' => 'nullable|string',
            'tank_id' => 'nullable|string',
            'shift' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $filterGroup = $request->group && $request->group !== 'ALL' ? $request->group : null;
        $filterTank = $request->tank_id && $request->tank_id !== 'ALL' ? $request->tank_id : null;
        $filterShift = $request->shift && $request->shift !== 'ALL' ? $request->shift : null;

        $tankQuery = OilBatchRefineryTank::query()->orderBy('sort_order');
        if ($filterGroup)
            $tankQuery->where('group_name', $filterGroup);
        if ($filterTank)
            $tankQuery->where('id', $filterTank);
        $tanks = $tankQuery->get();

        // FIX: MENGHINDARI PENGGUNAAN MAX(id) KARENA BUG PADA UUID
        // Ambil data (maks. mundur 30 hari agar query ringan), urutkan dari lama ke baru
        $readingsQuery = OilBatchRefineryReading::whereDate('reading_date', '<=', $endDate)
            ->whereDate('reading_date', '>=', $endDate->copy()->subDays(30))
            ->orderBy('reading_date', 'asc')
            ->orderBy('shift', 'asc');

        if ($filterShift) {
            $readingsQuery->where('shift', $filterShift);
        }

        // keyBy akan otomatis menimpa tangki yang sama dengan iterasi terakhir (Data Paling Baru)
        $latestReadings = $readingsQuery->get()->keyBy('tank_id');

        $tableData = $tanks->map(function ($tank) use ($latestReadings) {
            $reading = $latestReadings->get($tank->id);
            $currentVal = $reading ? $reading->current_value_kg : 0;
            $percent = $tank->capacity_kg > 0 ? ($currentVal / $tank->capacity_kg) * 100 : 0;
            return [
                'id' => $tank->id,
                'name' => $tank->name,
                'current_value' => number_format($currentVal),
                'raw_value' => $currentVal,
                'capacity_kg' => number_format($tank->capacity_kg),
                'fill_percent' => number_format($percent, 1),
                'status' => $reading ? $reading->status : 'N/A',
                'description' => $tank->description
            ];
        });

        // DATA CHART & AVERAGE (Tetap menggunakan Range untuk visualisasi Tren)
        $readingQuery = OilBatchRefineryReading::with('tank')->whereBetween('reading_date', [$startDate, $endDate])->orderBy('reading_date');
        if ($filterGroup)
            $readingQuery->whereHas('tank', fn($q) => $q->where('group_name', $filterGroup));
        if ($filterTank)
            $readingQuery->where('tank_id', $filterTank);
        if ($filterShift)
            $readingQuery->where('shift', $filterShift);

        $rangeReadings = $readingQuery->get();

        $averageSummary = $rangeReadings->groupBy('tank.group_name')->map(function ($group) {
            return $group->avg('current_value_kg');
        });

        $chartDetailData = $rangeReadings->groupBy(fn($item) => $item->reading_date->format('Y-m-d'))
            ->map(fn($readingsOnDate) => $readingsOnDate->groupBy('tank.group_name')
                ->map(fn($groupReadings) => $groupReadings->map(fn($r) => ['name' => $r->tank->name, 'value' => $r->current_value_kg])));

        return response()->json([
            'tableData' => $tableData,
            'chartDetailData' => $chartDetailData,
            'averageSummary' => $averageSummary
        ]);
    }



    // --- PERBAIKAN: EXPORT REFINERY DATA ---
    public function exportRefineryData(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $exportType = $request->input('export_type', 'daily');
        $isRange = !$startDate->isSameDay($endDate);

        if ($exportType === 'daily') {
            $titleHeader = "Daily Oil Stock Daily";
            $shiftNumber = 'ALL';
        } else {
            $shiftNumber = str_replace('shift_', '', $exportType);
            $titleHeader = "Daily Oil Stock Shift " . $shiftNumber;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Setup Styling
        $styleHeaderTitle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $styleOrange = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFC000']],
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $styleYellow = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']]
        ];
        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $styleCenter = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['bold' => true]
        ];

        // 1. Header Utama
        $sheet->setCellValue('A1', $titleHeader);
        $sheet->getStyle('A1')->applyFromArray($styleHeaderTitle);
        $sheet->mergeCells('A1:G1');

        // 2. Baris Judul Batch Refinery & Tanggal
        $sheet->setCellValue('A2', 'Batch Refinery');
        $sheet->getStyle('A2:E2')->applyFromArray($styleOrange);
        $sheet->mergeCells('A2:E2');

        $sheet->setCellValue('F2', 'Latest Update:');
        $sheet->setCellValue('G2', $endDate->format('d/m/Y'));
        $sheet->getStyle('F2:G2')->applyFromArray($styleBorder);

        // 3. Header Tabel
        $headers = ['Tank Code', 'Capacity', 'Oil Code', 'Description', 'Gauge Board', 'Temperature', 'Current Value'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $col++;
        }
        $sheet->getStyle('A3:G3')->applyFromArray($styleBorder);
        $sheet->getStyle('A3:G3')->applyFromArray($styleCenter);

        // 4. Proses Data
        $tanks = OilBatchRefineryTank::orderBy('group_name')->orderBy('sort_order')->get();

        $query = OilBatchRefineryReading::whereBetween('reading_date', [$startDate, $endDate]);
        if ($shiftNumber !== 'ALL') {
            $query->where('shift', $shiftNumber);
        }
        $allReadings = $query->get();

        $row = 4;
        foreach ($tanks as $tank) {
            // FIX: Menggunakan filter untuk memastikan tank_id matching dengan akurat (anti bug index UUID)
            $readings = $allReadings->filter(fn($r) => $r->tank_id === $tank->id);

            // Default nilai jika kosong
            $oilCode = '-';
            $description = $tank->description;
            $gauge = 0;
            $temp = 0;
            $currentValue = 0;

            if ($readings->isNotEmpty()) {
                if ($isRange) {
                    // Jika range, cari rata-rata & logika Various Code
                    $uniqueCodes = $readings->pluck('oil_code')->filter()->unique();
                    $oilCode = $uniqueCodes->count() > 1 ? 'Various Code' : ($uniqueCodes->first() ?? '-');

                    $gauge = round($readings->avg('gauge_board_meter') ?? 0, 2);
                    $temp = round($readings->avg('temperature_celsius') ?? 0, 2);
                    $currentValue = round($readings->avg('current_value_kg'), 2);
                } else {
                    // FIX: Sorting secara eksplisit dari Date + Shift memastikan yang diambil benar-benar Shift Terbaru
                    $latest = $readings->sortByDesc(fn($r) => $r->reading_date->format('Y-m-d') . '_' . $r->shift)->first();
                    $oilCode = $latest->oil_code ?? '-';
                    $gauge = $latest->gauge_board_meter ?? 0;
                    $temp = $latest->temperature_celsius ?? 0;
                    $currentValue = $latest->current_value_kg ?? 0;
                }
            }

            $sheet->setCellValue('A' . $row, $tank->name);
            $sheet->setCellValue('B' . $row, number_format($tank->capacity_kg));
            $sheet->setCellValue('C' . $row, $oilCode);
            $sheet->setCellValue('D' . $row, $description);
            $sheet->setCellValue('E' . $row, $gauge);
            $sheet->setCellValue('F' . $row, $temp);
            $sheet->setCellValue('G' . $row, $currentValue);

            // Warna kuning di Current Value
            $sheet->getStyle('G' . $row)->applyFromArray($styleYellow);

            $row++;
        }

        // Terapkan border ke seluruh isi tabel
        $sheet->getStyle('A3:G' . ($row - 1))->applyFromArray($styleBorder);
        foreach (range('A', 'G') as $colID) {
            $sheet->getColumnDimension($colID)->setAutoSize(true);
        }

        $fileName = 'Refinery_Report_' . str_replace(' ', '_', $titleHeader) . '_' . $startDate->format('Ymd') . '.xlsx';

        ob_clean();
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
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
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $shift = $request->input('shift', 'ALL');

        // Mengambil nama tabel secara dinamis agar aman
        $tableName = (new OilUtilityGasReading)->getTable();

        // --- 1. SNAPSHOT DATA (Visualisasi Tabung/Torpedo) ---
        $snapshotQuery = OilUtilityGasReading::with('master');

        $subquery = DB::table($tableName)
            ->select(DB::raw('max(id) as id'))
            ->whereDate('reading_date', '<=', $endDate);

        if ($shift !== 'ALL') {
            $subquery->where('shift', $shift);
        }
        $subquery->groupBy('master_id');

        $snapshotReadings = $snapshotQuery->whereIn('id', $subquery)->get();

        // --- TANGGAL TERAKHIR (GLOBAL) ---
        $lastRecordQuery = OilUtilityGasReading::latest('reading_date')->latest('shift');
        if ($shift !== 'ALL') {
            $lastRecordQuery->where('shift', $shift);
        }
        $lastRecord = $lastRecordQuery->first();

        // Format tanggal & Shift
        $lastUpdateText = $lastRecord
            ? Carbon::parse($lastRecord->reading_date)->format('l, d F Y') . ($shift === 'ALL' ? ' (Shift ' . $lastRecord->shift . ')' : '')
            : '-';

        $snapshotData = [
            'hydrogen' => $snapshotReadings->where('master.gas_type', 'HYDROGEN')->map(fn($r) => [
                'unit_name' => $r->master->name,
                'value' => $r->value
            ])->values(),
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
        $rangeQuery = OilUtilityGasReading::with('master')
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date');

        if ($shift !== 'ALL') {
            $rangeQuery->where('shift', $shift);
        }

        $rangeReadings = $rangeQuery->get();

        $labels = $rangeReadings->pluck('reading_date')
            ->unique()
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->sort()
            ->values();

        // Fungsi Helper: Jika ALL shift, hitung rata-rata per hari agar grafik rapi
        $prepareData = function ($gasType, $searchName = null) use ($rangeReadings, $labels, $shift) {
            $q = $rangeReadings->where('master.gas_type', $gasType);

            if ($searchName) {
                $q = $q->filter(fn($item) => stripos($item->master->name, $searchName) !== false);
            }

            $grouped = $q->groupBy(fn($i) => Carbon::parse($i->reading_date)->format('Y-m-d'));

            return $labels->map(function ($date) use ($grouped, $shift) {
                if (!isset($grouped[$date]))
                    return null;
                return $shift === 'ALL' ? round($grouped[$date]->avg('value'), 2) : $grouped[$date]->first()->value;
            })->all();
        };

        // A. Hydrogen 
        $h2Masters = OilUtilityGasMaster::where('gas_type', 'HYDROGEN')->where('is_active', 1)->get();
        $h2Trends = [];
        foreach ($h2Masters as $master) {
            $data = $rangeReadings->where('master_id', $master->id)
                ->groupBy(fn($i) => Carbon::parse($i->reading_date)->format('Y-m-d'));

            $h2Trends[] = [
                'label' => $master->name,
                'data' => $labels->map(function ($date) use ($data, $shift) {
                    if (!isset($data[$date]))
                        return null;
                    return $shift === 'ALL' ? round($data[$date]->avg('value'), 2) : $data[$date]->first()->value;
                })->all()
            ];
        }

        // B. Nitrogen
        $n2Master = OilUtilityGasMaster::where('gas_type', 'NITROGEN')->where('is_active', 1)->first();
        $n2Data = [];
        if ($n2Master) {
            $data = $rangeReadings->where('master_id', $n2Master->id)
                ->groupBy(fn($i) => Carbon::parse($i->reading_date)->format('Y-m-d'));
            $n2Data = $labels->map(function ($date) use ($data, $shift) {
                if (!isset($data[$date]))
                    return null;
                return $shift === 'ALL' ? round($data[$date]->avg('value'), 2) : $data[$date]->first()->value;
            })->all();
        }

        // C. Ammonia
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
            'last_update_label' => $lastUpdateText
        ]);
    }

    // --- TAMBAH METHOD BARU UNTUK EKSPOR ---
    public function exportUtilityGasData(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $exportType = $request->input('export_type', 'daily');
        $isRange = !$startDate->isSameDay($endDate);

        if ($exportType === 'daily') {
            $titleHeader = "Daily Oil Stock Daily";
            $shiftNumber = 'ALL';
        } else {
            $shiftNumber = str_replace('shift_', '', $exportType);
            $titleHeader = "Daily Oil Stock Shift " . $shiftNumber;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Setup Styling
        $styleHeaderTitle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $styleOrange = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFC000']],
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $styleCenter = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['bold' => true]
        ];

        // Header Utama Sesuai Request
        $sheet->setCellValue('A1', $titleHeader);
        $sheet->getStyle('A1')->applyFromArray($styleHeaderTitle);
        $sheet->mergeCells('A1:G1');

        // Fungsi Helper untuk ambil data (Rata-rata bila range, Nilai terbaru bila snapshot)
        $getGasValue = function ($gasType, $searchName = null) use ($startDate, $endDate, $shiftNumber, $isRange) {
            $q = OilUtilityGasReading::with('master')
                ->whereHas('master', function ($m) use ($gasType) {
                    $m->where('gas_type', $gasType);
                })
                ->whereBetween('reading_date', [$startDate, $endDate]);

            if ($shiftNumber !== 'ALL') {
                $q->where('shift', $shiftNumber);
            }

            $readings = $q->get();

            if ($searchName) {
                $readings = $readings->filter(fn($item) => stripos($item->master->name, $searchName) !== false);
            }

            if ($readings->isEmpty())
                return 0;

            if ($isRange) {
                return round($readings->avg('value'), 2);
            } else {
                return $readings->sortByDesc('reading_date')->sortByDesc('shift')->first()->value ?? 0;
            }
        };

        // --- SECTION: HYDROGEN (Kiri Atas) ---
        $sheet->setCellValue('A3', 'Hydrogen');
        $sheet->mergeCells('A3:B3');
        $sheet->getStyle('A3:B3')->applyFromArray($styleOrange);

        $sheet->setCellValue('A4', 'Torpedo No.');
        $sheet->setCellValue('B4', 'Pressure');
        $sheet->setCellValue('C4', ''); // Sesuai kolom kosong unit

        $hRow = 5;
        $hMasters = OilUtilityGasMaster::where('gas_type', 'HYDROGEN')->where('is_active', 1)->get();

        foreach ($hMasters as $master) {
            $val = $getGasValue('HYDROGEN', $master->name);
            $sheet->setCellValue('A' . $hRow, $master->name); // cth: 04
            $sheet->setCellValue('B' . $hRow, $val);
            $sheet->setCellValue('C' . $hRow, 'Bar');
            $hRow++;
        }

        $sheet->setCellValue('A' . $hRow, 'Latest Update');
        $sheet->setCellValue('B' . $hRow, $endDate->format('d/m/Y'));
        $sheet->getStyle('A4:C' . $hRow)->applyFromArray($styleBorder);
        $sheet->getStyle('A4:B4')->applyFromArray($styleCenter);

        // --- SECTION: NITROGEN (Kanan Atas) ---
        $sheet->setCellValue('E3', 'Nitrogen');
        $sheet->getStyle('E3')->applyFromArray($styleOrange);

        $sheet->setCellValue('F3', 'Stock');
        $sheet->mergeCells('F3:G3');
        $sheet->getStyle('F3:G3')->applyFromArray($styleCenter);
        $sheet->getStyle('F3:G3')->applyFromArray($styleOrange);

        $nVal = $getGasValue('NITROGEN'); // Current stock nitrogen
        // Asumsi minimum statis (seperti gambar) atau anda bisa ubah jika di DB dinamis
        $nMin = 65;

        $sheet->setCellValue('E4', 'Current Stock');
        $sheet->setCellValue('F4', $nVal);
        $sheet->setCellValue('G4', 'Inch Water');

        $sheet->setCellValue('E5', 'Minimum');
        $sheet->setCellValue('F5', $nMin);
        $sheet->setCellValue('G5', 'Inch Water');

        $sheet->setCellValue('E6', 'Latest Update');
        $sheet->setCellValue('F6', $endDate->format('d/m/Y'));
        $sheet->mergeCells('F6:G6');

        $sheet->getStyle('E3:G6')->applyFromArray($styleBorder);

        // --- SECTION: AMMONIA (Kiri Bawah, di bawah Hydrogen) ---
        $amRow = $hRow + 2;
        $sheet->setCellValue('A' . $amRow, 'Ammonia');
        $sheet->getStyle('A' . $amRow)->applyFromArray($styleOrange);

        $sheet->setCellValue('B' . $amRow, 'Stock');
        $sheet->getStyle('B' . $amRow)->applyFromArray($styleCenter);
        $sheet->getStyle('B' . $amRow)->applyFromArray($styleOrange);

        $amFull = $getGasValue('AMMONIA', 'Full');
        $amEmpty = $getGasValue('AMMONIA', 'Empty');

        $sheet->setCellValue('A' . ($amRow + 1), 'Full');
        $sheet->setCellValue('B' . ($amRow + 1), $amFull);
        $sheet->setCellValue('C' . ($amRow + 1), 'Cylinder');

        $sheet->setCellValue('A' . ($amRow + 2), 'Empty');
        $sheet->setCellValue('B' . ($amRow + 2), $amEmpty);
        $sheet->setCellValue('C' . ($amRow + 2), 'Cylinder');

        $sheet->setCellValue('A' . ($amRow + 3), 'Latest Update');
        $sheet->setCellValue('B' . ($amRow + 3), $endDate->format('d/m/Y'));
        $sheet->mergeCells('B' . ($amRow + 3) . ':C' . ($amRow + 3));

        $sheet->getStyle('A' . $amRow . ':C' . ($amRow + 3))->applyFromArray($styleBorder);

        // Rapihkan lebar kolom
        foreach (range('A', 'G') as $colID) {
            $sheet->getColumnDimension($colID)->setAutoSize(true);
        }

        $fileName = 'Utility_Gas_Report_' . str_replace(' ', '_', $titleHeader) . '_' . $startDate->format('Ymd') . '.xlsx';

        ob_clean();
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}