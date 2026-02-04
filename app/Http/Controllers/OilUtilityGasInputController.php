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
    /**
     * FUNGSI LAMA UNTUK ADMIN PANEL (Tidak diubah)
     * Tetap berfungsi jika diakses dari dalam sistem.
     */
    public function index(Request $request)
    {
        $data = $this->prepareDataForInput($request);
        return view('oil.gas_utility.input', $data);
    }

    /**
     * FUNGSI BARU: Menyiapkan data untuk view.
     * Dapat dipanggil oleh InputStationController.
     */
    public function prepareDataForInput(Request $request = null)
    {
        $date = $request ? $request->get('date', Carbon::now()->format('Y-m-d')) : Carbon::now()->format('Y-m-d');
        
        $masters = OilUtilityGasMaster::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('gas_type');

        $existingReadings = OilUtilityGasReading::where('reading_date', $date)
            ->pluck('value', 'master_id');

        $lastReadings = [];
        if ($existingReadings->isEmpty()) {
            $lastData = OilUtilityGasReading::where('reading_date', '<', $date)
                ->orderBy('reading_date', 'desc')
                ->get()
                ->unique('master_id');
            $lastReadings = $lastData->pluck('value', 'master_id');
        }

        return compact('masters', 'existingReadings', 'lastReadings', 'date');
    }
    
    /**
     * DIUBAH: Redirect ke stasiun input utama setelah menyimpan data.
     */
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
                if ($value === null || $value === '') continue;

                $master = OilUtilityGasMaster::find($masterId);
                
                $oldRecord = OilUtilityGasReading::where('master_id', $masterId)
                    ->where('reading_date', $date)
                    ->first();
                $oldValue = $oldRecord ? $oldRecord->value : null;

                if ($oldValue != $value) {
                    OilUtilityGasReading::updateOrCreate(
                        ['master_id' => $masterId, 'reading_date' => $date],
                        ['value' => $value, 'created_by' => $user]
                    );

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
            // PERUBAHAN: Redirect ke halaman utama stasiun input
            return redirect()->route('oil.input_station.index')->with('success', 'Data Utility Gas berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function logs()
    {
        $logs = OilUtilityGasLog::orderBy('created_at', 'desc')->paginate(20);
        return view('oil.gas_utility.logs', compact('logs'));
    }
}