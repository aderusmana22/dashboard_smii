<?php

namespace App\Http\Controllers;

use App\Models\OilUtilityGasMaster;
use App\Models\OilUtilityGasReading;
use App\Models\OilUtilityGasLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OilUtilityGasInputController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::now()->format('Y-m-d'));
        
        // Ambil Master Data
        $masters = OilUtilityGasMaster::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('gas_type');

        // Cek data existing hari ini
        $existingReadings = OilUtilityGasReading::where('reading_date', $date)
            ->pluck('value', 'master_id');

        // Logic Auto-fill: Ambil data terakhir jika hari ini kosong
        $lastReadings = [];
        if ($existingReadings->isEmpty()) {
            $lastData = OilUtilityGasReading::where('reading_date', '<', $date)
                ->orderBy('reading_date', 'desc')
                ->get()
                ->unique('master_id');
            $lastReadings = $lastData->pluck('value', 'master_id');
        }

        return view('oil.gas_utility.input', compact('masters', 'existingReadings', 'lastReadings', 'date'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reading_date' => 'required|date',
            'readings' => 'array',
        ]);

        $date = $request->reading_date;
        $user = Auth::user()->name ?? 'System';

        DB::beginTransaction();
        try {
            foreach ($request->readings as $masterId => $value) {
                // Skip jika kosong string (tapi terima 0)
                if ($value === null || $value === '') continue;

                $master = OilUtilityGasMaster::find($masterId);
                
                // Cek Data Lama untuk Log
                $oldRecord = OilUtilityGasReading::where('master_id', $masterId)
                    ->where('reading_date', $date)
                    ->first();
                $oldValue = $oldRecord ? $oldRecord->value : null;

                // Simpan jika nilai berbeda
                if ($oldValue != $value) {
                    OilUtilityGasReading::updateOrCreate(
                        ['master_id' => $masterId, 'reading_date' => $date],
                        ['value' => $value, 'created_by' => $user]
                    );

                    // Catat Log
                    OilUtilityGasLog::create([
                        'user_name' => $user,
                        'action' => $oldRecord ? 'UPDATE' : 'INSERT',
                        'reading_date' => $date,
                        'item_name' => $master->name,
                        'old_value' => $oldValue,
                        'new_value' => $value
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('utility.gas.input', ['date' => $date])->with('success', 'Data Saved Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function logs()
    {
        $logs = OilUtilityGasLog::orderBy('created_at', 'desc')->paginate(20);
        return view('oil.gas_utility.logs', compact('logs'));
    }
}