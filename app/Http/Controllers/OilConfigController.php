<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OilShift;

class OilConfigController extends Controller
{
    // Halaman Utama Config Center
    public function index()
    {
        return view('oil.config.center');
    }

    // Halaman Edit Shift
    public function shifts()
    {
        $shifts = OilShift::all();
        return view('oil.config.shifts', compact('shifts'));
    }

    // Update Jam Shift
    public function updateShift(Request $request, $id)
    {
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $shift = OilShift::findOrFail($id);
        $shift->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return back()->with('success', 'Shift time updated successfully.');
    }
}