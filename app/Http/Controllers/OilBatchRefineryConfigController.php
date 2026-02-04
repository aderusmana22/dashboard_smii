<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OilBatchRefineryTank;
use App\Models\OilBatchRefineryLog;
use Illuminate\Support\Facades\Auth;

class OilBatchRefineryConfigController extends Controller
{
    public function index()
    {
        $tanks = OilBatchRefineryTank::orderBy('group_name')->orderBy('sort_order')->get();
        return view('oil.batch_refinery.config', compact('tanks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'code' => 'required|unique:oil_batch_refinery_tanks,code',
            'capacity_kg' => 'required|numeric',
            'group_name' => 'required|in:Hydro,N.W.B,Deodorizer,Drop Tank,Wead Tank,Crystalizer,SX Tank',
            'sort_order' => 'required|integer'
        ]);
        
        OilBatchRefineryTank::create($validated);
        
        OilBatchRefineryLog::create([
            'user_id' => Auth::id(), 'action' => 'CONFIG_ADD',
            'details' => 'Added tank: ' . $validated['name'], 'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Tank Added');
    }

    public function update(Request $request, $id)
    {
        $tank = OilBatchRefineryTank::findOrFail($id);
        $tank->update($request->only(['name', 'capacity_kg', 'group_name', 'sort_order', 'is_active']));
        
        OilBatchRefineryLog::create([
            'user_id' => Auth::id(), 'action' => 'CONFIG_UPDATE',
            'details' => 'Updated tank: ' . $tank->name, 'ip_address' => $request->ip()
        ]);
        
        return back()->with('success', 'Tank Updated');
    }

    public function destroy($id)
    {
        $tank = OilBatchRefineryTank::findOrFail($id);
        $tank->delete();
        return back()->with('success', 'Tank Deleted');
    }
}