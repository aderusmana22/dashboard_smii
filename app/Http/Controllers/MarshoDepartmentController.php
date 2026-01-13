<?php

namespace App\Http\Controllers;

use App\Models\MarshoDepartment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarshoDepartmentController extends Controller
{
    /**
     * Menampilkan view utama dan mengirimkan semua data awal.
     * Kita menggunakan get() bukan paginate() karena state akan dikelola di frontend.
     */
    public function index()
    {
        $departments = MarshoDepartment::withCount('marshoUsers')->latest()->get();
        return view('jobs.departments.index', compact('departments'));
    }

    /**
     * Menyimpan department baru dan mengembalikan data yang baru dibuat sebagai JSON.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'department_name' => 'required|string|max:255|unique:marsho_departments,department_name',
        ]);

        $department = MarshoDepartment::create($validatedData);
        $department->loadCount('marshoUsers'); // Muat count setelah dibuat

        return response()->json([
            'department' => $department, 
            'message' => 'Department created successfully.'
        ], 201); // 201 Created
    }

    /**
     * Memperbarui department yang ada dan mengembalikan data yang telah diubah sebagai JSON.
     */
    public function update(Request $request, MarshoDepartment $marshoDepartment)
    {
        $validatedData = $request->validate([
            'department_name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('marsho_departments')->ignore($marshoDepartment->id)
            ],
        ]);

        $marshoDepartment->update($validatedData);
        $marshoDepartment->loadCount('marshoUsers'); // Muat ulang count

        return response()->json([
            'department' => $marshoDepartment,
            'message' => 'Department updated successfully.'
        ], 200); // 200 OK
    }

    /**
     * Menghapus department dengan validasi bisnis, mengembalikan status via JSON.
     */
    public function destroy(MarshoDepartment $marshoDepartment)
    {
        if ($marshoDepartment->marshoUsers()->exists()) {
            return response()->json([
                'message' => 'Cannot delete department: It is still assigned to one or more users.'
            ], 422); // 422 Unprocessable Entity
        }

        $marshoDepartment->delete();

        return response()->json(null, 204); // 204 No Content (Sukses tanpa body)
    }
}