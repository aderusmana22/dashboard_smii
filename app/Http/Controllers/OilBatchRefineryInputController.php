<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OilBatchRefineryTank;
use App\Models\OilBatchRefineryReading;
use App\Models\OilBatchRefineryLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OilBatchRefineryInputController extends Controller
{
    private $groupOrder = [
        'Hydro', 
        'N.W.B', 
        'Deodorizer', 
        'Drop Tank', 
        'Wead Tank', 
        'Crystalizer', 
        'SX Tank'
    ];

    public function index()
    {
        return redirect()->route('oil.input_station.index', ['type' => 'batch_refinery']);
    }

    public function prepareDataForInputFull()
    {
        $items = DB::table('items')
            ->select('pt_part', 'pt_desc1')
            ->whereIn('inventory_acct', ['1401', '1422'])
            ->orderBy('pt_part', 'asc')
            ->get();

        $tanks = OilBatchRefineryTank::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $groupedTanks = $tanks->groupBy('group_name')
            ->sortBy(function($items, $key) {
                return array_search($key, $this->groupOrder) !== false 
                    ? array_search($key, $this->groupOrder) 
                    : 999;
            });

        $date = Carbon::today()->format('Y-m-d');
        $existingReadings = OilBatchRefineryReading::where('reading_date', $date)
            ->get()
            ->keyBy('tank_id');

        return compact('groupedTanks', 'existingReadings', 'date', 'items');
    }

    public function storeFull(Request $request)
    {
        $request->validate([
            'readings' => 'present|array',
            'readings.*.tank_id' => 'required|exists:oil_batch_refinery_tanks,id',
            'readings.*.status' => 'required',
        ]);

        $date = Carbon::today()->format('Y-m-d');
        $user = Auth::user()->name ?? 'System';

        DB::beginTransaction();
        try {
            if ($request->has('readings')) {
                foreach ($request->readings as $r) {
                    OilBatchRefineryReading::updateOrCreate(
                        ['tank_id' => $r['tank_id'], 'reading_date' => $date],
                        [
                            'current_value_kg' => $r['current_value_kg'] ?? 0,
                            'oil_code' => $r['oil_code'] ?? null,
                            'description' => $r['description'] ?? null,
                            'status' => $r['status'],
                            'temperature' => null,
                            'gauge_board' => null,
                            'created_by' => $user
                        ]
                    );
                }
            }

            OilBatchRefineryLog::create([
                'user_id' => Auth::id(),
                'action' => 'INPUT_FULL_BATCH',
                'details' => "Updated full batch refinery report",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return redirect()->route('oil.input_station.index')
                             ->with('success', 'Data Batch Refinery berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}