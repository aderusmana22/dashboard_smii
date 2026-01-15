<?php

namespace App\Http\Controllers;

use App\Events\JobUpdated;
use App\Models\JobMarsho;
use App\Models\MarshoDepartment;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendJobCompletedEmail;
use App\Models\User; // Jangan lupa import User
use Illuminate\Support\Facades\Mail;
use App\Mail\JobCancelledNotification;


class JobController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Eager load history lengkap untuk modal "Show More"
        $jobs = JobMarsho::with([
            'pengaju',
            'area',
            'latestRoute.toDepartment',
            'routes.fromDepartment',
            'routes.toDepartment',
            'routes.creator',
            'attachments',
            'notes.creator'
        ])->latest()->get();

        // Filtering status (sama seperti sebelumnya)
        $toBeScheduledJobs = $jobs->where('status', 'to_be_scheduled');
        $scheduledJobs = $jobs->where('status', 'scheduled');
        $preparationJobs = $jobs->where('status', 'preparation');
        $onGoingJobs = $jobs->where('status', 'on_going');
        $completedJobs = $jobs->where('status', 'completed');
        $closedJobs = $jobs->where('status', 'closed');

        $departments = MarshoDepartment::pluck('department_name', 'id');
        $areas = Area::pluck('name', 'id');

        return view('jobs.index', compact('toBeScheduledJobs', 'scheduledJobs', 'preparationJobs', 'onGoingJobs', 'completedJobs', 'closedJobs', 'user', 'departments', 'areas'));
    }

    // Fungsi helper private untuk render partial view (AJAX response)
    private function prepareJobResponse(JobMarsho $job, string $message)
    {
        // Reload relasi agar tampilan card update
        $job->load(['pengaju', 'area', 'latestRoute.toDepartment', 'routes.fromDepartment', 'routes.toDepartment', 'attachments', 'notes.creator']);
        $html = View::make('jobs.partials.job_card', ['job' => $job])->render();
        JobUpdated::dispatch($job, $html);
        return response()->json(['job' => $job, 'html' => $html, 'message' => $message]);
    }

    // CREATE JOB
    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'list_job' => 'required|string',
            'to_department_id' => 'required|exists:marsho_departments,id',
            'start_date' => 'required|date', // Input Tanggal Mulai
            'deadline' => 'required|date|after_or_equal:start_date', // Input Deadline Total
            'note' => 'nullable|string|max:500',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120'
        ]);

        $jobIdString = JobMarsho::generateJobId();

        // Cek apakah start date hari ini? Jika ya langsung Scheduled, jika besok/lusa status To Be Scheduled
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
                'deadline' => $request->deadline, // Simpan deadline
                'status' => $status,
                'last_stage_update' => Carbon::now(), // Mulai timer 3 hari
            ]);

            $route = $job->routes()->create([
                'to_department_id' => $request->to_department_id,
                'note' => $request->note ?: 'Job created.',
                'created_by' => Auth::id()
            ]);

            $this->handleAttachments($request, $job, $route->id);
            return $job;
        });

        // Karena DB Transaction return $job, kita ambil instance terbaru diluar
        $job = JobMarsho::where('id_job', $jobIdString)->first();

        return $this->prepareJobResponse($job, 'Job created successfully!');
    }

    // Helper untuk handle upload
    private function handleAttachments($request, $job, $routeId)
    {
        if ($request->hasFile('attachments')) {
            $attachmentNumber = $job->attachments()->count() + 1;
            foreach ($request->file('attachments') as $file) {
                $newFileName = "{$job->id_job}_{$attachmentNumber}_" . time() . "." . $file->getClientOriginalExtension();
                $path = $file->storeAs('job_attachments', $newFileName, 'public');
                $job->attachments()->create([
                    'job_id' => $job->id,
                    'job_route_id' => $routeId,
                    'file_path' => $path,
                    'file_name' => $newFileName,
                    'uploaded_by' => Auth::id()
                ]);
                $attachmentNumber++;
            }
        }
    }

    // GENERIC METHOD UNTUK PINDAH STATUS (Schedule, Preparation, Ongoing)
    // Sekarang semua butuh Request karena perlu Note & Attachments
    public function changeStatus(Request $request, JobMarsho $job)
    {
        $request->validate([
            'status' => 'required|in:scheduled,preparation,on_going',
            'note' => 'required|string|max:1000', // Note wajib saat pindah
            'attachments' => 'nullable|array|max:3', // Bukti foto/file
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        $job->update([
            'status' => $request->status,
            'last_stage_update' => Carbon::now() // Reset timer 3 hari
        ]);

        // Simpan Note & Attachment (Dikaitkan dengan Route terakhir agar rapi)
        // Atau buat JobNote baru jika tidak pindah departemen
        $latestRoute = $job->latestRoute;

        // Kita simpan sebagai Note biasa tapi dikaitkan dengan route saat ini
        $job->notes()->create([
            'job_id' => $job->id,
            'job_route_id' => $latestRoute ? $latestRoute->id : null,
            'note' => "Status changed to " . ucfirst(str_replace('_', ' ', $request->status)) . ". Note: " . $request->note,
            'created_by' => Auth::id()
        ]);

        // Handle attachment (bukti pindah alur)
        // Kita pakai route ID terakhir untuk mengelompokkan file
        $this->handleAttachments($request, $job, $latestRoute ? $latestRoute->id : null);

        return $this->prepareJobResponse($job, 'Job moved to ' . ucfirst($request->status));
    }

    // Forward (Pindah Departemen) - Reset Timer juga
    public function forward(Request $request, JobMarsho $job)
    {
        $request->validate([
            'to_department_id' => 'required|exists:marsho_departments,id',
            'note' => 'required|string|max:500',
            'attachments' => 'nullable|array|max:3'
        ]);

        $job->update(['last_stage_update' => Carbon::now()]); // Reset timer

        $route = $job->routes()->create([
            'job_id' => $job->id,
            'from_department_id' => $job->latestRoute->to_department_id,
            'to_department_id' => $request->to_department_id,
            'note' => $request->note,
            'created_by' => Auth::id()
        ]);

        $this->handleAttachments($request, $job, $route->id);

        return $this->prepareJobResponse($job, 'Job forwarded successfully!');
    }

    // Complete
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
            'note' => "COMPLETED: " . $request->note,
            'created_by' => Auth::id()
        ]);

        $this->handleAttachments($request, $job, $latestRouteId);

        SendJobCompletedEmail::dispatch($job);
        return $this->prepareJobResponse($job, 'Job marked as completed!');
    }

    // API untuk mengambil detail lengkap (Timeline)
    public function showDetails(JobMarsho $job)
    {
        // Ambil semua note, route, dan attachment, urutkan berdasarkan waktu
        $activities = collect();

        // 1. Masukkan Routes (Perpindahan Dept)
        foreach ($job->routes as $route) {
            $activities->push([
                'type' => 'route',
                'timestamp' => $route->created_at,
                'data' => $route,
                'files' => $job->attachments->where('job_route_id', $route->id)
            ]);
        }

        // 2. Masukkan Notes (Update Status dalam Dept yg sama)
        foreach ($job->notes as $note) {
            $activities->push([
                'type' => 'note',
                'timestamp' => $note->created_at,
                'data' => $note,
                // Files yang mungkin nempel di note ini (jika logika attachment diubah ke note_id)
                // Di skema saat ini file nempel ke route_id, jadi kita anggap file di handle di route
                'files' => collect()
            ]);
        }

        $activities = $activities->sortByDesc('timestamp')->values();

        // Render partial view untuk isi modal
        $html = View::make('jobs.partials.job_detail_content', compact('job', 'activities'))->render();

        return response()->json(['html' => $html]);
    }

    // Close job
    public function close(Request $request, JobMarsho $job)
    {
        $job->update(['status' => 'closed', 'penutup_id' => Auth::id(), 'closed_at' => Carbon::now()]);
        return $this->prepareJobResponse($job, 'Job closed.');
    }

    // Tambahkan di JobController

    public function cancel(Request $request, JobMarsho $job)
    {
        $user = Auth::user();

        if ($job->pengaju_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        // 1. Simpan perubahan status
        $job->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason,
            'closed_at' => \Carbon\Carbon::now()
        ]);

        // 2. Buat Note System
        $job->notes()->create([
            'job_id' => $job->id,
            'job_route_id' => $job->latestRoute->id,
            'note' => "JOB CANCELLED. Reason: " . $request->reason,
            'created_by' => $user->id
        ]);

        // --- LOGIKA KIRIM EMAIL ---

        // Ambil ID departemen yang sedang memegang job ini
        $currentDeptId = $job->latestRoute->to_department_id ?? null;

        if ($currentDeptId) {
            // Cari semua user yang ada di departemen tersebut
            $recipients = User::whereHas('marshoProfile', function ($q) use ($currentDeptId) {
                $q->where('marsho_department_id', $currentDeptId);
            })->get();

            // Kirim email ke setiap anggota departemen
            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->send(
                    new JobCancelledNotification($job, $request->reason, $user->name)
                );
            }
        }

        return $this->prepareJobResponse($job, 'Job has been cancelled and department notified.');
    }
}