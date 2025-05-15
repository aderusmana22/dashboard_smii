<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Level;
use App\Models\Position;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $positions = Position::with('department','level')->get();
        $departments = Department::all();
        $levels = Level::all();
        return view('roleuser.position.index', compact('positions','departments','levels'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'position_name' => ['required', 'string', 'max:255'],
            'department_id' => ['required'],
            'level_id' => ['required'],
        ]);

        $position = new Position();
        $position->position_name = $request->position_name;
        $position->department_id = $request->department_id;
        $position->level_id = $request->level_id;
        $position->save();
        Alert::toast('Position created successfully!', 'success');
        return redirect()->route('position.index');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position)
    {
        $request->validate([
            'position_name' => ['required', 'string', 'max:255'],
            'department_id' => ['required'],
            'level_id' => ['required'],
        ]);

        $position->update($request->all());
        Alert::toast('Position Updated successfully!','success');
        return redirect()->route('position.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        $position->delete();
        Alert::toast('Position Deleted successfully!','success');
        return redirect()->route('position.index');
    }
}
