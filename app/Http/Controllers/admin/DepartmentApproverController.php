<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepartmentApprover;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert; // If you are using SweetAlert

class DepartmentApproverController extends Controller
{
    protected function getFormData()
    {
        // Users eligible to be approvers (must have NIK and email)
        // Format for select: ['nik' => 'Name (NIK: nik)', ...]
        $users = User::whereNotNull('nik')
            ->whereNotNull('email')
            ->where('nik', '!=', '')
            ->where('email', '!=', '')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->nik => $user->name . ' (NIK: ' . $user->nik . ')'];
            });

        $departments = Department::orderBy('department_name')->pluck('department_name', 'id');
        $statuses = ['active' => 'Active', 'inactive' => 'Inactive'];
        return compact('departments', 'users', 'statuses');
    }

    public function index()
    {
        $approvers = DepartmentApprover::with(['department', 'user'])->latest()->paginate(10);
        // Data for modal dropdowns
        $formData = $this->getFormData();
        return view('department_approvers.index', compact('approvers'), $formData);
    }

    // No 'create' view needed, data provided by index for modal
    // public function create() { /* ... */ }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'user_nik' => [
                'required',
                'exists:users,nik',
                Rule::unique('department_approvers')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                }),
            ],
            'status' => 'required|in:active,inactive',
        ], [
            'user_nik.unique' => 'This combination of department and user approver already exists.',
        ]);

        try {
            DepartmentApprover::create($validatedData);
            if ($request->ajax()) {
                return response()->json(['success' => 'Department approver added successfully.']);
            }
            Alert::success('Success', 'Department approver added successfully.');
            return redirect()->route('department-approvers.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to add department approver: ' . $e->getMessage()], 500);
            }
            Alert::error('Error', 'Failed to add department approver: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(Request $request, DepartmentApprover $departmentApprover)
    {
        if ($request->ajax()) {
            $departmentApprover->load(['department', 'user']); // Eager load relationships
            return response()->json($departmentApprover);
        }
        // Fallback for non-AJAX if needed, though we aim for modal
        $departmentApprover->load(['department', 'user']);
        return view('department-approvers.show-page', compact('departmentApprover')); // A simple page if modal fails
    }


    // This method will provide data for the edit modal
    public function edit(Request $request, DepartmentApprover $departmentApprover)
    {
        if ($request->ajax()) {
            $formData = $this->getFormData();
            $currentUser = null;
            if ($departmentApprover->user) {
                $currentUser = [
                    'nik' => $departmentApprover->user_nik,
                    'name' => $departmentApprover->user->name . ' (NIK: ' . $departmentApprover->user_nik . ')'
                ];
                 // Ensure current user is in the list if not already
                if (!isset($formData['users'][$departmentApprover->user_nik])) {
                    $formData['users'][$departmentApprover->user_nik] = $currentUser['name'];
                }
            }

            return response()->json([
                'approver' => $departmentApprover,
                'departments' => $formData['departments'],
                'users' => $formData['users'],
                'statuses' => $formData['statuses'],
                'currentUser' => $currentUser // Send current user separately if needed for special handling
            ]);
        }
        // Fallback for non-AJAX if needed
        $formData = $this->getFormData();
        return view('department-approvers.edit-page', compact('departmentApprover'), $formData);
    }


    public function update(Request $request, DepartmentApprover $departmentApprover)
    {
        $validatedData = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'user_nik' => [
                'required',
                'exists:users,nik',
                Rule::unique('department_approvers')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                })->ignore($departmentApprover->id),
            ],
            'status' => 'required|in:active,inactive',
        ], [
            'user_nik.unique' => 'This combination of department and user approver already exists.',
        ]);

        try {
            $departmentApprover->update($validatedData);
            if ($request->ajax()) {
                return response()->json(['success' => 'Department approver updated successfully.']);
            }
            Alert::success('Success', 'Department approver updated successfully.');
            return redirect()->route('department-approvers.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to update department approver: ' . $e->getMessage()], 500);
            }
            Alert::error('Error', 'Failed to update department approver: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, DepartmentApprover $departmentApprover)
    {
        try {
            $departmentApprover->delete();
            if ($request->ajax()) {
                return response()->json(['success' => 'Department approver deleted successfully.']);
            }
            Alert::success('Success', 'Department approver deleted successfully.');
            return redirect()->route('department-approvers.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to delete department approver.'], 500);
            }
            Alert::error('Error', 'Failed to delete department approver.');
            return redirect()->route('department-approvers.index');
        }
    }
}   