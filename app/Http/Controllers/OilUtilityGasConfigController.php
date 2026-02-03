<?php

namespace App\Http\Controllers;

use App\Models\OilUtilityGasMaster;
use Illuminate\Http\Request;

class OilUtilityGasConfigController extends Controller
{
    public function index()
    {
        // Ambil data dan kelompokkan berdasarkan Gas Type untuk tampilan Card
        $masters = OilUtilityGasMaster::orderBy('sort_order')->get()->groupBy('gas_type');
        return view('oil.gas_utility.config', compact('masters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'gas_type' => 'required|in:HYDROGEN,NITROGEN,AMMONIA',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'input_type' => 'required|in:number,stepper',
            'min_limit' => 'nullable|numeric',
            'max_limit' => 'nullable|numeric',
            'sort_order' => 'required|integer',
        ]);

        OilUtilityGasMaster::create($request->all());

        return redirect()->back()->with('success', 'New item added successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'input_type' => 'required|in:number,stepper',
            'min_limit' => 'nullable|numeric',
            'max_limit' => 'nullable|numeric',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        $master = OilUtilityGasMaster::findOrFail($id);

        // HANYA update field yang divalidasi di atas (gas_type tidak termasuk)
        $master->update($validated);

        return redirect()->back()->with('success', 'Configuration updated.');
    }

    public function destroy($id)
    {
        $master = OilUtilityGasMaster::findOrFail($id);

        // Hapus item (Hati-hati: Data history reading juga akan terhapus karena cascade)
        // Jika ingin aman, sebaiknya gunakan Soft Deletes atau set is_active = 0 saja.
        $master->delete();

        return redirect()->back()->with('success', 'Item deleted successfully.');
    }
}