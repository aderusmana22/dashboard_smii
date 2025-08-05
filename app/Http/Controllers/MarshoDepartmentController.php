<?php

namespace App\Http\Controllers;

use App\Models\MarshoDepartment;
use Illuminate\Http\Request;

class MarshoDepartmentController extends Controller
{
    public function index()
    {
        $departments = MarshoDepartment::latest()->paginate(10);
        return view('resources.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255|unique:marsho_departments,department_name',
        ]);

        MarshoDepartment::create($request->all());
        return redirect()->route('marsho-departments.index')->with('success', 'Department created successfully.');
    }

    public function update(Request $request, MarshoDepartment $marshoDepartment)
    {
        $request->validate([
            'department_name' => 'required|string|max:255|unique:marsho_departments,department_name,' . $marshoDepartment->id,
        ]);

        $marshoDepartment->update($request->all());
        return redirect()->route('marsho-departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(MarshoDepartment $marshoDepartment)
    {
        // Anda bisa menambahkan validasi pengecekan jika departemen sedang digunakan
        $marshoDepartment->delete();
        return redirect()->route('marsho-departments.index')->with('success', 'Department deleted successfully.');
    }
}