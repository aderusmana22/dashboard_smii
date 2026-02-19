<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\HasShiftLogic;

// Models
use App\Models\OilBatchRefineryTank;
use App\Models\OilBatchRefineryReading;
use App\Models\OilUtilityGasMaster;
use App\Models\OilUtilityGasReading;

class OilInputStationController extends Controller
{
    use HasShiftLogic;

    private $batchRefineryGroupOrder = [
        'Hydro',
        'N.W.B',
        'Deodorizer',
        'Drop Tank',
        'Head Tank',
        'Crystalizer',
        'SX Tank'
    ];

    public function index(Request $request)
    {
        // Default NULL agar tampilan awal BLANK / Tertutup
        // Jangan beri default value 'batch_refinery' disini
        $type = $request->get('type', null);

        $view = null;
        $data = [];

        // --- 1. ROUTING LOGIC ---
        if ($type === 'utility_gas') {
            $data = $this->getUtilityGasData($request);
            $view = 'oil.gas_utility.partials._form';
        } elseif ($type === 'batch_refinery') {
            $data = $this->getBatchRefineryData($request);
            $view = 'oil.batch_refinery.partials._form_full';
        }

        // --- 2. RESPONSE ---
        if ($request->ajax() && $view) {
            return view($view, $data)->render();
        }

        return view('oil.input_station.index', [
            'currentType' => $type,
            // Jika type null, initialData juga null (Blank State)
            'initialData' => ($view) ? view($view, $data)->render() : null
        ]);
    }

private function getBatchRefineryData(Request $request)
    {
        $user = Auth::user();
        $isSupervisor = $user->hasRole('supervisor_oil') || $user->role === 'supervisor_oil';

        $context = $this->getShiftContext();
        $targetDate = $context->current_date;
        $targetShift = $context->current_shift;
        $isEditingPrevious = false;

        if ($isSupervisor && $request->has('target_key')) {
            $parts = explode('|', $request->target_key);
            if (count($parts) == 2) {
                $reqDate = $parts[0];
                $reqShift = intval($parts[1]);
                $isAllowed = $context->editable_list->contains(function ($val) use ($reqDate, $reqShift) {
                    return $val['date'] == $reqDate && $val['shift'] == $reqShift;
                });
                if ($isAllowed) {
                    $targetDate = $reqDate;
                    $targetShift = $reqShift;
                    if ($targetDate != $context->current_date || $targetShift != $context->current_shift) {
                        $isEditingPrevious = true;
                    }
                }
            }
        }

        $items = DB::table('items')->select('pt_part', 'pt_desc1')->whereIn('inventory_acct', ['1401', '1422'])->orderBy('pt_part', 'asc')->get();
        
        $tanks = OilBatchRefineryTank::where('is_active', true)->orderBy('sort_order')->get();
        
        // --- PERBAIKAN: Definisikan variabel $groups ---
        // Mengambil daftar nama grup unik untuk dropdown filter
        $groups = $tanks->pluck('group_name')->unique()->values(); 
        
        $groupedTanks = $tanks->groupBy('group_name')->sortBy(function ($items, $key) {
            return array_search($key, $this->batchRefineryGroupOrder) !== false ? array_search($key, $this->batchRefineryGroupOrder) : 999;
        });

        $existingReadings = OilBatchRefineryReading::where('reading_date', $targetDate)->where('shift', $targetShift)->get()->keyBy('tank_id');

        $isLocked = false;
        $lockMessage = null;
        if ($existingReadings->isNotEmpty()) {
            if (!$isSupervisor) {
                $isLocked = true;
                $lockMessage = "Laporan untuk Shift $targetShift ($targetDate) sudah selesai.";
            } else {
                $lockMessage = "Supervisor Edit Mode";
            }
        }

        // --- PERBAIKAN: Tambahkan 'groups' ke dalam compact ---
        return compact('groupedTanks', 'existingReadings', 'targetDate', 'targetShift', 'items', 'isLocked', 'lockMessage', 'isSupervisor', 'context', 'isEditingPrevious', 'groups');
    }
    private function getUtilityGasData(Request $request)
    {
        $date = $request->get('date', Carbon::now()->format('Y-m-d'));
        $masters = OilUtilityGasMaster::where('is_active', true)->orderBy('sort_order')->get()->groupBy('gas_type');
        $context = $this->getShiftContext();
        $existingReadings = OilUtilityGasReading::where('reading_date', $date)
            ->get()
            ->keyBy('master_id');
        $lastReadings = collect();

        return compact('masters', 'existingReadings', 'date', 'context', 'lastReadings');
    }
}