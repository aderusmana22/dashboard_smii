<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use App\Models\JobApprovalDetail;
use App\Models\DepartmentApprover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskApprovalRequestMailHtml;
use App\Mail\TaskStatusUpdateMailHtml;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Exports\TasksExport;
use Maatwebsite\Excel\Facades\Excel;

class KanbanController extends Controller
{
    private function getRecipientEmailsForDepartment(Department $department): array
    {
        $approvers = DepartmentApprover::where('department_id', $department->id)
            ->where('status', 'active')
            ->with('user:id,nik,email,name')
            ->get();

        $emails = [];
        foreach ($approvers as $approver) {
            if ($approver->user && filter_var($approver->user->email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $approver->user->email;
            } else {
                Log::warning("DepartmentApprover ID {$approver->id} for User NIK {$approver->user_nik} has no valid user or email.");
            }
        }

        if (empty($emails)) {
            Log::warning("No active approvers with valid emails found for department '{$department->department_name}' (ID: {$department->id}).");
        }
        return array_unique($emails);
    }

    public function index(Request $request)
    {
        $user = Auth::user()->load(['department', 'position']);
        Log::info('KanbanController@index (Kanban View): Fetching tasks for user ID ' . $user->id . ' (NIK: ' . $user->nik . ')', $request->all());

        $baseTasksQuery = Task::with([
            'pengaju:id,name,nik',
            'department:id,department_name',
            'penutup:id,name,nik',
            'approvalDetails' => function ($query) {
                $query->with('approver:id,name,nik')->orderBy('processed_at', 'desc');
            }
        ])
        ->latest('tasks.created_at');

        if ($request->filled('status_filter')) {
            $baseTasksQuery->where('tasks.status', $request->input('status_filter'));
        }
        if ($request->filled('pengaju_filter')) {
            $baseTasksQuery->where('tasks.pengaju_id', $request->input('pengaju_filter'));
        }
        if ($request->filled('date_from_filter')) {
            $baseTasksQuery->whereDate('tasks.created_at', '>=', Carbon::parse($request->input('date_from_filter'))->startOfDay());
        }
        if ($request->filled('date_to_filter')) {
            $baseTasksQuery->whereDate('tasks.created_at', '<=', Carbon::parse($request->input('date_to_filter'))->endOfDay());
        }
        if ($request->filled('search_filter')) {
            $searchTerm = '%' . $request->input('search_filter') . '%';
            $baseTasksQuery->where(function ($q) use ($searchTerm) {
                $q->where('tasks.id_job', 'like', $searchTerm)
                  ->orWhere('tasks.area', 'like', $searchTerm)
                  ->orWhere('tasks.list_job', 'like', $searchTerm)
                  ->orWhereHas('pengaju', function($sq) use ($searchTerm){
                      $sq->where('name', 'like', $searchTerm)->orWhere('nik', 'like', $searchTerm);
                  })
                  ->orWhereHas('department', function($sq) use ($searchTerm){
                      $sq->where('department_name', 'like', $searchTerm);
                  });
            });
        }

        if ($user->isSuperAdmin() || $user->isAdminProject()) {
            if ($request->filled('department_filter')) {
                $baseTasksQuery->where('tasks.department_id', $request->input('department_filter'));
            }
        } else {
            $departmentFilter = $request->input('department_filter');
            if ($departmentFilter) {
                if ($departmentFilter == $user->department_id) {
                    $baseTasksQuery->where('tasks.department_id', $departmentFilter);
                } else {
                    $baseTasksQuery->where('tasks.pengaju_id', $user->id)
                               ->where('tasks.department_id', $departmentFilter);
                }
            } else {
                $baseTasksQuery->where(function ($q) use ($user) {
                    $q->where('tasks.pengaju_id', $user->id);
                    if ($user->department_id) {
                        $q->orWhere('tasks.department_id', $user->department_id);
                    }
                });
            }
        }

        $pendingApprovalTasks = (clone $baseTasksQuery)->where('status', Task::STATUS_PENDING_APPROVAL)->get();
        $rejectedTasks = (clone $baseTasksQuery)->where('status', Task::STATUS_REJECTED)->get();
        $openTasks = (clone $baseTasksQuery)->where('status', Task::STATUS_OPEN)->get();
        $completedTasks = (clone $baseTasksQuery)->where('status', Task::STATUS_COMPLETED)->get();
        $closedTasks = (clone $baseTasksQuery)->where('status', Task::STATUS_CLOSED)->get();
        $cancelledTasks = (clone $baseTasksQuery)->where('status', Task::STATUS_CANCELLED)->get();

        $departmentsForFilter = Department::orderBy('department_name')->pluck('department_name', 'id');
        $usersForFilter = User::orderBy('name')->where('status', 'active')->get()->mapWithKeys(function ($u) {
            return [$u->id => $u->name . ' (NIK: ' . $u->nik . ')'];
        });
        $taskStatusesForFilter = Task::getAvailableStatuses();
        $departments = Department::orderBy('department_name')->pluck('department_name', 'id');

        return view('page.kanban.index', compact(
            'pendingApprovalTasks',
            'rejectedTasks',
            'openTasks',
            'completedTasks',
            'closedTasks',
            'cancelledTasks',
            'user',
            'departmentsForFilter',
            'usersForFilter',
            'taskStatusesForFilter',
            'departments',
            'request'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'area' => 'required|string|max:255',
            'list_job' => 'required|string',
            'tanggal_job_mulai' => 'nullable|date_format:Y-m-d|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            Log::error('KanbanController@store: Validation failed.', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $currentYear = Carbon::now()->year;
        $prefix = "JOB/{$currentYear}/";

        $lastTask = Task::where('id_job', 'like', $prefix . '%')
                        ->orderBy('id', 'desc')
                        ->first();

        $nextNumber = 1;
        if ($lastTask) {
            $lastNumber = (int) Str::afterLast($lastTask->id_job, '/');
            $nextNumber = $lastNumber + 1;
        }

        $newIdJob = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $data = $request->all();
        $data['id_job'] = $newIdJob;
        $data['pengaju_id'] = Auth::id();
        $data['status'] = Task::STATUS_PENDING_APPROVAL;
        $data['tanggal_job_mulai'] = !empty($data['tanggal_job_mulai'])
            ? Carbon::parse($data['tanggal_job_mulai'])->format('Y-m-d')
            : Carbon::today()->format('Y-m-d');

        DB::beginTransaction();
        try {
            $task = Task::create($data);
            $task->load(['pengaju:id,name,nik', 'department:id,department_name']);

            $departmentApprovers = DepartmentApprover::where('department_id', $task->department_id)
                ->where('status', 'active')
                ->with('user:id,nik,email,name')
                ->get();

            if ($departmentApprovers->isEmpty()) {
                DB::rollBack();
                Log::error('KanbanController@store: No active approvers found for department ID ' . $task->department_id);
                return response()->json(['message' => 'No active approvers are configured for the target department. The task cannot be created.'], 422);
            }

            $approvalEmailsSentTo = [];
            foreach ($departmentApprovers as $departmentApproverInstance) {
                $approverUser = $departmentApproverInstance->user;
                if (!$approverUser || !filter_var($approverUser->email, FILTER_VALIDATE_EMAIL)) {
                    Log::warning("KanbanController@store: Approver NIK {$departmentApproverInstance->user_nik} for DeptApprover ID {$departmentApproverInstance->id} not found or has invalid email. Skipping.");
                    continue;
                }

                $token = Task::generateUniqueToken();
                $approvalDetail = $task->approvalDetails()->create([
                    'approver_nik' => $approverUser->nik,
                    'status' => JobApprovalDetail::STATUS_PENDING,
                    'token' => $token,
                ]);

                if ($approverUser->email) {
                    // Menggunakan queue untuk mengirim email di background
                    Mail::to($approverUser->email)->queue(new TaskApprovalRequestMailHtml($task, $approvalDetail));
                    $approvalEmailsSentTo[] = $approverUser->email;
                    Log::info("KanbanController@store: Queued approval email for Task ID {$task->id} to {$approverUser->email} (Approver NIK: {$approverUser->nik})");
                }
            }

            if (empty($approvalEmailsSentTo)) {
                DB::rollBack();
                Log::error('KanbanController@store: No approval emails could be queued for task ID ' . $task->id . '. All configured approvers might have invalid email addresses.');
                return response()->json(['message' => 'Failed to queue approval request email because no valid approvers could be contacted. The task cannot be created.'], 500);
            }

            DB::commit();
            $task->load(['approvalDetails.approver:id,name,nik']);
            Log::info('KanbanController@store: Task created successfully, pending approval.', $task->toArray());
            return response()->json($task, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KanbanController@store: Error creating task.', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to create the task on the server: ' . $e->getMessage()], 500);
        }
    }

    public function handleApproval(Request $request, $token)
    {
        $approvalDetail = JobApprovalDetail::where('token', $token)
            ->where('status', JobApprovalDetail::STATUS_PENDING)
            ->first();

        if (!$approvalDetail) {
            return view('page.kanban.approval_feedback', ['message' => 'The approval token is invalid, has already been processed, or has expired.']);
        }

        $task = $approvalDetail->task->load(['pengaju:id,name,email,nik', 'department:id,department_name']);
        $action = $request->query('action');
        $loggedInUser = Auth::user();
        $processingUser = $loggedInUser ?? User::where('nik', $approvalDetail->approver_nik)->first();

        if (!$processingUser) {
             Log::error("KanbanController@handleApproval: Approver user for NIK {$approvalDetail->approver_nik} not found for token {$token}.");
             return view('page.kanban.approval_feedback', ['message' => 'Approver user not found.']);
        }
        if ($loggedInUser && $loggedInUser->nik !== $approvalDetail->approver_nik) {
            Log::warning("KanbanController@handleApproval: User {$loggedInUser->nik} attempted to process approval for {$approvalDetail->approver_nik} with token {$token}.");
            return view('page.kanban.approval_feedback', ['message' => 'You are not authorized to process this approval.']);
        }

        DB::beginTransaction();
        try {
            $message = '';
            $logEvent = '';
            $emailSubject = '';
            $emailBodyToRequester = '';
            $rejectionNotes = null;

            if ($action === 'approve') {
                $approvalDetail->status = JobApprovalDetail::STATUS_APPROVED;
                $task->status = Task::STATUS_OPEN;
                $message = "Task '{$task->id_job}' has been successfully APPROVED and is now OPEN.";
                $logEvent = 'task_approved';
                $emailSubject = 'Task Approved: ' . $task->id_job;
                $emailBodyToRequester = "The task you submitted (JOB ID: {$task->id_job}) has been approved by {$processingUser->name} ({$processingUser->nik}) and is now OPEN.";

            } elseif ($action === 'reject') {
                if ($request->isMethod('post')) {
                    $request->validate(['notes' => 'required|string|max:1000']);
                    $approvalDetail->notes = $request->input('notes');
                    $rejectionNotes = $approvalDetail->notes;
                } elseif (!$request->isMethod('post') && $request->has('notes')) {
                     $approvalDetail->notes = $request->query('notes');
                     $rejectionNotes = $approvalDetail->notes;
                }

                $approvalDetail->status = JobApprovalDetail::STATUS_REJECTED;
                $task->status = Task::STATUS_REJECTED;
                $message = "Task '{$task->id_job}' has been successfully REJECTED.";
                if ($rejectionNotes) $message .= " With notes: " . $rejectionNotes;
                $logEvent = 'task_rejected';
                $emailSubject = 'Task Rejected: ' . $task->id_job;
                $emailBodyToRequester = "The task you submitted (JOB ID: {$task->id_job}) has been rejected by {$processingUser->name} ({$processingUser->nik}).";
                if ($rejectionNotes) $emailBodyToRequester .= "\nRejection Notes: " . $rejectionNotes;

                if ($request->isMethod('get') && !$request->has('notes')) {
                    DB::rollBack();
                    return view('page.kanban.reject_task_form', compact('task', 'token', 'approvalDetail'));
                }

            } else {
                DB::rollBack();
                return view('page.kanban.approval_feedback', ['message' => 'Invalid action.']);
            }

            $approvalDetail->processed_at = Carbon::now();
            $approvalDetail->token = null;
            $approvalDetail->save();
            $task->save();

            JobApprovalDetail::where('task_id', $task->id)
                ->where('status', JobApprovalDetail::STATUS_PENDING)
                ->where('id', '!=', $approvalDetail->id)
                ->update([
                    'status' => JobApprovalDetail::STATUS_SUPERSEDED,
                    'token' => null,
                    'notes' => 'Processed by another approver (' . $processingUser->name . ').'
                ]);

            // NOTIFICATION TO REQUESTER
            if ($task->pengaju && $task->pengaju->email) {
                 // Menggunakan queue untuk mengirim email di background
                 Mail::to($task->pengaju->email)->queue(new TaskStatusUpdateMailHtml(
                    $task,
                    $emailSubject,
                    $emailBodyToRequester,
                    $task->pengaju,
                    $rejectionNotes,
                    null, // cancel reason
                    $task->status
                ));
            }

            if (function_exists('activity')) {
                activity()
                    ->causedBy($processingUser)
                    ->performedOn($task)
                    ->withProperties([
                        'id_job' => $task->id_job,
                        'approver_nik' => $processingUser->nik,
                        'approval_detail_id' => $approvalDetail->id,
                        'notes' => $rejectionNotes
                    ])
                    ->log($logEvent);
            }

            DB::commit();
            return view('page.kanban.approval_feedback', ['message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("KanbanController@handleApproval: Error processing token {$token}.", ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return view('page.kanban.approval_feedback', ['message' => 'An internal error occurred while processing your request. Error: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        Log::info('KanbanController@updateStatus: User ID ' . $user->id . ' updating status for task ID ' . $task->id, $request->all());

        $allowedStatuses = Task::getAvailableStatuses();
        $validatorRules = [
            'status' => 'required|string|in:' . implode(',', array_keys($allowedStatuses)),
        ];

        if ($request->input('status') === Task::STATUS_CANCELLED) {
            $validatorRules['cancel_reason'] = 'required|string|max:1000';
            $validatorRules['requester_confirmation_cancel'] = 'required|accepted';
        }
        if ($request->input('status') === Task::STATUS_REJECTED && $request->has('notes')) {
            $validatorRules['notes'] = 'nullable|string|max:1000';
        }

        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            Log::error('KanbanController@updateStatus: Validation failed for task ID ' . $task->id, $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newStatus = $request->input('status');
        $oldStatus = $task->status;

        $canUpdate = false;
        if ($newStatus === Task::STATUS_COMPLETED) {
            $canUpdate = ($user->id === $task->pengaju_id || $user->isSuperAdmin() || $user->isAdminProject() || ($user->department_id && $user->department_id === $task->department_id));
        } elseif ($newStatus === Task::STATUS_CLOSED) {
            $canUpdate = ($user->id === $task->pengaju_id || $user->isSuperAdmin() || $user->isAdminProject()) &&
                         in_array($oldStatus, [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED, Task::STATUS_REJECTED]);
        } elseif ($newStatus === Task::STATUS_CANCELLED) {
            $canUpdate = ($user->id === $task->pengaju_id) &&
                         in_array($oldStatus, [Task::STATUS_PENDING_APPROVAL, Task::STATUS_OPEN]);
        } elseif (($newStatus === Task::STATUS_OPEN || $newStatus === Task::STATUS_PENDING_APPROVAL) &&
                   in_array($oldStatus, [Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED, Task::STATUS_REJECTED])) {
            $canUpdate = ($user->isAdminProject() || $user->isSuperAdmin());
        } elseif ($newStatus === Task::STATUS_REJECTED) {
            $canUpdate = ($user->isAdminProject() || $user->isSuperAdmin());
        } else if ($newStatus === $oldStatus) {
            $canUpdate = true;
        }

        if (!$canUpdate) {
            return response()->json(['message' => 'You do not have permission to change the status of this task to ' . $newStatus . ' or the conditions are not met.'], 403);
        }

        DB::beginTransaction();
        try {
            $task->status = $newStatus;
            $logProperties = ['id_job' => $task->id_job, 'old_status' => $oldStatus, 'new_status' => $newStatus];
            $logEvent = 'task_status_updated';
            $emailSubject = '';
            $emailBodyToRequester = '';
            $rejectionNotesForEmail = null;
            $cancellationReasonForEmail = null;

            if ($newStatus === Task::STATUS_COMPLETED && $oldStatus !== Task::STATUS_COMPLETED) {
                $task->tanggal_job_selesai = Carbon::today()->format('Y-m-d');
                $logEvent = 'task_completed';
                $emailSubject = 'Task Completed: ' . $task->id_job;
                $emailBodyToRequester = "The task you submitted (JOB ID: {$task->id_job}) has been marked as COMPLETED.";
            } elseif ($newStatus === Task::STATUS_CANCELLED && $oldStatus !== Task::STATUS_CANCELLED) {
                $task->cancel_reason = $request->input('cancel_reason');
                $task->requester_confirmation_cancel = $request->boolean('requester_confirmation_cancel');
                $logProperties['cancel_reason'] = $task->cancel_reason;
                $logEvent = 'task_cancelled';
                $cancellationReasonForEmail = $task->cancel_reason;
                $task->pendingApprovalDetails()->update(['status' => JobApprovalDetail::STATUS_SUPERSEDED, 'token' => null, 'notes' => 'Task cancelled by the requester.']);

                $task->load(['department', 'pengaju:id,name']);
                $recipientEmails = $this->getRecipientEmailsForDepartment($task->department);
                $emailSubjectToApprovers = 'Task Cancellation Notice: ' . $task->id_job;
                $emailBodyToApprovers = "The task (JOB ID: {$task->id_job}) for the {$task->department->department_name} department has been cancelled by the requester: {$task->pengaju->name}.\nReason: {$task->cancel_reason}";
                if (!empty($recipientEmails)) {
                    foreach($recipientEmails as $emailAddr) {
                        $recipientUserForMail = User::where('email', $emailAddr)->first();
                        // Menggunakan queue untuk mengirim email di background
                        Mail::to($emailAddr)->queue(new TaskStatusUpdateMailHtml(
                            $task, $emailSubjectToApprovers, $emailBodyToApprovers, $recipientUserForMail, null, $cancellationReasonForEmail, Task::STATUS_CANCELLED
                        ));
                    }
                }
            } elseif (($newStatus === Task::STATUS_OPEN || $newStatus === Task::STATUS_PENDING_APPROVAL) && in_array($oldStatus, [Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED, Task::STATUS_REJECTED])) {
                $task->tanggal_job_selesai = null;
                $task->penutup_id = null;
                $task->closed_at = null;
                $task->cancel_reason = null;
                $task->requester_confirmation_cancel = false;
                $logEvent = ($newStatus === Task::STATUS_PENDING_APPROVAL) ? 'task_reopened_for_approval' : 'task_reopened';
                $emailSubject = 'Task Reopened: ' . $task->id_job;
                $emailBodyToRequester = "Task (JOB ID: {$task->id_job}) has been reopened. The current status is: " . ucfirst(str_replace('_', ' ', $newStatus)) . ".";

                if ($newStatus === Task::STATUS_PENDING_APPROVAL) {
                    $task->approvalDetails()->whereNotIn('status', [JobApprovalDetail::STATUS_APPROVED])
                         ->update(['status' => JobApprovalDetail::STATUS_SUPERSEDED, 'token' => null, 'notes' => 'Task reopened for re-approval.']);

                    $departmentApprovers = DepartmentApprover::where('department_id', $task->department_id)
                        ->where('status', 'active')->with('user:id,nik,email,name')->get();
                    if ($departmentApprovers->isEmpty()) {
                        DB::rollBack();
                        return response()->json(['message' => 'No active approvers found for the target department. The task cannot be reopened for approval.'], 422);
                    }
                    $emailsSentInReopen = 0;
                    foreach ($departmentApprovers as $da) {
                        if ($da->user && filter_var($da->user->email, FILTER_VALIDATE_EMAIL)) {
                            $token = Task::generateUniqueToken();
                            $newApprovalDetail = $task->approvalDetails()->create(['approver_nik' => $da->user_nik, 'status' => JobApprovalDetail::STATUS_PENDING, 'token' => $token]);
                            // Menggunakan queue untuk mengirim email di background
                            Mail::to($da->user->email)->queue(new TaskApprovalRequestMailHtml($task, $newApprovalDetail));
                            $emailsSentInReopen++;
                        }
                    }
                    if ($emailsSentInReopen === 0) {
                        DB::rollBack();
                        return response()->json(['message' => 'Failed to send re-approval request email (no valid approvers found).'], 500);
                    }
                    $emailBodyToRequester = "Task (JOB ID: {$task->id_job}) has been resubmitted for approval.";
                }
            } elseif ($newStatus === Task::STATUS_CLOSED && $oldStatus !== Task::STATUS_CLOSED) {
                $task->penutup_id = $user->id;
                $task->closed_at = Carbon::now();
                if (!$task->tanggal_job_selesai && $oldStatus === Task::STATUS_COMPLETED) {
                    $task->tanggal_job_selesai = Carbon::today()->format('Y-m-d');
                }
                $logEvent = 'task_archived';
                $emailSubject = 'Task Archived: ' . $task->id_job;
                $emailBodyToRequester = "Task (JOB ID: {$task->id_job}) has been archived.";
            } elseif ($newStatus === Task::STATUS_REJECTED && $oldStatus !== Task::STATUS_REJECTED) {
                $logEvent = 'task_rejected_directly';
                if ($request->has('notes')) {
                    $logProperties['rejection_notes'] = $request->input('notes');
                    $rejectionNotesForEmail = $request->input('notes');
                    $task->approvalDetails()->create([
                        'approver_nik' => $user->nik,
                        'status' => JobApprovalDetail::STATUS_REJECTED,
                        'notes' => $rejectionNotesForEmail,
                        'processed_at' => Carbon::now()
                    ]);
                }
                $emailSubject = 'Task Rejected: ' . $task->id_job;
                $emailBodyToRequester = "Task (JOB ID: {$task->id_job}) has been rejected by an administrator.";
                if ($rejectionNotesForEmail) $emailBodyToRequester .= "\nNotes: " . $rejectionNotesForEmail;
            }

            $task->save();
            DB::commit();

            if ($task->pengaju && $task->pengaju->email && $emailSubject &&
                !($newStatus === Task::STATUS_CANCELLED && $user->id === $task->pengaju_id)
            ) {
                // Menggunakan queue untuk mengirim email di background
                Mail::to($task->pengaju->email)->queue(new TaskStatusUpdateMailHtml(
                    $task, $emailSubject, $emailBodyToRequester, $task->pengaju,
                    $rejectionNotesForEmail, $cancellationReasonForEmail, $newStatus
                ));
            }

            if (function_exists('activity')) {
                activity()->causedBy($user)->performedOn($task)->withProperties($logProperties)->log($logEvent);
            }

            $task->load(['pengaju:id,name,nik', 'department:id,department_name', 'penutup:id,name,nik', 'approvalDetails.approver:id,name,nik']);
            Log::info("KanbanController@updateStatus: Task ID {$task->id} status successfully updated to {$newStatus}.");
            return response()->json($task);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("KanbanController@updateStatus: Error updating task status for task ID {$task->id}.", ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to update task status: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Task $task)
    {
        $user = Auth::user();
        Log::info('KanbanController@destroy: User ID ' . $user->id . ' attempting to delete task ID ' . $task->id);

        if (!($user->isAdminProject() || $user->isSuperAdmin())) {
            Log::warning("KanbanController@destroy: Unauthorized attempt to delete task ID {$task->id} by user ID {$user->id}.");
            return response()->json(['message' => 'You do not have permission to permanently delete this task.'], 403);
        }
        $allowedDeleteStatuses = [Task::STATUS_REJECTED, Task::STATUS_CANCELLED, Task::STATUS_CLOSED, Task::STATUS_PENDING_APPROVAL];
        if (!in_array($task->status, $allowedDeleteStatuses)) {
            Log::warning("KanbanController@destroy: Attempt to delete task ID {$task->id} with status '{$task->status}'. Not allowed.");
            return response()->json(['message' => 'Tasks can only be permanently deleted if their status is: Rejected, Cancelled, Closed, or Pending Approval.'], 403);
        }

        DB::beginTransaction();
        try {
            $idJob = $task->id_job;
            $originalTaskId = $task->id;
            $statusAtDeletion = $task->status;

            $task->approvalDetails()->delete();
            $task->delete();

            DB::commit();

            if (function_exists('activity')) {
                activity()
                    ->causedBy($user)
                    ->log("Task '{$idJob}' (ID: {$originalTaskId}, Status: {$statusAtDeletion}) deleted permanently.");
            }

            Log::info("KanbanController@destroy: Task {$idJob} (Original ID: {$originalTaskId}) and its approval details deleted permanently by User ID {$user->id}.");
            return response()->json(['message' => "Task '{$idJob}' has been permanently deleted."]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("KanbanController@destroy: Error deleting task ID {$task->id}.", ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to delete task: ' . $e->getMessage()], 500);
        }
    }

    public function taskListReport(Request $request)
    {
        $user = Auth::user()->load(['department', 'position']);
        Log::info('KanbanController@taskListReport (Task List View): Fetching tasks for user ID ' . $user->id . ' (NIK: ' . $user->nik . ')', $request->all());

        $tasksQuery = Task::with([
            'pengaju:id,name,nik',
            'department:id,department_name',
            'penutup:id,name,nik',
            'approvalDetails' => function ($query) {
                $query->with('approver:id,name,nik')->orderBy('processed_at', 'desc');
            }
        ])
        ->latest('tasks.created_at');

        if ($request->filled('status_filter')) {
            $tasksQuery->where('tasks.status', $request->input('status_filter'));
        }
        if ($request->filled('pengaju_filter')) {
            $tasksQuery->where('tasks.pengaju_id', $request->input('pengaju_filter'));
        }
        if ($request->filled('date_from_filter')) {
            $tasksQuery->whereDate('tasks.created_at', '>=', Carbon::parse($request->input('date_from_filter'))->startOfDay());
        }
        if ($request->filled('date_to_filter')) {
            $tasksQuery->whereDate('tasks.created_at', '<=', Carbon::parse($request->input('date_to_filter'))->endOfDay());
        }
        if ($request->filled('search_filter')) {
            $searchTerm = '%' . $request->input('search_filter') . '%';
            $tasksQuery->where(function ($q) use ($searchTerm) {
                $q->where('tasks.id_job', 'like', $searchTerm)
                  ->orWhere('tasks.area', 'like', $searchTerm)
                  ->orWhere('tasks.list_job', 'like', $searchTerm)
                  ->orWhereHas('pengaju', function($sq) use ($searchTerm){
                      $sq->where('name', 'like', $searchTerm)->orWhere('nik', 'like', $searchTerm);
                  })
                  ->orWhereHas('department', function($sq) use ($searchTerm){
                      $sq->where('department_name', 'like', $searchTerm);
                  });
            });
        }

        if ($user->isSuperAdmin() || $user->isAdminProject()) {
            if ($request->filled('department_filter')) {
                $tasksQuery->where('tasks.department_id', $request->input('department_filter'));
            }
        } else {
            $departmentFilter = $request->input('department_filter');
            if ($departmentFilter) {
                if ($departmentFilter == $user->department_id) {
                    $tasksQuery->where('tasks.department_id', $departmentFilter);
                } else {
                    $tasksQuery->where('tasks.pengaju_id', $user->id)
                               ->where('tasks.department_id', $departmentFilter);
                }
            } else {
                $tasksQuery->where(function ($q) use ($user) {
                    $q->where('tasks.pengaju_id', $user->id);
                    if ($user->department_id) {
                        $q->orWhere('tasks.department_id', $user->department_id);
                    }
                });
            }
        }

        $tasks = $tasksQuery->paginate(10)->withQueryString();

        $departmentsForFilter = Department::orderBy('department_name')->pluck('department_name', 'id');
        $usersForFilter = User::orderBy('name')->where('status', 'active')->get()->mapWithKeys(function ($u) {
            return [$u->id => $u->name . ' (NIK: ' . $u->nik . ')'];
        });
        $taskStatusesForFilter = Task::getAvailableStatuses();

        return view('reports.tasks.list', compact(
            'tasks',
            'user',
            'departmentsForFilter',
            'usersForFilter',
            'taskStatusesForFilter',
            'request'
        ));
    }

    public function exportTasks(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'status'        => $request->query('status_filter'),
            'department_id' => $request->query('department_filter'),
            'pengaju_id'    => $request->query('pengaju_filter'),
            'date_from'     => $request->query('date_from_filter'),
            'date_to'       => $request->query('date_to_filter'),
            'search'        => $request->query('search_filter'),
        ];
        $fileName = 'Task_Report_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        Log::info('KanbanController@exportTasks: Exporting tasks with filters', ['user_id' => $user->id, 'filters' => $filters]);
        return Excel::download(new TasksExport($filters), $fileName);
    }
}