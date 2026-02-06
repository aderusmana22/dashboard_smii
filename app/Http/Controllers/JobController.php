<?php

namespace App\Http\Controllers;

use App\Events\JobUpdated;
use App\Models\JobMarsho;
use App\Models\MarshoDepartment;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendJobCompletedEmail;
use App\Mail\JobCancelledNotification;

class JobController extends Controller
{
    /**
     * Menampilkan halaman utama Job Board (Kanban).
     */
    // app/Http/Controllers/JobController.php

    // app/Http/Controllers/JobController.php

    public function index()
    {
        $user = Auth::user();

        // LOGIKA: Sembunyikan job yang sudah CLOSED lebih dari 3 hari kerja
        $hideDate = Carbon::now()->subWeekdays(3)->startOfDay();

        $jobs = JobMarsho::with([
            'pengaju',
            'area',
            'latestRoute.toDepartment',
            'latestRoute.creator',
            'notes'
        ])
            ->where(function ($query) use ($hideDate) {
                $query->where('status', '!=', 'closed') // Tampilkan semua yang belum closed
                    ->orWhere('closed_at', '>=', $hideDate); // Atau yang closed tapi belum lewat 3 hari kerja
            })
            ->latest()
            ->get();

        // Filtering status untuk kolom Kanban
        $toBeScheduledJobs = $jobs->where('status', 'to_be_scheduled');
        $scheduledJobs = $jobs->where('status', 'scheduled');
        $preparationJobs = $jobs->where('status', 'preparation');
        $onGoingJobs = $jobs->where('status', 'on_going');
        $completedJobs = $jobs->where('status', 'completed');
        $closedJobs = $jobs->where('status', 'closed');

        $departments = MarshoDepartment::pluck('department_name', 'id');
        $areas = Area::pluck('name', 'id');

        return view('jobs.index', compact(
            'toBeScheduledJobs',
            'scheduledJobs',
            'preparationJobs',
            'onGoingJobs',
            'completedJobs',
            'closedJobs',
            'user',
            'departments',
            'areas'
        ));
    }

    public function close(Request $request, JobMarsho $job)
    {
        DB::transaction(function () use ($job) {
            $job->update([
                'status' => 'closed',
                'penutup_id' => Auth::id(),
                'closed_at' => Carbon::now()
            ]);

            $job->notes()->create([
                'job_id' => $job->id,
                'note' => "JOB CLOSED manual by " . Auth::user()->name,
                'created_by' => Auth::id()
            ]);
        });

        // Kirim notifikasi ke semua pihak (Requester + Semua Dept yang pernah menangani)
        $this->notifyAllParties($job);

        return $this->prepareJobResponse($job, 'Job closed and archived.');
    }

    /**
     * Logika notifikasi massal yang sama dengan Command AutoClose
     */
    private function notifyAllParties($job)
    {
        $involvedUsers = collect();

        // 1. Tambahkan Pengaju
        if ($job->pengaju)
            $involvedUsers->push($job->pengaju);

        // 2. Tambahkan semua user dari semua departemen yang pernah ada di Route
        $deptIds = $job->routes()->pluck('to_department_id')->filter()->unique();

        if ($deptIds->isNotEmpty()) {
            $usersInDepts = User::whereHas('marshoProfile', function ($query) use ($deptIds) {
                $query->whereIn('marsho_department_id', $deptIds);
            })->get();
            $involvedUsers = $involvedUsers->merge($usersInDepts);
        }

        // 3. Kirim Email ke setiap user unik
        foreach ($involvedUsers->unique('id') as $recipient) {
            Mail::to($recipient->email)->send(new \App\Mail\JobAutoClosedNotification($job, $recipient));
        }
    }

    /**
     * Helper: Mencari semua user terlibat (Requester + Semua Dept di Route)
     */
    private function notifyAllInvolvedUsers($job)
    {
        $involvedUsers = collect();

        // 1. Tambahkan Pengaju
        if ($job->pengaju)
            $involvedUsers->push($job->pengaju);

        // 2. Cari semua Departemen yang pernah disinggah (Target Dept & Forwarded Dept)
        $deptIds = $job->routes()->pluck('to_department_id')
            ->merge($job->routes()->pluck('from_department_id'))
            ->filter()->unique();

        if ($deptIds->isNotEmpty()) {
            // Ambil semua user yang berada di departemen-departemen tersebut
            $usersInDepts = User::whereHas('marshoProfile', function ($q) use ($deptIds) {
                $q->whereIn('marsho_department_id', $deptIds);
            })->get();

            $involvedUsers = $involvedUsers->merge($usersInDepts);
        }

        // 3. Kirim Email (Unikkan berdasarkan email agar tidak dobel)
        foreach ($involvedUsers->unique('id') as $recipient) {
            // Gunakan Mailable yang sudah kita buat sebelumnya
            Mail::to($recipient->email)->send(new \App\Mail\JobAutoClosedNotification($job, $recipient));
        }
    }

    /**
     * Membuat Job Baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'list_job' => 'required|string',
            'to_department_id' => 'required|exists:marsho_departments,id',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:500',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120'
        ]);

        $jobIdString = JobMarsho::generateJobId();

        // Otomatis status Scheduled jika start date hari ini/lewat
        $status = Carbon::parse($request->start_date)->isPast() || Carbon::parse($request->start_date)->isToday()
            ? 'scheduled'
            : 'to_be_scheduled';

        DB::transaction(function () use ($request, $jobIdString, $status) {
            $job = JobMarsho::create([
                'id_job' => $jobIdString,
                'pengaju_id' => Auth::id(),
                'area_id' => $request->area_id,
                'list_job' => $request->list_job,
                'tanggal_job_mulai' => $request->start_date,
                'deadline' => $request->deadline,
                'status' => $status,
                'last_stage_update' => Carbon::now(),
            ]);

            // Buat Route Awal (Initial Dept)
            $deptName = MarshoDepartment::find($request->to_department_id)->department_name;
            $route = $job->routes()->create([
                'to_department_id' => $request->to_department_id,
                'note' => "JOB CREATED. Assigned to: {$deptName}\nNote: " . ($request->note ?: '-'),
                'created_by' => Auth::id()
            ]);

            $this->handleAttachments($request, $job, $route->id);
            return $job;
        });

        $job = JobMarsho::where('id_job', $jobIdString)->first();
        return $this->prepareJobResponse($job, 'Job created successfully!');
    }

    /**
     * METHOD UTAMA: Ganti Status (Bisa sekaligus Pindah Departemen).
     */
    public function changeStatus(Request $request, JobMarsho $job)
    {
        $request->validate([
            'status' => 'required|in:scheduled,preparation,on_going',
            'to_department_id' => 'nullable|exists:marsho_departments,id', // Opsional: Target Dept
            'note' => 'required|string|max:1000',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        DB::transaction(function () use ($request, $job) {
            $oldStatus = $job->status;
            $newStatus = $request->status;

            // 1. Update Status Job & Timer SLA
            $job->update([
                'status' => $newStatus,
                'last_stage_update' => Carbon::now()
            ]);

            $latestRoute = $job->latestRoute;
            $currentDeptId = $latestRoute ? $latestRoute->to_department_id : null;
            $targetDeptId = $request->to_department_id; // ID Dept baru (jika dipilih user)

            // Format Header Text
            $statusChangeText = "STATUS CHANGE: " . ucfirst(str_replace('_', ' ', $oldStatus)) . " ➝ " . ucfirst(str_replace('_', ' ', $newStatus));

            // 2. CEK LOGIKA: Apakah User memilih pindah departemen?
            if ($targetDeptId && $targetDeptId != $currentDeptId) {

                // --- SKENARIO A: PINDAH DEPARTEMEN SEKALIGUS GANTI STATUS ---
                // Kita buat Route baru (akan muncul ikon Kuning/Panah di history)

                $fromDeptName = $latestRoute->toDepartment->department_name ?? 'Initial';
                $toDeptName = MarshoDepartment::find($targetDeptId)->department_name;

                // Gabungkan info Status Change + Dept Move
                $finalNote = "{$statusChangeText}\nDEPARTMENT MOVE: {$fromDeptName} ➝ {$toDeptName}\n\nNote: " . $request->note;

                $newRoute = $job->routes()->create([
                    'job_id' => $job->id,
                    'from_department_id' => $currentDeptId,
                    'to_department_id' => $targetDeptId,
                    'note' => $finalNote,
                    'created_by' => Auth::id()
                ]);

                // Attachment ditempel ke Route baru ini
                $this->handleAttachments($request, $job, $newRoute->id);

            } else {

                // --- SKENARIO B: TETAP DI DEPARTEMEN SAMA (HANYA STATUS) ---
                // Kita buat Note biasa (akan muncul ikon Biru/Pensil di history)

                $finalNote = "{$statusChangeText}\n\nNote: " . $request->note;

                $job->notes()->create([
                    'job_id' => $job->id,
                    'job_route_id' => $latestRoute ? $latestRoute->id : null,
                    'note' => $finalNote,
                    'created_by' => Auth::id()
                ]);

                // Attachment ditempel ke Route saat ini (nanti di showDetails dicocokkan via timestamp)
                $this->handleAttachments($request, $job, $latestRoute ? $latestRoute->id : null);
            }
        });

        // Refresh data job
        $job->refresh();

        // Pesan respons kustom
        $msg = 'Job moved to ' . ucfirst(str_replace('_', ' ', $request->status));
        if ($request->to_department_id && $request->to_department_id != $job->latestRoute->from_department_id) {
            $msg .= ' and forwarded to new department.';
        }

        return $this->prepareJobResponse($job, $msg);
    }

    /**
     * Forward Job (Hanya Pindah Departemen, Status Tetap).
     */
    public function forward(Request $request, JobMarsho $job)
    {
        $request->validate([
            'to_department_id' => 'required|exists:marsho_departments,id',
            'note' => 'required|string|max:500',
            'attachments' => 'nullable|array|max:3'
        ]);

        $oldRoute = $job->latestRoute;
        $fromDeptName = $oldRoute->toDepartment->department_name ?? 'Initial';
        $toDeptName = MarshoDepartment::find($request->to_department_id)->department_name;

        // Reset timer SLA
        $job->update(['last_stage_update' => Carbon::now()]);

        // Buat Route Baru
        $route = $job->routes()->create([
            'job_id' => $job->id,
            'from_department_id' => $oldRoute->to_department_id,
            'to_department_id' => $request->to_department_id,
            'note' => "DEPARTMENT MOVE: {$fromDeptName} ➝ {$toDeptName}\nNote: " . $request->note,
            'created_by' => Auth::id()
        ]);

        $this->handleAttachments($request, $job, $route->id);

        return $this->prepareJobResponse($job, 'Job forwarded to ' . $toDeptName);
    }

    /**
     * Complete Job.
     */
    public function complete(Request $request, JobMarsho $job)
    {
        $request->validate(['note' => 'required|string', 'attachments' => 'nullable|array']);

        $job->update([
            'status' => 'completed',
            'tanggal_job_selesai' => Carbon::now(),
            'last_stage_update' => Carbon::now()
        ]);

        $latestRouteId = $job->latestRoute->id;

        $job->notes()->create([
            'job_id' => $job->id,
            'job_route_id' => $latestRouteId,
            'note' => "COMPLETED: Job marked as done by " . Auth::user()->name . ".\nNote: " . $request->note,
            'created_by' => Auth::id()
        ]);

        $this->handleAttachments($request, $job, $latestRouteId);

        // Kirim Email Notifikasi (Job Queue)
        SendJobCompletedEmail::dispatch($job);

        return $this->prepareJobResponse($job, 'Job marked as completed!');
    }

    /**
     * Cancel Job (Hanya Requester).
     */
    public function cancel(Request $request, JobMarsho $job)
    {
        $user = Auth::user();

        if ($job->pengaju_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $job->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason,
            'closed_at' => Carbon::now()
        ]);

        $job->notes()->create([
            'job_id' => $job->id,
            'job_route_id' => $job->latestRoute->id,
            'note' => "JOB CANCELLED by Requester.\nReason: " . $request->reason,
            'created_by' => $user->id
        ]);

        // Kirim notifikasi email ke departemen terakhir
        $currentDeptId = $job->latestRoute->to_department_id ?? null;
        if ($currentDeptId) {
            $recipients = User::whereHas('marshoProfile', function ($q) use ($currentDeptId) {
                $q->where('marsho_department_id', $currentDeptId);
            })->get();

            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->send(
                    new JobCancelledNotification($job, $request->reason, $user->name)
                );
            }
        }

        return $this->prepareJobResponse($job, 'Job cancelled.');
    }


    /**
     * API untuk mengambil detail History (Timeline).
     * LOGIKA PENTING: Matching attachment dengan aktivitas berdasarkan waktu.
     */
    public function showDetails(JobMarsho $job)
    {
        // Load semua data
        $job->load([
            'routes.fromDepartment',
            'routes.toDepartment',
            'routes.creator',
            'notes.creator',
            'attachments'
        ]);

        $activities = collect();
        $allAttachments = $job->attachments;

        // 1. Proses Routes (Perpindahan Departemen)
        foreach ($job->routes as $route) {
            // Cari file yang Route ID-nya sama DAN waktu upload berdekatan (toleransi 10 detik)
            $relatedFiles = $allAttachments->filter(function ($att) use ($route) {
                return $att->job_route_id == $route->id &&
                    $att->created_at->diffInSeconds($route->created_at) <= 15;
            });

            $activities->push([
                'type' => 'route', // Ikon Kuning
                'timestamp' => $route->created_at,
                'creator' => $route->creator,
                'data' => $route,
                'files' => $relatedFiles
            ]);
        }

        // 2. Proses Notes (Update Status / Komentar)
        foreach ($job->notes as $note) {
            // Cari file yang Route ID-nya sama dengan posisi note
            // DAN waktu upload berdekatan dengan waktu Note dibuat
            $relatedFiles = $allAttachments->filter(function ($att) use ($note) {
                return $att->job_route_id == $note->job_route_id &&
                    $att->created_at->diffInSeconds($note->created_at) <= 15;
            });

            $activities->push([
                'type' => 'note', // Ikon Biru
                'timestamp' => $note->created_at,
                'creator' => $note->creator,
                'data' => $note,
                'files' => $relatedFiles
            ]);
        }

        // Urutkan dari Terbaru ke Terlama
        $activities = $activities->sortByDesc('timestamp')->values();

        $html = View::make('jobs.partials.job_detail_content', compact('job', 'activities'))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Helper: Menyiapkan respons JSON + HTML partial untuk update Kanban real-time.
     */
    private function prepareJobResponse(JobMarsho $job, string $message)
    {
        $job->load(['pengaju', 'area', 'latestRoute.toDepartment', 'routes.fromDepartment', 'routes.toDepartment', 'attachments', 'notes.creator']);
        $html = View::make('jobs.partials.job_card', ['job' => $job])->render();

        // Trigger event untuk real-time update (Pusher/Echo)
        JobUpdated::dispatch($job, $html);

        return response()->json(['job' => $job, 'html' => $html, 'message' => $message]);
    }

    /**
     * Helper: Handle Upload File.
     */
    private function handleAttachments($request, $job, $routeId)
    {
        if ($request->hasFile('attachments')) {
            $attachmentNumber = $job->attachments()->count() + 1;
            foreach ($request->file('attachments') as $file) {
                $newFileName = "{$job->id_job}_{$attachmentNumber}_" . time() . "." . $file->getClientOriginalExtension();
                $path = $file->storeAs('job_attachments', $newFileName, 'public');

                $job->attachments()->create([
                    'job_id' => $job->id,
                    'job_route_id' => $routeId, // File dikaitkan ke Route saat ini
                    'file_path' => $path,
                    'file_name' => $newFileName,
                    'uploaded_by' => Auth::id()
                ]);
                $attachmentNumber++;
            }
        }
    }
}