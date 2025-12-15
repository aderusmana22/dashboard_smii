<?php

namespace App\Http\Controllers;

use App\Models\Tank;
use App\Models\OilStockReading;
use App\Models\ProductionTank;
use App\Models\ProductionTankReading;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
}

