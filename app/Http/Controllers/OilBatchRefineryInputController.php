<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OilBatchRefineryTank;
use App\Models\OilBatchRefineryReading;
use App\Models\OilBatchRefineryLog;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OilBatchRefineryInputController extends Controller
{
    private $stepOrder = [
        'Hydro', 
        'N.W.B', 
        'Deodorizer', 
        'Drop Tank', 
        'Wead Tank', 
        'Crystalizer', 
        'SX Tank'
    ];

    /**
     * FUNGSI LAMA UNTUK ADMIN PANEL (Tidak diubah)
     * Tetap berfungsi jika diakses dari dalam sistem.
     */
    public function index()
    {
        $data = $this->prepareDataForInput();
        
        if (isset($data['session_active']) && $data['session_active']) {
             return view('oil.batch_refinery.input_step', $data);
        }
        return view('oil.batch_refinery.input_start');
    }

    /**
     * FUNGSI BARU: Menyiapkan data untuk view.
     * Dapat dipanggil oleh InputStationController.
     */
    public function prepareDataForInput()
    {
        if (!Session::has('br_session_active')) {
            return ['session_active' => false];
        }

        $step = Session::get('br_step', 0);

        if ($step >= count($this->stepOrder)) {
             Session::forget(['br_session_active', 'br_step']);
             return ['session_active' => false, 'finished' => true];
        }

        $groupName = $this->stepOrder[$step];
        $tanks = OilBatchRefineryTank::where('group_name', $groupName)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $progress = round((($step) / count($this->stepOrder)) * 100);
        $isLastStep = ($step === count($this->stepOrder) - 1);
        $session_active = true; // Tambahkan ini agar variabel ada

        return compact('session_active', 'tanks', 'groupName', 'step', 'progress', 'isLastStep');
    }

    /**
     * DIUBAH: Redirect ke stasiun input setelah memulai sesi.
     */
    public function startSession(Request $request)
    {
        Session::put('br_session_active', true);
        Session::put('br_step', 0);
        
        OilBatchRefineryLog::create([
            'user_id' => Auth::id(),
            'action' => 'SESSION_START',
            'details' => 'Started daily input sequence',
            'ip_address' => $request->ip()
        ]);

        // PERUBAHAN: Redirect ke stasiun input dengan tipe yang benar
        return redirect()->route('input_station.index', ['type' => 'batch_refinery']);
    }

    /**
     * DIUBAH: Redirect ke stasiun input setelah menyimpan data step.
     */
    public function storeStep(Request $request)
    {
        $step = Session::get('br_step', 0);
        
        // Cek jika sesi sudah tidak valid
        if ($step >= count($this->stepOrder) || !Session::has('br_session_active')) {
             Session::forget(['br_session_active', 'br_step']);
             return redirect()->route('input_station.index')->with('success', 'Sesi input telah selesai.');
        }

        $groupName = $this->stepOrder[$step];
        $isLastStep = ($step === count($this->stepOrder) - 1);

        $request->validate([
            'readings' => 'present|array',
            'readings.*.tank_id' => 'required|exists:oil_batch_refinery_tanks,id',
            'readings.*.status' => 'required',
        ]);

        $date = Carbon::today()->format('Y-m-d');
        $user = Auth::user()->name ?? 'System';

        if ($request->has('readings')) {
            foreach ($request->readings as $r) {
                OilBatchRefineryReading::updateOrCreate(
                    ['tank_id' => $r['tank_id'], 'reading_date' => $date],
                    [
                        'current_value_kg' => $r['current_value_kg'] ?? 0,
                        'temperature' => $r['temperature'],
                        'gauge_board' => $r['gauge_board'],
                        'oil_code' => $r['oil_code'],
                        'description' => $r['description'],
                        'status' => $r['status'],
                        'created_by' => $user
                    ]
                );
            }
        }

        OilBatchRefineryLog::create([
            'user_id' => Auth::id(),
            'action' => 'INPUT_STEP',
            'details' => "Completed step: $groupName",
            'ip_address' => $request->ip()
        ]);

        // PERUBAHAN: Logika redirect
        if ($isLastStep) {
            // Jika ini langkah terakhir, bersihkan sesi dan kembali ke halaman utama
            Session::forget(['br_session_active', 'br_step']);
            return redirect()->route('input_station.index')
                             ->with('success', 'Input Batch Refinery berhasil diselesaikan!');
        } else {
            // Jika belum selesai, lanjutkan ke step berikutnya
            Session::put('br_step', $step + 1);
            return redirect()->route('input_station.index', ['type' => 'batch_refinery']);
        }
    }

    /**
     * DIUBAH: Redirect ke stasiun input utama setelah reset.
     */
    public function resetSession()
    {
        Session::forget(['br_session_active', 'br_step']);
        // PERUBAHAN: Redirect ke halaman utama stasiun input
        return redirect()->route('oil.input_station.index');
    }
}