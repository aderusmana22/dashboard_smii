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
                Log::warning("DepartmentApprover ID {$approver->id} for NIK {$approver->user_nik} has no valid user or email.");
            }
        }

        if (empty($emails)) {
            Log::warning("No active approvers with valid emails found for department '{$department->department_name}'.");
        }
        return array_unique($emails);
    }

    public function index()
    {
        $user = Auth::user()->load(['department', 'position']);
        Log::info('KanbanController@index: Fetching tasks for user ID ' . $user->id . ' (NIK: ' . $user->nik . ')');

        $tasksQuery = Task::with([
            'pengaju:id,name,nik',
            'department:id,department_name',
            'penutup:id,name,nik',
            'approvalDetails' => function ($query) {
                $query->with('approver:id,name,nik')->orderBy('processed_at', 'desc');
            }
        ])
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
                $query->where('pengaju_id', $user->id)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('department_id', $user->department_id)
                          ->whereIn('status', [Task::STATUS_OPEN, Task::STATUS_COMPLETED]);
                    })
                    ->orWhereHas('approvalDetails', function ($q_approval) use ($user) {
                        $q_approval->where('approver_nik', $user->nik)
                                   ->where('status', JobApprovalDetail::STATUS_PENDING);
                    });
            });
        }

        $tasks = $tasksQuery->get();
        $departments = Department::orderBy('department_name')->get();

        $groupedTasks = $tasks->groupBy('status');
        $pendingApprovalTasks = $groupedTasks->get(Task::STATUS_PENDING_APPROVAL, collect());
        $openTasks = $groupedTasks->get(Task::STATUS_OPEN, collect());
        $completedTasks = $groupedTasks->get(Task::STATUS_COMPLETED, collect());
        $closedTasks = $groupedTasks->get(Task::STATUS_CLOSED, collect());
        $rejectedTasks = $groupedTasks->get(Task::STATUS_REJECTED, collect());
        $cancelledTasks = $groupedTasks->get(Task::STATUS_CANCELLED, collect());

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
        $data['tanggal_job_mulai'] = empty($data['tanggal_job_mulai'])
            ? Carbon::today()->format('Y-m-d')
            : Carbon::parse($data['tanggal_job_mulai'])->format('Y-m-d');

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
                return response()->json(['message' => 'Tidak ada approver aktif yang dikonfigurasi untuk departemen tujuan. Task tidak dapat dibuat.'], 422);
            }

            $approvalEmailsSentTo = [];
            foreach ($departmentApprovers as $departmentApproverInstance) {
                $approverUser = $departmentApproverInstance->user;

                if (!$approverUser || !filter_var($approverUser->email, FILTER_VALIDATE_EMAIL)) {
                    Log::warning("KanbanController@store: Approver NIK {$departmentApproverInstance->user_nik} (via DeptApprover ID {$departmentApproverInstance->id}) not found or has invalid email. Skipping.");
                    continue;
                }

                $token = Task::generateUniqueToken();
                $approvalDetail = $task->approvalDetails()->create([
                    'approver_nik' => $approverUser->nik,
                    'status' => JobApprovalDetail::STATUS_PENDING,
                    'token' => $token,
                ]);

                $recipientEmail = $approverUser->email;

                if ($recipientEmail) {
                    Mail::to($recipientEmail)->send(new TaskApprovalRequestMailHtml($task, $approvalDetail));
                    $approvalEmailsSentTo[] = $recipientEmail;
                    Log::info("KanbanController@store: Sending approval email for Task ID {$task->id} to {$recipientEmail} (Approver NIK: {$approverUser->nik})");
                }
            }


            if (empty($approvalEmailsSentTo)) {
                DB::rollBack();
                Log::error('KanbanController@store: No approval emails could be sent for task ID ' . $task->id . '. This might happen if all found approvers had invalid emails or were skipped.');
                return response()->json(['message' => 'Gagal mengirim email permintaan persetujuan karena tidak ada approver valid yang bisa dihubungi. Task tidak dapat dibuat.'], 500);
            }

            DB::commit();
            $task->load(['pengaju:id,name,nik', 'department:id,department_name', 'approvalDetails.approver:id,name,nik']);
            Log::info('KanbanController@store: Task created, pending approval.', $task->toArray());
            return response()->json($task, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KanbanController@store: Error creating task.', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal membuat task di server: ' . $e->getMessage()], 500);
        }
    }

    public function handleApproval(Request $request, $token)
    {
        $approvalDetail = JobApprovalDetail::where('token', $token)
            ->where('status', JobApprovalDetail::STATUS_PENDING)
            ->first();

        if (!$approvalDetail) {
            return view('page.kanban.approval_feedback', ['message' => 'Token persetujuan tidak valid, sudah diproses, atau kadaluarsa.']);
        }

        $task = $approvalDetail->task->load(['pengaju:id,name,email,nik', 'department:id,department_name']);
        $action = $request->query('action');
        $loggedInUser = Auth::user();
        $processingUser = $loggedInUser ?? User::where('nik', $approvalDetail->approver_nik)->first();

        if (!$processingUser) {
             Log::error("KanbanController@handleApproval: Approver user for NIK {$approvalDetail->approver_nik} not found.");
             return view('page.kanban.approval_feedback', ['message' => 'User approver tidak ditemukan.']);
        }

        DB::beginTransaction();
        try {
            $message = '';
            $logEvent = '';
            $emailSubject = '';
            $emailBody = '';
            $emailRecipient = $task->pengaju;
            $rejectionNotes = null;

            if ($action === 'approve') {
                $approvalDetail->status = JobApprovalDetail::STATUS_APPROVED;
                $task->status = Task::STATUS_OPEN;
                $message = 'Tugas berhasil disetujui dan sekarang berstatus OPEN.';
                $logEvent = 'approved';
                $emailSubject = 'Tugas Disetujui: ' . $task->id_job;
                $emailBody = "Tugas yang Anda ajukan (JOB ID: {$task->id_job}) telah disetujui oleh {$processingUser->name} dari departemen {$task->department->department_name} dan sekarang berstatus OPEN.";

            } elseif ($action === 'reject') {
                if ($request->isMethod('post')) {
                    $request->validate(['notes' => 'required|string|max:1000']);
                    $approvalDetail->notes = $request->input('notes');
                    $rejectionNotes = $approvalDetail->notes;
                    $approvalDetail->status = JobApprovalDetail::STATUS_REJECTED;
                    $task->status = Task::STATUS_REJECTED;
                    $message = 'Tugas berhasil ditolak.';
                    $logEvent = 'rejected';
                    $emailSubject = 'Tugas Ditolak: ' . $task->id_job;
                    $emailBody = "Tugas yang Anda ajukan (JOB ID: {$task->id_job}) telah ditolak oleh {$processingUser->name} dari departemen {$task->department->department_name}.";
                } else {
                    return view('page.kanban.reject_task_form', compact('task', 'token', 'approvalDetail'));
                }
            } else {
                DB::rollBack();
                return view('page.kanban.approval_feedback', ['message' => 'Aksi tidak valid.']);
            }

            $approvalDetail->processed_at = Carbon::now();
            $approvalDetail->token = null;
            $approvalDetail->save();
            $task->save();

            JobApprovalDetail::where('task_id', $task->id)
                ->where('status', JobApprovalDetail::STATUS_PENDING)
                ->where('id', '!=', $approvalDetail->id)
                ->update(['status' => 'superseded', 'token' => null, 'notes' => 'Processed by another approver.']);

            if ($emailRecipient && $emailRecipient->email) {
                 Mail::to($emailRecipient->email)->send(new TaskStatusUpdateMailHtml(
                    $task,
                    $emailSubject,
                    $emailBody,
                    $emailRecipient,
                    $rejectionNotes,
                    null,
                    $approvalDetail->status
                ));
            }

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

            DB::commit();
            return view('page.kanban.approval_feedback', ['message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("KanbanController@handleApproval: Error processing token {$token}.", ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return view('page.kanban.approval_feedback', ['message' => 'Terjadi kesalahan saat memproses permintaan Anda. Error: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        Log::info('KanbanController@updateStatus: User ID ' . $user->id . ' updating status for task ID ' . $task->id, $request->all());

        $allowedStatuses = [
            Task::STATUS_OPEN, Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED
        ];
        $validatorRules = [
            'status' => 'required|string|in:' . implode(',', $allowedStatuses),
        ];

        if ($request->input('status') === Task::STATUS_CANCELLED) {
            $validatorRules['cancel_reason'] = 'required|string|max:1000';
            $validatorRules['requester_confirmation_cancel'] = 'required|accepted';
        }
        $validator = Validator::make($request->all(), $validatorRules);

        if ($validator->fails()) {
            Log::error('KanbanController@updateStatus: Validation failed for task ID ' . $task->id, $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newStatus = $request->input('status');
        $oldStatus = $task->status;

        if ($newStatus === Task::STATUS_COMPLETED) {
            if (!($user->id === $task->pengaju_id || $user->isSuperAdmin() || $user->isAdminProject() || $user->department_id === $task->department_id)) {
                return response()->json(['message' => 'Anda tidak memiliki izin untuk menyelesaikan task ini.'], 403);
            }
        } elseif ($newStatus === Task::STATUS_CLOSED) {
            if (!($user->id === $task->pengaju_id || $user->isSuperAdmin() || $user->isAdminProject())) {
                return response()->json(['message' => 'Hanya pengaju, Super Admin, atau Admin Project yang dapat mengarsipkan task ini.'], 403);
            }
            if (!in_array($oldStatus, [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED, Task::STATUS_REJECTED])) {
                return response()->json(['message' => 'Task hanya bisa diarsipkan jika sudah COMPLETED, CANCELLED, atau REJECTED.'], 403);
            }
        } elseif ($newStatus === Task::STATUS_CANCELLED) {
            if (!($user->id === $task->pengaju_id)) {
                return response()->json(['message' => 'Hanya pengaju yang dapat membatalkan task ini.'], 403);
            }
            if (!in_array($oldStatus, [Task::STATUS_PENDING_APPROVAL, Task::STATUS_OPEN])) {
                return response()->json(['message' => 'Task hanya bisa dibatalkan jika berstatus PENDING APPROVAL atau OPEN.'], 403);
            }
            $task->cancel_reason = $request->input('cancel_reason');
            $task->requester_confirmation_cancel = $request->boolean('requester_confirmation_cancel');
        } elseif ($newStatus === Task::STATUS_OPEN && in_array($oldStatus, [Task::STATUS_COMPLETED, Task::STATUS_CLOSED, Task::STATUS_CANCELLED, Task::STATUS_REJECTED])) {
            if (!($user->isAdminProject() || $user->isSuperAdmin())) {
                return response()->json(['message' => 'Hanya Admin Project atau Super Admin yang bisa membuka kembali task.'], 403);
            }
        }

        DB::beginTransaction();
        try {
            $task->status = $newStatus;
            $logProperties = ['id_job' => $task->id_job, 'old_status' => $oldStatus, 'new_status' => $newStatus];
            $logEvent = 'updated_status';

            if ($newStatus === Task::STATUS_COMPLETED && $oldStatus !== Task::STATUS_COMPLETED) {
                $task->tanggal_job_selesai = Carbon::today()->format('Y-m-d');
                $logEvent = 'completed';
                if ($task->pengaju_id !== $user->id && $task->pengaju && $task->pengaju->email) {
                    Mail::to($task->pengaju->email)->send(new TaskStatusUpdateMailHtml(
                        $task, 'Tugas Selesai: ' . $task->id_job,
                        "Tugas (JOB ID: {$task->id_job}) yang Anda ajukan telah ditandai SELESAI.",
                        $task->pengaju, null, null, Task::STATUS_COMPLETED
                    ));
                }
            } elseif ($newStatus === Task::STATUS_OPEN) {
                $task->tanggal_job_selesai = null;
                $task->penutup_id = null;
                $task->closed_at = null;
                $task->cancel_reason = null;
                $task->requester_confirmation_cancel = false;
                $logEvent = 'reopened';

                if (in_array($oldStatus, [Task::STATUS_REJECTED, Task::STATUS_CANCELLED])) {
                    $task->status = Task::STATUS_PENDING_APPROVAL;
                    $task->approvalDetails()->whereIn('status', [JobApprovalDetail::STATUS_REJECTED, 'superseded', JobApprovalDetail::STATUS_PENDING])->delete();

                    $departmentApprovers = DepartmentApprover::where('department_id', $task->department_id)
                        ->where('status', 'active')
                        ->with('user:id,nik,email,name')
                        ->get();

                    if ($departmentApprovers->isEmpty()) {
                        DB::rollBack();
                        return response()->json(['message' => 'Tidak ada approver aktif untuk re-approval.'], 422);
                    }
                    $emailsSentInReopen = 0;
                    foreach ($departmentApprovers as $departmentApproverInstance) {
                        $approverUser = $departmentApproverInstance->user;
                        if ($approverUser && filter_var($approverUser->email, FILTER_VALIDATE_EMAIL)) {
                            $token = Task::generateUniqueToken();
                            $approvalDetail = $task->approvalDetails()->create([
                                'approver_nik' => $approverUser->nik,
                                'status' => JobApprovalDetail::STATUS_PENDING,
                                'token' => $token,
                            ]);
                            $recipientEmail = $approverUser->email;
                            Mail::to($recipientEmail)->send(new TaskApprovalRequestMailHtml($task, $approvalDetail));
                            Log::info("KanbanController@updateStatus (reopened_for_approval): Sending approval email for Task ID {$task->id} to {$recipientEmail} (Approver NIK: {$approverUser->nik})");
                            $emailsSentInReopen++;
                        } else {
                            Log::warning("KanbanController@updateStatus (reopened_for_approval): Approver NIK {$departmentApproverInstance->user_nik} (via DeptApprover ID {$departmentApproverInstance->id}) not found or has invalid email. Skipping.");
                        }
                    }
                    if ($emailsSentInReopen === 0) {
                        DB::rollBack();
                        Log::error('KanbanController@updateStatus (reopened_for_approval): No approval emails could be sent for task ID ' . $task->id);
                        return response()->json(['message' => 'Gagal mengirim email permintaan persetujuan ulang karena tidak ada approver valid yang bisa dihubungi.'], 500);
                    }
                    $logEvent = 'reopened_for_approval';
                }
            } elseif ($newStatus === Task::STATUS_CLOSED && $oldStatus !== Task::STATUS_CLOSED) {
                $task->penutup_id = $user->id;
                $task->closed_at = Carbon::now();
                if (!$task->tanggal_job_selesai && $oldStatus === Task::STATUS_COMPLETED) {
                    $task->tanggal_job_selesai = Carbon::today()->format('Y-m-d');
                }
                $logEvent = 'archived';
            } elseif ($newStatus === Task::STATUS_CANCELLED && $oldStatus !== Task::STATUS_CANCELLED) {
                $logProperties['cancel_reason'] = $task->cancel_reason;
                $logEvent = 'cancelled';
                $task->pendingApprovalDetails()->update(['status' => 'superseded', 'token' => null, 'notes' => 'Task cancelled by requester.']);
                $task->load(['department', 'pengaju:id,name']);
                $recipientEmails = $this->getRecipientEmailsForDepartment($task->department);
                if (!empty($recipientEmails)) {
                    foreach($recipientEmails as $email) {
                        $recipientUser = User::where('email', $email)->first();
                        Mail::to($email)->send(new TaskStatusUpdateMailHtml(
                            $task, 'Tugas Dibatalkan: ' . $task->id_job,
                            "Tugas (JOB ID: {$task->id_job}) untuk departemen {$task->department->department_name} telah dibatalkan oleh pengaju: {$task->pengaju->name}.\nAlasan: {$task->cancel_reason}",
                            $recipientUser, null, $task->cancel_reason, Task::STATUS_CANCELLED
                        ));
                    }
                    Log::info("KanbanController@updateStatus: Cancellation email for Task ID {$task->id} sent to: " . implode(', ', $recipientEmails));
                }
            }

            $task->save();
            DB::commit();

            activity()
                ->causedBy($user)
                ->performedOn($task)
                ->withProperties($logProperties)
                ->log($logEvent);

            $task->load(['pengaju:id,name,nik', 'department:id,department_name', 'penutup:id,name,nik', 'approvalDetails.approver:id,name,nik']);
            Log::info('KanbanController@updateStatus: Task ID ' . $task->id . ' status updated to ' . $newStatus);
            return response()->json($task);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KanbanController@updateStatus: Error updating task status for task ID ' . $task->id, ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal memperbarui status task: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Task $task)
    {
        $user = Auth::user();
        Log::info('KanbanController@destroy: User ID ' . $user->id . ' attempting to delete task ID ' . $task->id);

        if (!($user->isAdminProject() || $user->isSuperAdmin())) {
            Log::warning('KanbanController@destroy: Unauthorized attempt to delete task ID ' . $task->id . ' by user ID ' . $user->id);
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus permanen task ini.'], 403);
        }
        if (!in_array($task->status, [Task::STATUS_REJECTED, Task::STATUS_CANCELLED, Task::STATUS_CLOSED])) {
            Log::warning('KanbanController@destroy: Attempt to delete task ID ' . $task->id . ' with status ' . $task->status);
            return response()->json(['message' => 'Task hanya bisa dihapus permanen jika berstatus REJECTED, CANCELLED, atau CLOSED (ARCHIVED).'], 403);
        }

        DB::beginTransaction();
        try {
            $idJob = $task->id_job;
            $taskId = $task->id;
            $task->approvalDetails()->delete();
            $task->delete();
            DB::commit();


            Log::info("KanbanController@destroy: Task {$idJob} (Original ID: {$taskId}) deleted permanently.");
            return response()->json(['message' => 'Task berhasil dihapus permanen.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KanbanController@destroy: Error deleting task ID ' . $task->id, ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menghapus task: ' . $e->getMessage()], 500);
        }
    }
}