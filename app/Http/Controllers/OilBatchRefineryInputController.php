<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OilBatchRefineryReading;
use App\Models\OilBatchRefineryLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\HasShiftLogic;
use App\Models\OilBatchRefineryTank;
use Carbon\Carbon;

class OilBatchRefineryInputController extends Controller
{
    use HasShiftLogic;

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
            ->sortBy(function ($items, $key) {
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
        $user = Auth::user();

        // 1. CEK ROLE 
        $isOperator = $user->hasRole('operator_oil') || $user->role == 'operator_oil';
        $isSupervisor = $user->hasRole('supervisor_oil') || $user->role == 'supervisor_oil';

        if (!$isOperator && !$isSupervisor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $context = $this->getShiftContext();

        // 2. Validasi Input (Tambahkan validation untuk temperature & gauge_board)
        $validator = Validator::make($request->all(), [
            'readings' => 'present|array',
            'readings.*.tank_id' => 'required|exists:oil_batch_refinery_tanks,id',
            'readings.*.status' => 'required',
            'readings.*.temperature' => 'nullable|numeric', // Validasi angka
            'readings.*.gauge_board' => 'nullable',        // Bisa angka atau string
            'reading_date' => 'required|date',
            'shift' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $inputDate = $request->reading_date;
        $inputShift = intval($request->shift);

        // 3. Cek Window Waktu (Security)
        $isAllowedTime = $context->editable_list->contains(function ($val) use ($inputDate, $inputShift) {
            return $val['date'] == $inputDate && $val['shift'] == $inputShift;
        });

        if (!$isAllowedTime) {
            return response()->json(['status' => 'error', 'message' => 'Shift time window expired.'], 400);
        }

        // 4. Operator Logic
        if (!$isSupervisor) {
            if ($inputDate != $context->current_date || $inputShift != $context->current_shift) {
                return response()->json(['status' => 'error', 'message' => 'Operators can only input for the CURRENT shift.'], 403);
            }
            $exists = OilBatchRefineryReading::where('reading_date', $inputDate)->where('shift', $inputShift)->exists();
            if ($exists) {
                return response()->json(['status' => 'error', 'message' => 'Data already exists. Contact Supervisor.'], 400);
            }
        }

        // 5. Simpan ke Database
        DB::beginTransaction();
        try {
            if ($request->has('readings')) {
                foreach ($request->readings as $r) {
                    OilBatchRefineryReading::updateOrCreate(
                        [
                            'tank_id' => $r['tank_id'],
                            'reading_date' => $inputDate,
                            'shift' => $inputShift
                        ],
                        [
                            'current_value_kg' => $r['current_value_kg'] ?? 0,
                            'oil_code' => $r['oil_code'] ?? null,
                            'description' => $r['description'] ?? null,

                            // --- KOLOM BARU DITAMBAHKAN DISINI ---
                            'temperature' => $r['temperature'] ?? null,
                            'gauge_board' => $r['gauge_board'] ?? null,

                            'status' => $r['status'],
                            'created_by' => $user->name
                        ]
                    );
                }
            }

            OilBatchRefineryLog::create([
                'user_id' => Auth::id(),
                'action' => $isSupervisor ? 'SUPERVISOR_EDIT' : 'OPERATOR_INPUT',
                'details' => "Submit: $inputDate | Shift $inputShift",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data saved successfully!',
                'redirect_url' => route('oil.input_station.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()], 500);
        }
    }
}