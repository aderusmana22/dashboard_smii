<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskApprovalRequestMail;
use App\Mail\TaskStatusUpdateMail;
// use Illuminate\Support\Str; // Not strictly needed here if model handles token

class KanbanController extends Controller
{
    // Helper function for test email mapping
    private function getTestRecipientEmailForDepartment(Department $department): ?string
    {
        $testEmail = 'yogaardiansyah04@gmail.com';
        $targetDepartmentsForTest = [
            'Engineering & Maintainance',
            'Finance Admin',
            'HCD',
            'Manufacturing',
            'QM & HSE',
            'R&D',
            'Sales & Marketing',
            'Supply Chain',
            'Secret'
        ];

        if (in_array($department->department_name, $targetDepartmentsForTest)) {
            return $testEmail;
        }

        // Fallback: If you want ALL department emails to go to the test email for now
        // return $testEmail;

        // Fallback: If you only want the listed departments to go to the test email,
        // and others to use a different logic (or not send for testing)
        // For now, let's make it so only the listed ones use the test email, others will be skipped
        // or use a default if you had one.
        Log::info("Department '{$department->department_name}' not in test list, email not sent to test address.");
        return null; // Or return a default admin email if you prefer for non-listed ones
    }

    public function index()
    {
        // ... (index method remains the same)
        $user = Auth::user();
        Log::info('KanbanController@index: Fetching tasks for user ID ' . $user->id);

        // Base query
        $tasksQuery = Task::with(['pengaju', 'department', 'penutup', 'approver'])
                         ->orderBy('created_at', 'desc');

        $tasksQuery->where(function ($query) {
            $query->whereNotIn('status', [Task::STATUS_CLOSED])
                  ->orWhere(function($q){
                      $q->where('status', Task::STATUS_CLOSED)
                        ->where('closed_at', '>', Carbon::now()->subDays(30));
                  });
        });

        if (!($user->isSuperAdmin() || $user->isAdminProject())) {
            $tasksQuery->where(function ($query) use ($user) {
                $query->where('status', '!=', Task::STATUS_COMPLETED)
                      ->orWhere(function ($q) use ($user) {
                          $q->where('status', Task::STATUS_COMPLETED)
                            ->where('department_id', $user->department_id);
                      })
                      ->orWhere('pengaju_id', $user->id);
            });
        }

        $tasks = $tasksQuery->get();
        $departments = Department::orderBy('department_name')->get();

        $pendingApprovalTasks = $tasks->where('status', Task::STATUS_PENDING_APPROVAL);
        $openTasks = $tasks->where('status', Task::STATUS_OPEN);
        $completedTasks = $tasks->where('status', Task::STATUS_COMPLETED);
        $closedTasks = $tasks->where('status', Task::STATUS_CLOSED);
        $rejectedTasks = $tasks->where('status', Task::STATUS_REJECTED);
        $cancelledTasks = $tasks->where('status', Task::STATUS_CANCELLED);

        return view('page.kanban.index', compact(
            'pendingApprovalTasks',
            'openTasks',
            'completedTasks',
            'closedTasks',
            'rejectedTasks',
            'cancelledTasks',
            'departments',
            'user'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_job' => 'required|string|max:255|unique:tasks,id_job',
            'department_id' => 'required|exists:departments,id',
            'area' => 'required|string|max:255',
            'list_job' => 'required|string',
            'tanggal_job_mulai' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            Log::error('KanbanController@store: Validation failed.', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['pengaju_id'] = Auth::id();
        $data['status'] = Task::STATUS_PENDING_APPROVAL;

        if (empty($data['tanggal_job_mulai'])) {
            $data['tanggal_job_mulai'] = Carbon::today()->format('Y-m-d');
        } else {
            $data['tanggal_job_mulai'] = Carbon::parse($data['tanggal_job_mulai'])->format('Y-m-d');
        }

        try {
            $task = Task::create($data);
            $task->load(['pengaju', 'department']); // Ensure department is loaded

            // MODIFIED: Use the helper for test email
            $departmentApproverEmail = $this->getTestRecipientEmailForDepartment($task->department);

            if ($departmentApproverEmail && filter_var($departmentApproverEmail, FILTER_VALIDATE_EMAIL)) {
                 Log::info('KanbanController@store: Sending approval email for Task ID ' . $task->id . ' to test address: ' . $departmentApproverEmail);
                 Mail::to($departmentApproverEmail)->send(new TaskApprovalRequestMail($task));
            } else {
                Log::warning('KanbanController@store: No valid test email determined for department ID ' . $task->department_id . ' ('.$task->department->department_name.'). Approval email not sent.');
            }

            Log::info('KanbanController@store: Task created, pending approval.', $task->toArray());
            return response()->json($task, 201);
        } catch (\Exception $e) {
            Log::error('KanbanController@store: Error creating task.', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal membuat task di server: ' . $e->getMessage()], 500);
        }
    }

    public function handleApproval(Request $request, $token)
    {
        // ... (handleApproval method remains the same)
        $task = Task::where('approval_token', $token)->where('status', Task::STATUS_PENDING_APPROVAL)->first();

        if (!$task) {
            return view('page.kanban.approval_feedback', ['message' => 'Token persetujuan tidak valid atau tugas sudah diproses.']);
        }

        $action = $request->query('action');
        $user = Auth::user();

        if ($action === 'approve') {
            $task->status = Task::STATUS_OPEN;
            $task->approver_id = $user ? $user->id : null;
            $task->approved_at = Carbon::now();
            $task->approval_token = null;
            $task->save();

            Mail::to($task->pengaju->email)->send(new TaskStatusUpdateMail(
                $task,
                'Tugas Disetujui: ' . $task->id_job,
                'Tugas yang Anda ajukan (' . $task->id_job . ') telah disetujui oleh departemen ' . $task->department->department_name . ' dan sekarang berstatus OPEN.'
            ));
            return view('page.kanban.approval_feedback', ['message' => 'Tugas berhasil disetujui dan sekarang berstatus OPEN.']);

        } elseif ($action === 'reject') {
            if ($request->isMethod('post')) {
                $request->validate(['rejection_reason' => 'required|string|max:1000']);
                $task->status = Task::STATUS_REJECTED;
                $task->approver_id = $user ? $user->id : null;
                $task->approved_at = Carbon::now();
                $task->rejection_reason = $request->input('rejection_reason');
                $task->approval_token = null;
                $task->save();

                Mail::to($task->pengaju->email)->send(new TaskStatusUpdateMail(
                    $task,
                    'Tugas Ditolak: ' . $task->id_job,
                    'Tugas yang Anda ajukan (' . $task->id_job . ') telah ditolak oleh departemen ' . $task->department->department_name . '.'
                ));
                return view('page.kanban.approval_feedback', ['message' => 'Tugas berhasil ditolak.']);
            }
            return view('page.kanban.reject_task_form', compact('task', 'token'));
        }
        return view('page.kanban.approval_feedback', ['message' => 'Aksi tidak valid.']);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        Log::info('KanbanController@updateStatus: User ID ' . $user->id . ' updating status for task ID ' . $task->id, $request->all());

        $allowedStatuses = [
            Task::STATUS_OPEN, Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED
        ];
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:' . implode(',', $allowedStatuses),
        ]);

        if ($validator->fails()) {
            // ... (validation fail logic)
            Log::error('KanbanController@updateStatus: Validation failed for task ID ' . $task->id, $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newStatus = $request->input('status');
        $oldStatus = $task->status;

        // ... (permission checks from previous step) ...
        if ($newStatus === Task::STATUS_COMPLETED) {
            if (!($user->id === $task->pengaju_id || $user->isSuperAdmin() || $user->isAdminProject())) {
                return response()->json(['message' => 'Anda tidak memiliki izin untuk menyelesaikan task ini.'], 403);
            }
        } elseif ($newStatus === Task::STATUS_CLOSED) {
            if (!($user->id === $task->pengaju_id)) {
                return response()->json(['message' => 'Hanya pengaju yang dapat mengarsipkan task ini.'], 403);
            }
            if ($oldStatus !== Task::STATUS_COMPLETED) {
                return response()->json(['message' => 'Task hanya bisa diarsipkan jika sudah COMPLETED.'], 403);
            }
        } elseif ($newStatus === Task::STATUS_CANCELLED) {
             if (!($user->id === $task->pengaju_id)) {
                return response()->json(['message' => 'Hanya pengaju yang dapat membatalkan task ini.'], 403);
            }
            if ($oldStatus !== Task::STATUS_OPEN) { // Can only cancel OPEN tasks
                return response()->json(['message' => 'Task hanya bisa dibatalkan jika berstatus OPEN.'], 403);
            }
        }
        elseif ($newStatus === Task::STATUS_OPEN && in_array($oldStatus, [Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED, Task::STATUS_REJECTED])) {
            if (!($user->isAdminProject() || $user->isSuperAdmin())) {
                return response()->json(['message' => 'Hanya Admin Project atau Super Admin yang bisa membuka kembali task.'], 403);
            }
        }


        $task->status = $newStatus;

        if ($newStatus === Task::STATUS_COMPLETED && $oldStatus !== Task::STATUS_COMPLETED) {
            $task->tanggal_job_selesai = Carbon::today()->format('Y-m-d');
            if ($task->pengaju_id !== $user->id) {
                Mail::to($task->pengaju->email)->send(new TaskStatusUpdateMail(
                    $task,
                    'Tugas Selesai: ' . $task->id_job,
                    'Tugas (' . $task->id_job . ') telah ditandai sebagai SELESAI.'
                ));
            }
        } elseif ($newStatus === Task::STATUS_OPEN) {
            $task->tanggal_job_selesai = null;
            $task->penutup_id = null;
            $task->closed_at = null;
            $task->rejection_reason = null;
            $task->approved_at = null; // Reset approval details if reopened
            $task->approver_id = null;
            // If re-opening from pending_approval (by admin), regenerate token or handle flow
            if ($oldStatus === Task::STATUS_PENDING_APPROVAL) {
                // This case might need specific logic if an admin forces it open
                // For now, we assume re-open is from other states
            }

        } elseif ($newStatus === Task::STATUS_CLOSED && $oldStatus !== Task::STATUS_CLOSED) {
            $task->penutup_id = $user->id;
            $task->closed_at = Carbon::now();
            if (!$task->tanggal_job_selesai && $oldStatus === Task::STATUS_COMPLETED) {
                $task->tanggal_job_selesai = Carbon::today()->format('Y-m-d');
            }
        } elseif ($newStatus === Task::STATUS_CANCELLED && $oldStatus !== Task::STATUS_CANCELLED) {
            // MODIFIED: Use the helper for test email
            $task->load('department'); // Ensure department is loaded
            $departmentContactEmail = $this->getTestRecipientEmailForDepartment($task->department);

            if ($departmentContactEmail && filter_var($departmentContactEmail, FILTER_VALIDATE_EMAIL)) {
                 Log::info('KanbanController@updateStatus: Sending cancellation email for Task ID ' . $task->id . ' to test address: ' . $departmentContactEmail);
                 Mail::to($departmentContactEmail)->send(new TaskStatusUpdateMail(
                    $task,
                    'Tugas Dibatalkan: ' . $task->id_job,
                    'Tugas (' . $task->id_job . ') untuk departemen Anda (' . $task->department->department_name . ') telah dibatalkan oleh pengaju: ' . $task->pengaju->name . '.',
                    null // Recipient is department (test admin), not pengaju for this specific mail
                ));
            } else {
                Log::warning('KanbanController@updateStatus: No valid test email determined for department ID ' . $task->department_id . ' ('.$task->department->department_name.'). Cancellation email not sent.');
            }
        }


        try {
            $task->save();
            $task->load(['pengaju', 'department', 'penutup', 'approver']);
            Log::info('KanbanController@updateStatus: Task ID ' . $task->id . ' status updated to ' . $newStatus);
            return response()->json($task);
        } catch (\Exception $e) {
            Log::error('KanbanController@updateStatus: Error updating task status for task ID ' . $task->id, ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal memperbarui status task: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Task $task)
    {
        // ... (destroy method remains the same)
        $user = Auth::user();
        Log::info('KanbanController@destroy: User ID ' . $user->id . ' attempting to delete task ID ' . $task->id);

        if (!($user->isAdminProject() || $user->isSuperAdmin())) {
            Log::warning('KanbanController@destroy: Unauthorized attempt to delete task ID ' . $task->id . ' by user ID ' . $user->id);
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus permanen task ini.'], 403);
        }

        if (!in_array($task->status, [Task::STATUS_REJECTED, Task::STATUS_CANCELLED, Task::STATUS_CLOSED])) {
            Log::warning('KanbanController@destroy: Attempt to delete task ID ' . $task->id . ' with status ' . $task->status);
            return response()->json(['message' => 'Task hanya bisa dihapus permanen jika berstatus REJECTED, CANCELLED, atau CLOSED.'], 403);
        }

        try {
            $task->delete();
            Log::info('KanbanController@destroy: Task ID ' . $task->id . ' deleted permanently.');
            return response()->json(['message' => 'Task berhasil dihapus permanen.']);
        } catch (\Exception $e) {
            Log::error('KanbanController@destroy: Error deleting task ID ' . $task->id, ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menghapus task: ' . $e->getMessage()], 500);
        }
    }
}