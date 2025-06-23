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
            ->where('status', 'active') // Assuming 'active' is the status for active approvers
            ->with('user:id,nik,email,name') // Eager load user details
            ->get();

        $emails = [];
        foreach ($approvers as $approver) {
            if ($approver->user && filter_var($approver->user->email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $approver->user->email;
            } else {
                // Corrected Log message
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
        Log::info('KanbanController@index (Report View): Fetching tasks for user ID ' . $user->id . ' (NIK: ' . $user->nik . ')', $request->all());

        $tasksQuery = Task::with([
            'pengaju:id,name,nik',
            'department:id,department_name', // Corrected: ensure department is loaded with specific columns
            'penutup:id,name,nik',
            'approvalDetails' => function ($query) {
                $query->with('approver:id,name,nik')->orderBy('processed_at', 'desc');
            }
        ])
        ->latest('tasks.created_at'); // Default sort: newest tasks first

        // Apply Filters from request
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

        // Role-based access control and Department Filter
        if ($user->isSuperAdmin() || $user->isAdminProject()) {
            // Admins can filter by any department
            if ($request->filled('department_filter')) {
                $tasksQuery->where('tasks.department_id', $request->input('department_filter'));
            }
        } else {
            // Regular User access logic
            $departmentFilter = $request->input('department_filter');
            if ($departmentFilter) {
                // If filtering by their own department
                if ($departmentFilter == $user->department_id) {
                    $tasksQuery->where('tasks.department_id', $departmentFilter);
                } else {
                    // If filtering by another department, show only tasks they submitted to that department
                    $tasksQuery->where('tasks.pengaju_id', $user->id)
                               ->where('tasks.department_id', $departmentFilter);
                }
            } else {
                // No specific department filter by regular user:
                // Show tasks they submitted OR tasks for their own department (if any)
                $tasksQuery->where(function ($q) use ($user) {
                    $q->where('tasks.pengaju_id', $user->id);
                    if ($user->department_id) {
                        $q->orWhere('tasks.department_id', $user->department_id);
                    }
                });
            }
        }

        $tasks = $tasksQuery->paginate(10)->withQueryString();

        // Data for filter dropdowns
        $departmentsForFilter = Department::orderBy('department_name')->pluck('department_name', 'id');
        // Assuming 'status' i'active' the column name in your users table for active status
        $usersForFilter = User::orderBy('name')->where('status', 'active')->get()->mapWithKeys(function ($u) {
            return [$u->id => $u->name . ' (NIK: ' . $u->nik . ')'];
        });
        $taskStatusesForFilter = Task::getAvailableStatuses();

        // Data for "Create Task" Modal
        $departmentsForTaskForm = Department::orderBy('department_name')->pluck('department_name', 'id');

        return view('page.kanban.index', compact(
            'tasks',
            'user',
            'departmentsForFilter',
            'usersForFilter',
            'taskStatusesForFilter',
            'departmentsForTaskForm',
            'request' // To repopulate filter form fields
        ));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_job' => 'required|string|max:255|unique:tasks,id_job',
            'department_id' => 'required|exists:departments,id',
            'area' => 'required|string|max:255',
            'list_job' => 'required|string',
            'tanggal_job_mulai' => 'nullable|date_format:Y-m-d|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            Log::error('KanbanController@store: Validation failed.', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['pengaju_id'] = Auth::id();
        $data['status'] = Task::STATUS_PENDING_APPROVAL;
        // Ensure tanggal_job_mulai is set, default to today if not provided or empty
        $data['tanggal_job_mulai'] = !empty($data['tanggal_job_mulai'])
            ? Carbon::parse($data['tanggal_job_mulai'])->format('Y-m-d')
            : Carbon::today()->format('Y-m-d');
        // requester_confirmation_cancel defaults to false or 0 in DB schema or model

        DB::beginTransaction();
        try {
            $task = Task::create($data);
            $task->load(['pengaju:id,name,nik', 'department:id,department_name']); // Load for email and response

            $departmentApprovers = DepartmentApprover::where('department_id', $task->department_id)
                ->where('status', 'active') // This 'status' is for DepartmentApprover model
                ->with('user:id,nik,email,name')
                ->get();

            if ($departmentApprovers->isEmpty()) {
                DB::rollBack();
                Log::error('KanbanController@store: No active approvers found for department ID ' . $task->department_id);
                return response()->json(['message' => 'Tidak ada approver aktif yang dikonfigurasi untuk departemen tujuan. Task tidak dapat dibuat.'], 422);
            }

            $approvalEmailsSentTo = [];
            foreach ($departmentApprovers as $departmentApproverInstance) {
                $approverUser = $departmentApproverInstance->user;
                if (!$approverUser || !filter_var($approverUser->email, FILTER_VALIDATE_EMAIL)) {
                    Log::warning("KanbanController@store: Approver NIK {$departmentApproverInstance->user_nik} for DeptApprover ID {$departmentApproverInstance->id} not found or has invalid email. Skipping.");
                    continue;
                }

                $token = Task::generateUniqueToken(); // Assumes static method in Task model
                $approvalDetail = $task->approvalDetails()->create([
                    'approver_nik' => $approverUser->nik,
                    'status' => JobApprovalDetail::STATUS_PENDING,
                    'token' => $token,
                ]);

                if ($approverUser->email) { // Check again before sending
                    Mail::to($approverUser->email)->send(new TaskApprovalRequestMailHtml($task, $approvalDetail));
                    $approvalEmailsSentTo[] = $approverUser->email;
                    Log::info("KanbanController@store: Sending approval email for Task ID {$task->id} to {$approverUser->email} (Approver NIK: {$approverUser->nik})");
                }
            }

            if (empty($approvalEmailsSentTo)) {
                DB::rollBack();
                Log::error('KanbanController@store: No approval emails could be sent for task ID ' . $task->id . '. All configured approvers might have invalid email addresses.');
                return response()->json(['message' => 'Gagal mengirim email permintaan persetujuan karena tidak ada approver valid yang bisa dihubungi. Task tidak dapat dibuat.'], 500);
            }

            DB::commit();
            $task->load(['approvalDetails.approver:id,name,nik']); // Load for the JSON response
            Log::info('KanbanController@store: Task created successfully, pending approval.', $task->toArray());
            return response()->json($task, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KanbanController@store: Error creating task.', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal membuat task di server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle task approval or rejection via email link.
     */
    public function handleApproval(Request $request, $token)
    {
        $approvalDetail = JobApprovalDetail::where('token', $token)
            ->where('status', JobApprovalDetail::STATUS_PENDING) // Only process pending approvals
            ->first();

        if (!$approvalDetail) {
            return view('page.kanban.approval_feedback', ['message' => 'Token persetujuan tidak valid, sudah diproses, atau kadaluarsa.']);
        }

        $task = $approvalDetail->task->load(['pengaju:id,name,email,nik', 'department:id,department_name']);
        $action = $request->query('action'); // 'approve' or 'reject'
        $loggedInUser = Auth::user(); // User processing via web session
        // User identified by the NIK in approvalDetail if no web session (e.g. direct link access)
        $processingUser = $loggedInUser ?? User::where('nik', $approvalDetail->approver_nik)->first();

        if (!$processingUser) {
             Log::error("KanbanController@handleApproval: Approver user for NIK {$approvalDetail->approver_nik} not found for token {$token}.");
             return view('page.kanban.approval_feedback', ['message' => 'User approver tidak ditemukan.']);
        }
        // Ensure the processing user is indeed the designated approver for this detail if logged in
        if ($loggedInUser && $loggedInUser->nik !== $approvalDetail->approver_nik) {
            Log::warning("KanbanController@handleApproval: User {$loggedInUser->nik} attempted to process approval for {$approvalDetail->approver_nik} with token {$token}.");
            return view('page.kanban.approval_feedback', ['message' => 'Anda tidak diizinkan untuk memproses persetujuan ini.']);
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
                $task->status = Task::STATUS_OPEN; // Task becomes open upon approval
                $message = "Tugas '{$task->id_job}' berhasil Anda SETUJUI dan sekarang berstatus OPEN.";
                $logEvent = 'task_approved';
                $emailSubject = 'Tugas Disetujui: ' . $task->id_job;
                $emailBodyToRequester = "Tugas yang Anda ajukan (JOB ID: {$task->id_job}) telah disetujui oleh {$processingUser->name} ({$processingUser->nik}) dan sekarang berstatus OPEN.";

            } elseif ($action === 'reject') {
                if ($request->isMethod('post')) { // Rejection form submitted
                    $request->validate(['notes' => 'required|string|max:1000']);
                    $approvalDetail->notes = $request->input('notes');
                    $rejectionNotes = $approvalDetail->notes;
                } elseif (!$request->isMethod('post') && $request->has('notes')) { // Notes from query param (less secure, for simple cases)
                     $approvalDetail->notes = $request->query('notes');
                     $rejectionNotes = $approvalDetail->notes;
                }


                $approvalDetail->status = JobApprovalDetail::STATUS_REJECTED;
                $task->status = Task::STATUS_REJECTED; // Task becomes rejected
                $message = "Tugas '{$task->id_job}' berhasil Anda TOLAK.";
                if ($rejectionNotes) $message .= " Dengan catatan: " . $rejectionNotes;
                $logEvent = 'task_rejected';
                $emailSubject = 'Tugas Ditolak: ' . $task->id_job;
                $emailBodyToRequester = "Tugas yang Anda ajukan (JOB ID: {$task->id_job}) telah ditolak oleh {$processingUser->name} ({$processingUser->nik}).";
                if ($rejectionNotes) $emailBodyToRequester .= "\nCatatan Penolakan: " . $rejectionNotes;

                // If rejection form is needed and not yet submitted
                if ($request->isMethod('get') && !$request->has('notes')) { // Show rejection form if GET and no notes
                    DB::rollBack(); // No changes made yet
                    return view('page.kanban.reject_task_form', compact('task', 'token', 'approvalDetail'));
                }

            } else {
                DB::rollBack();
                return view('page.kanban.approval_feedback', ['message' => 'Aksi tidak valid.']);
            }

            $approvalDetail->processed_at = Carbon::now();
            $approvalDetail->token = null; // Invalidate token after use
            $approvalDetail->save();
            $task->save();

            // Mark other pending approval details for this task as 'superseded'
            JobApprovalDetail::where('task_id', $task->id)
                ->where('status', JobApprovalDetail::STATUS_PENDING)
                ->where('id', '!=', $approvalDetail->id)
                ->update([
                    'status' => JobApprovalDetail::STATUS_SUPERSEDED,
                    'token' => null,
                    'notes' => 'Diproses oleh approver lain (' . $processingUser->name . ').'
                ]);

            // Notify task requester
            if ($task->pengaju && $task->pengaju->email) {
                 Mail::to($task->pengaju->email)->send(new TaskStatusUpdateMailHtml(
                    $task,
                    $emailSubject,
                    $emailBodyToRequester,
                    $task->pengaju, // Recipient User object
                    $rejectionNotes,
                    null, // No cancellation reason here
                    $task->status // The new status of the task
                ));
            }

            // Log activity
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
            return view('page.kanban.approval_feedback', ['message' => 'Terjadi kesalahan internal saat memproses permintaan Anda. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the status of a specific task.
     */
    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        Log::info('KanbanController@updateStatus: User ID ' . $user->id . ' updating status for task ID ' . $task->id, $request->all());

        $allowedStatuses = Task::getAvailableStatuses(); // Get all defined statuses
        $validatorRules = [
            'status' => 'required|string|in:' . implode(',', array_keys($allowedStatuses)),
        ];

        if ($request->input('status') === Task::STATUS_CANCELLED) {
            $validatorRules['cancel_reason'] = 'required|string|max:1000';
            $validatorRules['requester_confirmation_cancel'] = 'required|accepted';
        }
        if ($request->input('status') === Task::STATUS_REJECTED && $request->has('notes')) {
            $validatorRules['notes'] = 'nullable|string|max:1000'; // For admin direct rejection with notes
        }

        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            Log::error('KanbanController@updateStatus: Validation failed for task ID ' . $task->id, $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newStatus = $request->input('status');
        $oldStatus = $task->status;

        // Authorization checks
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
                   in_array($oldStatus, [Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED, Task::STATUS_REJECTED])) { // Reopening
            $canUpdate = ($user->isAdminProject() || $user->isSuperAdmin());
        } elseif ($newStatus === Task::STATUS_REJECTED) { // Direct rejection by admin
            $canUpdate = ($user->isAdminProject() || $user->isSuperAdmin());
        } else if ($newStatus === $oldStatus) { // No actual change
            $canUpdate = true; // Allow if no change, or handle as no-op
        }


        if (!$canUpdate) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengubah status task ini ke ' . $newStatus . ' atau kondisi tidak terpenuhi.'], 403);
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
                $emailSubject = 'Tugas Selesai: ' . $task->id_job;
                $emailBodyToRequester = "Tugas (JOB ID: {$task->id_job}) yang Anda ajukan telah ditandai SELESAI.";
            } elseif ($newStatus === Task::STATUS_CANCELLED && $oldStatus !== Task::STATUS_CANCELLED) {
                $task->cancel_reason = $request->input('cancel_reason');
                $task->requester_confirmation_cancel = $request->boolean('requester_confirmation_cancel');
                $logProperties['cancel_reason'] = $task->cancel_reason;
                $logEvent = 'task_cancelled';
                $cancellationReasonForEmail = $task->cancel_reason;
                $task->pendingApprovalDetails()->update(['status' => JobApprovalDetail::STATUS_SUPERSEDED, 'token' => null, 'notes' => 'Task dibatalkan oleh pengaju.']);

                // Notify relevant approvers/department heads about cancellation
                $task->load(['department', 'pengaju:id,name']);
                $recipientEmails = $this->getRecipientEmailsForDepartment($task->department);
                $emailSubjectToApprovers = 'Informasi Pembatalan Tugas: ' . $task->id_job;
                $emailBodyToApprovers = "Tugas (JOB ID: {$task->id_job}) untuk departemen {$task->department->department_name} telah dibatalkan oleh pengaju: {$task->pengaju->name}.\nAlasan: {$task->cancel_reason}";
                if (!empty($recipientEmails)) {
                    foreach($recipientEmails as $emailAddr) {
                        $recipientUserForMail = User::where('email', $emailAddr)->first();
                        Mail::to($emailAddr)->send(new TaskStatusUpdateMailHtml(
                            $task, $emailSubjectToApprovers, $emailBodyToApprovers, $recipientUserForMail, null, $cancellationReasonForEmail, Task::STATUS_CANCELLED
                        ));
                    }
                }
            } elseif (($newStatus === Task::STATUS_OPEN || $newStatus === Task::STATUS_PENDING_APPROVAL) && in_array($oldStatus, [Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED, Task::STATUS_REJECTED])) {
                // Reopening logic
                $task->tanggal_job_selesai = null;
                $task->penutup_id = null;
                $task->closed_at = null;
                $task->cancel_reason = null;
                $task->requester_confirmation_cancel = false;
                $logEvent = ($newStatus === Task::STATUS_PENDING_APPROVAL) ? 'task_reopened_for_approval' : 'task_reopened';
                $emailSubject = 'Tugas Dibuka Kembali: ' . $task->id_job;
                $emailBodyToRequester = "Tugas (JOB ID: {$task->id_job}) telah dibuka kembali. Status saat ini: " . ucfirst(str_replace('_', ' ', $newStatus)) . ".";

                if ($newStatus === Task::STATUS_PENDING_APPROVAL) {
                    // Reset or supersede previous approval details and create new pending ones
                    $task->approvalDetails()->whereNotIn('status', [JobApprovalDetail::STATUS_APPROVED]) // Keep approved ones for history
                         ->update(['status' => JobApprovalDetail::STATUS_SUPERSEDED, 'token' => null, 'notes' => 'Task dibuka kembali untuk persetujuan ulang.']);

                    $departmentApprovers = DepartmentApprover::where('department_id', $task->department_id)
                        ->where('status', 'active')->with('user:id,nik,email,name')->get();
                    if ($departmentApprovers->isEmpty()) {
                        DB::rollBack();
                        return response()->json(['message' => 'Tidak ada approver aktif untuk departemen tujuan. Task tidak dapat dibuka kembali untuk persetujuan.'], 422);
                    }
                    $emailsSentInReopen = 0;
                    foreach ($departmentApprovers as $da) {
                        if ($da->user && filter_var($da->user->email, FILTER_VALIDATE_EMAIL)) {
                            $token = Task::generateUniqueToken();
                            $newApprovalDetail = $task->approvalDetails()->create(['approver_nik' => $da->user_nik, 'status' => JobApprovalDetail::STATUS_PENDING, 'token' => $token]);
                            Mail::to($da->user->email)->send(new TaskApprovalRequestMailHtml($task, $newApprovalDetail));
                            $emailsSentInReopen++;
                        }
                    }
                    if ($emailsSentInReopen === 0) {
                        DB::rollBack();
                        return response()->json(['message' => 'Gagal mengirim email permintaan persetujuan ulang (tidak ada approver valid).'], 500);
                    }
                    $emailBodyToRequester = "Tugas (JOB ID: {$task->id_job}) telah diajukan ulang untuk persetujuan.";
                }
            } elseif ($newStatus === Task::STATUS_CLOSED && $oldStatus !== Task::STATUS_CLOSED) {
                $task->penutup_id = $user->id;
                $task->closed_at = Carbon::now();
                if (!$task->tanggal_job_selesai && $oldStatus === Task::STATUS_COMPLETED) {
                    $task->tanggal_job_selesai = Carbon::today()->format('Y-m-d'); // Set if closed from completed
                }
                $logEvent = 'task_archived';
                $emailSubject = 'Tugas Diarsipkan: ' . $task->id_job;
                $emailBodyToRequester = "Tugas (JOB ID: {$task->id_job}) telah diarsipkan.";
            } elseif ($newStatus === Task::STATUS_REJECTED && $oldStatus !== Task::STATUS_REJECTED) {
                // Admin directly rejecting (not via approval link)
                $logEvent = 'task_rejected_directly';
                if ($request->has('notes')) {
                    $logProperties['rejection_notes'] = $request->input('notes');
                    $rejectionNotesForEmail = $request->input('notes');
                    // Optionally, create a JobApprovalDetail for this direct rejection
                    $task->approvalDetails()->create([
                        'approver_nik' => $user->nik, // Admin who rejected
                        'status' => JobApprovalDetail::STATUS_REJECTED,
                        'notes' => $rejectionNotesForEmail,
                        'processed_at' => Carbon::now()
                    ]);
                }
                $emailSubject = 'Tugas Ditolak: ' . $task->id_job;
                $emailBodyToRequester = "Tugas (JOB ID: {$task->id_job}) telah ditolak oleh administrator.";
                if ($rejectionNotesForEmail) $emailBodyToRequester .= "\nCatatan: " . $rejectionNotesForEmail;
            }

            $task->save();
            DB::commit();

            // Send email to requester for status changes (unless they initiated it, e.g. cancellation by self)
            if ($task->pengaju && $task->pengaju->email && $emailSubject &&
                !($newStatus === Task::STATUS_CANCELLED && $user->id === $task->pengaju_id)
            ) {
                Mail::to($task->pengaju->email)->send(new TaskStatusUpdateMailHtml(
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
            return response()->json(['message' => 'Gagal memperbarui status task: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified task from storage (permanent delete).
     */
    public function destroy(Request $request, Task $task)
    {
        $user = Auth::user();
        Log::info('KanbanController@destroy: User ID ' . $user->id . ' attempting to delete task ID ' . $task->id);

        if (!($user->isAdminProject() || $user->isSuperAdmin())) {
            Log::warning("KanbanController@destroy: Unauthorized attempt to delete task ID {$task->id} by user ID {$user->id}.");
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus permanen task ini.'], 403);
        }
        $allowedDeleteStatuses = [Task::STATUS_REJECTED, Task::STATUS_CANCELLED, Task::STATUS_CLOSED, Task::STATUS_PENDING_APPROVAL];
        if (!in_array($task->status, $allowedDeleteStatuses)) {
            Log::warning("KanbanController@destroy: Attempt to delete task ID {$task->id} with status '{$task->status}'. Not allowed.");
            return response()->json(['message' => 'Task hanya bisa dihapus permanen jika berstatus: Ditolak, Dibatalkan, Diarsipkan, atau Pending Approval.'], 403);
        }

        DB::beginTransaction();
        try {
            $idJob = $task->id_job;
            $originalTaskId = $task->id;
            $statusAtDeletion = $task->status;

            $task->approvalDetails()->delete(); // Delete related approval details
            $task->delete(); // Delete the task itself

            DB::commit();

            if (function_exists('activity')) {
                // Note: $task object is now "deleted" from DB perspective for `performedOn`
                // For some activity loggers, you might log before delete or pass attributes.
                activity()
                    ->causedBy($user)
                    ->log("Task '{$idJob}' (ID: {$originalTaskId}, Status: {$statusAtDeletion}) deleted permanently.");
            }

            Log::info("KanbanController@destroy: Task {$idJob} (Original ID: {$originalTaskId}) and its approval details deleted permanently by User ID {$user->id}.");
            return response()->json(['message' => "Task '{$idJob}' berhasil dihapus permanen."]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("KanbanController@destroy: Error deleting task ID {$task->id}.", ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal menghapus task: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the Department Approver Management page.
     * This method provides data for managing department approvers.
     */
 public function taskListReport(Request $request)
    {
        // Menggunakan logika yang sangat mirip dengan method index() untuk mengambil data Task
        $user = Auth::user()->load(['department', 'position']); // Ganti $loggedInUser menjadi $user agar konsisten
        Log::info('KanbanController@taskListReport (Task List View): Fetching tasks for user ID ' . $user->id . ' (NIK: ' . $user->nik . ')', $request->all());

        $tasksQuery = Task::with([
            'pengaju:id,name,nik',
            'department:id,department_name',
            'penutup:id,name,nik',
            'approvalDetails' => function ($query) {
                $query->with('approver:id,name,nik')->orderBy('processed_at', 'desc');
            }
        ])
        ->latest('tasks.created_at'); // Default sort: newest tasks first

        // Apply Filters from request (sama seperti di method index)
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

        // Role-based access control and Department Filter (sama seperti di method index)
        if ($user->isSuperAdmin() || $user->isAdminProject()) {
            // Admins can filter by any department
            if ($request->filled('department_filter')) {
                $tasksQuery->where('tasks.department_id', $request->input('department_filter'));
            }
        } else {
            // Regular User access logic
            $departmentFilter = $request->input('department_filter');
            if ($departmentFilter) {
                // If filtering by their own department
                if ($departmentFilter == $user->department_id) {
                    $tasksQuery->where('tasks.department_id', $departmentFilter);
                } else {
                    // If filtering by another department, show only tasks they submitted to that department
                    $tasksQuery->where('tasks.pengaju_id', $user->id)
                               ->where('tasks.department_id', $departmentFilter);
                }
            } else {
                // No specific department filter by regular user:
                // Show tasks they submitted OR tasks for their own department (if any)
                $tasksQuery->where(function ($q) use ($user) {
                    $q->where('tasks.pengaju_id', $user->id);
                    if ($user->department_id) {
                        $q->orWhere('tasks.department_id', $user->department_id);
                    }
                });
            }
        }

        $tasks = $tasksQuery->paginate(10)->withQueryString(); // Variabel $tasks yang dibutuhkan view

        // Data for filter dropdowns (sama seperti di method index)
        $departmentsForFilter = Department::orderBy('department_name')->pluck('department_name', 'id');
        $usersForFilter = User::orderBy('name')->where('status', 'active')->get()->mapWithKeys(function ($u) {
            return [$u->id => $u->name . ' (NIK: ' . $u->nik . ')'];
        });
        $taskStatusesForFilter = Task::getAvailableStatuses();

        // Data yang sebelumnya ada di taskListReport untuk approver management tidak lagi relevan
        // jika halaman ini HANYA untuk menampilkan list job.
        // Hapus: $approvers_data, $departments_for_form, $users_for_form, $statuses_for_form
        // dari compact() jika tidak digunakan.

        return view('reports.tasks.list', compact(
            'tasks',
            'user', // Menggunakan $user, bukan $loggedInUser
            'departmentsForFilter',
            'usersForFilter',
            'taskStatusesForFilter',
            'request' // Untuk repopulate filter form fields
        ));
    }

    /**
     * Handle export of tasks to Excel.
     */
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
        $fileName = 'Laporan_Tugas_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        Log::info('KanbanController@exportTasks: Exporting tasks with filters', ['user_id' => $user->id, 'filters' => $filters]);
        return Excel::download(new TasksExport($filters), $fileName);
    }
}