<?php

namespace App\Http\Controllers;

use App\Models\JobMarsho;
use App\Models\MarshoDepartment;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JobController extends Controller
{
    /**
     * Menampilkan papan Kanban dengan semua pekerjaan.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Query ini sudah benar, memuat semua relasi yang dibutuhkan tanpa batasan.
        $jobs = JobMarsho::with([
            'pengaju', 
            'area',
            'latestRoute.toDepartment', 
            'routes.toDepartment', 
            'routes.fromDepartment',
            'attachments.uploadedByUser',
            'notes.creator'
        ])->latest()->get();

        $openJobs = $jobs->where('status', 'open');
        $onProcessJobs = $jobs->where('status', 'on_process');
        $completedJobs = $jobs->where('status', 'completed');
        $closedJobs = $jobs->where('status', 'closed');
        
        $departments = MarshoDepartment::pluck('department_name', 'id');
        $areas = Area::pluck('name', 'id');

        return view('jobs.index', compact('openJobs', 'onProcessJobs', 'completedJobs', 'closedJobs', 'user', 'departments', 'areas'));
    }

    /**
     * Menyimpan pekerjaan baru, termasuk "Initial Attachments".
     */
    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'list_job' => 'required|string',
            'to_department_id' => 'required|exists:marsho_departments,id',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120'
        ]);

        $user = Auth::user();

        $job = JobMarsho::create([
            'id_job' => JobMarsho::generateJobId(),
            'pengaju_id' => $user->id,
            'area_id' => $request->area_id,
            'list_job' => $request->list_job,
            'tanggal_job_mulai' => Carbon::now(),
            'status' => 'open',
        ]);

        $route = $job->routes()->create([
            'to_department_id' => $request->to_department_id,
            'note' => 'Job created and assigned.',
            'created_by' => $user->id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // MODIFIKASI: Simpan file ke dalam sub-folder 'open'
                $path = $file->store('job_attachments/open', 'public');
                
                $job->attachments()->create([
                    'job_id' => $job->id,
                    'job_route_id' => $route->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'uploaded_by' => $user->id,
                ]);
            }
        }

        return response()->json($job);
    }
    
    /**
     * Memulai pekerjaan, mengubah status menjadi 'on_process'.
     */
    public function start(JobMarsho $job)
    {
        $job->update(['status' => 'on_process']);
        return response()->json($job);
    }

    /**
     * Meneruskan pekerjaan ke departemen lain.
     */
    public function forward(Request $request, JobMarsho $job)
    {
        $request->validate([
            'to_department_id' => 'required|exists:marsho_departments,id',
            'note' => 'required|string',
        ]);

        $job->routes()->create([
            'job_id' => $job->id,
            'from_department_id' => $job->latestRoute->to_department_id,
            'to_department_id' => $request->to_department_id,
            'note' => $request->note,
            'created_by' => Auth::id(),
        ]);

        return response()->json($job);
    }

    /**
     * Menyelesaikan pekerjaan, termasuk menyimpan "Closing Attachments".
     */
    public function complete(Request $request, JobMarsho $job)
    {
        $request->validate([
            'note' => 'required|string',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120'
        ]);

        $job->update([
            'status' => 'completed',
            'tanggal_job_selesai' => Carbon::now(),
        ]);
        
        $latestRouteId = $job->latestRoute->id;

        $job->notes()->create([
            'job_id' => $job->id,
            'job_route_id' => $latestRouteId,
            'note' => $request->note,
            'created_by' => Auth::id(),
        ]);

        if ($request->hasFile('attachments')) {
            $user = Auth::user();
            foreach ($request->file('attachments') as $file) {
                // MODIFIKASI: Simpan file ke dalam sub-folder 'closed'
                $path = $file->store('job_attachments/closed', 'public');

                $job->attachments()->create([
                    'job_id' => $job->id,
                    'job_route_id' => $latestRouteId,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'uploaded_by' => $user->id,
                ]);
            }
        }

        return response()->json($job);
    }

    /**
     * Menutup pekerjaan, ini adalah langkah konfirmasi akhir tanpa input file.
     */
    public function close(Request $request, JobMarsho $job)
    {
        $job->update([
            'status' => 'closed',
            'penutup_id' => Auth::id(),
            'closed_at' => Carbon::now(),
        ]);

        return response()->json($job);
    }
}