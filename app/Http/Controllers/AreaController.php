<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AreaController extends Controller
{
    public function index()
    {
        // Data awal masih dikirim seperti biasa saat halaman pertama kali dimuat
        $areas = Area::latest()->get(); 
        return view('resources.areas.index', compact('areas'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:areas,name',
            'description' => 'nullable|string',
        ]);

        $area = Area::create($validatedData);

        // Mengembalikan data area yang baru dibuat sebagai JSON dengan status 201 Created
        return response()->json(['area' => $area, 'message' => 'Area created successfully.'], 201);
    }

    public function update(Request $request, Area $area)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:areas,name,' . $area->id,
            'description' => 'nullable|string',
        ]);

        $area->update($validatedData);

        // Mengembalikan data area yang telah diperbarui sebagai JSON
        return response()->json(['area' => $area, 'message' => 'Area updated successfully.']);
    }

    public function destroy(Area $area)
    {
        // Logika bisnis tetap sama, tetapi responsnya berbeda
        if ($area->jobs()->exists()) {
            // Mengembalikan respons error sebagai JSON dengan status 422 Unprocessable Entity
            return response()->json(['message' => 'Cannot delete area that is in use by a job.'], 422);
        }
        
        $area->delete();

        // Mengembalikan respons kosong dengan status 204 No Content, menandakan sukses
        return response()->json(null, 204);
    }
}