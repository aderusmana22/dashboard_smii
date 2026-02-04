<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OilBatchRefineryTank;
use App\Models\OilBatchRefineryReading;
use App\Models\OilBatchRefineryLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OilBatchRefineryController extends Controller
{
    public function index()
    {
        return view('oil.batch_refinery.index');
    }

    public function logs()
    {
        $logs = OilBatchRefineryLog::latest()->paginate(20);
        return view('oil.batch_refinery.logs', compact('logs'));
    }

    public function getData(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today()->subDays(7);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today();

        $latestIds = OilBatchRefineryReading::select(DB::raw('MAX(id) as id'))
            ->where('reading_date', '<=', $endDate)
            ->groupBy('tank_id');

        $tanks = OilBatchRefineryTank::with(['readings' => function ($q) use ($latestIds) {
            $q->whereIn('id', $latestIds);
        }])->orderBy('sort_order')->get();

        $tableData = $tanks->map(function ($tank) {
            $reading = $tank->readings->first();
            return [
                'tank_code' => $tank->code,
                'name' => $tank->name,
                'capacity_kg' => number_format($tank->capacity_kg),
                'oil_code' => $reading ? $reading->oil_code : '-',
                'description' => $reading ? $reading->description : '-',
                'gauge_board' => $reading ? number_format($reading->gauge_board, 2) : '-',
                'temperature' => $reading ? number_format($reading->temperature, 1) : '-',
                'current_value' => $reading ? number_format($reading->current_value_kg) : '0',
                'status' => $reading ? $reading->status : 'N/A',
                'last_update' => $reading ? Carbon::parse($reading->reading_date)->format('d M Y') : '-',
            ];
        });

        $chartRaw = OilBatchRefineryReading::with('tank')
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->get();

        $chartDetailData = $chartRaw->groupBy(fn($i) => $i->reading_date)
            ->map(fn($dateGroup) => $dateGroup->groupBy('tank.group_name')
                ->map(fn($groupReadings) => $groupReadings->map(fn($r) => [
                    'name' => $r->tank->name,
                    'value' => $r->current_value_kg
                ]))
            );

        return response()->json([
            'tableData' => $tableData,
            'chartDetailData' => $chartDetailData
        ]);
    }
}