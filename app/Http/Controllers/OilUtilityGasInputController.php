<?php

namespace App\Http\Controllers;

use App\Models\OilUtilityGasMaster;
use App\Models\OilUtilityGasReading;
use App\Models\OilUtilityGasLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\HasShiftLogic; // Tambahkan ini

class OilUtilityGasInputController extends Controller
{
    use HasShiftLogic; // Gunakan trait ini

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // 1. CEK ROLE
        $isOperator = $user->hasRole('operator_oil') || $user->role == 'operator_oil';
        $isSupervisor = $user->hasRole('supervisor_oil') || $user->role == 'supervisor_oil';

        if (!$isOperator && !$isSupervisor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized: Hanya Operator/Supervisor Oil yang dapat melakukan input.'], 403);
        }

        // 2. GET CONTEXT SHIFT
        $context = $this->getShiftContext();

        // 3. VALIDASI INPUT
        $validator = Validator::make($request->all(), [
            'reading_date' => 'required|date',
            'shift'        => 'required|integer',
            'readings'     => 'present|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $inputDate = $request->reading_date;
        $inputShift = intval($request->shift);
        $userName = $user->name ?? 'System';

        // 4. CEK WINDOW WAKTU (SUPERVISOR & OPERATOR)
        $isAllowedTime = collect($context->editable_list)->contains(function ($val) use ($inputDate, $inputShift) {
            return $val['date'] == $inputDate && $val['shift'] == $inputShift;
        });

        if (!$isAllowedTime) {
            return response()->json(['status' => 'error', 'message' => 'Shift time window expired.'], 400);
        }

        // 5. OPERATOR LOGIC RESTRICTION
        if (!$isSupervisor) {
            // Operator HANYA boleh input di shift yang sedang berjalan
            if ($inputDate != $context->current_date || $inputShift != $context->current_shift) {
                return response()->json(['status' => 'error', 'message' => 'Operators can only input for the CURRENT shift.'], 403);
            }
            
            // Cek apakah data di shift tersebut sudah ada
            $exists = OilUtilityGasReading::where('reading_date', $inputDate)
                                          ->where('shift', $inputShift)
                                          ->exists();
            if ($exists) {
                return response()->json(['status' => 'error', 'message' => 'Data untuk shift ini sudah ada. Hubungi Supervisor untuk mengedit.'], 400);
            }
        }

        // 6. SIMPAN KE DATABASE
        DB::beginTransaction();
        try {
            foreach ($request->readings as $masterId => $value) {
                if ($value === null || $value === '') continue;

                $master = OilUtilityGasMaster::find($masterId);
                
                // Cek data lama berdasarkan ID, Tanggal, dan SHIFT
                $oldRecord = OilUtilityGasReading::where('master_id', $masterId)
                    ->where('reading_date', $inputDate)
                    ->where('shift', $inputShift) // Query by Shift
                    ->first();
                    
                $oldValue = $oldRecord ? $oldRecord->value : null;

                // Simpan jika nilai berubah atau data baru
                if ($oldValue != $value) {
                    OilUtilityGasReading::updateOrCreate(
                        ['master_id' => $masterId, 'reading_date' => $inputDate, 'shift' => $inputShift],
                        ['value' => $value, 'created_by' => $userName]
                    );

                    OilUtilityGasLog::create([
                        'user_name' => $userName,
                        'action' => $isSupervisor ? 'SUPERVISOR_EDIT' : 'OPERATOR_INPUT',
                        'reading_date' => $inputDate,
                        // Tambahkan info shift ke log agar mudah ditracking
                        'item_name' => "Shift $inputShift - " . $master->name,
                        'old_value' => $oldValue,
                        'new_value' => $value
                    ]);
                }
            }
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data Utility Gas berhasil disimpan!',
                'redirect_url' => route('oil.input_station.index') 
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}