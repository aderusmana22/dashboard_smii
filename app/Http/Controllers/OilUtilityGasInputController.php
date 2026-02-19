<?php

namespace App\Http\Controllers;

use App\Models\OilUtilityGasMaster;
use App\Models\OilUtilityGasReading;
use App\Models\OilUtilityGasLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OilUtilityGasInputController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // 1. SECURITY CHECK: Hanya Operator Oil & Supervisor Oil yang boleh input
        $isOperator = $user->hasRole('operator_oil') || $user->role == 'operator_oil';
        $isSupervisor = $user->hasRole('supervisor_oil') || $user->role == 'supervisor_oil';

        if (!$isOperator && !$isSupervisor) {
            return back()->with('error', 'Unauthorized: Hanya Operator Oil yang dapat melakukan input.');
        }

        $request->validate([
            'reading_date' => 'required|date',
            'readings' => 'array',
        ]);

        $date = $request->reading_date;
        $userName = $user->name ?? 'System';

        DB::beginTransaction();
        try {
            foreach ($request->readings as $masterId => $value) {
                // Skip jika null/kosong (nilai 0 tetap diproses)
                if ($value === null || $value === '') continue;

                $master = OilUtilityGasMaster::find($masterId);
                
                $oldRecord = OilUtilityGasReading::where('master_id', $masterId)
                    ->where('reading_date', $date)
                    ->first();
                $oldValue = $oldRecord ? $oldRecord->value : null;

                // Simpan jika nilai berubah atau data baru
                if ($oldValue != $value) {
                    OilUtilityGasReading::updateOrCreate(
                        ['master_id' => $masterId, 'reading_date' => $date],
                        ['value' => $value, 'created_by' => $userName]
                    );

                    OilUtilityGasLog::create([
                        'user_name' => $userName,
                        'action' => $oldRecord ? 'UPDATE' : 'INSERT',
                        'reading_date' => $date,
                        'item_name' => $master->name,
                        'old_value' => $oldValue,
                        'new_value' => $value
                    ]);
                }
            }
            DB::commit();
            
            // --- REDIRECT KE BLANK STATE ---
            // Redirect tanpa parameter 'type' agar dropdown kembali reset
            return redirect()->route('oil.input_station.index')
                             ->with('success', 'Data Utility Gas berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}