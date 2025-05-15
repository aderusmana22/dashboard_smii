<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::all();
        return view('roleuser.department.index', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required',
        ]);

        $department = new Department();
        $department->department_name = $request->department_name;
        $department->save();
        Alert::toast('Department created successfully!', 'success');
        return redirect()->route('department.index');
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {

        $request->validate([
            'department_name' => 'required',
        ]);

        $department->update($request->all());
        Alert::toast('Department Updated successfully!','success');
        return redirect()->route('department.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();
        // alert()->success('Department deleted successfully!', 'Department has been deleted.');
        Alert::toast('Department deleted successfully!','success');

        return redirect()->route('department.index');
    }
}
