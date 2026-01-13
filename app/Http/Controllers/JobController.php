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
use App\Jobs\SendJobCompletedEmail;

class JobController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $jobs = JobMarsho::with(['pengaju', 'area', 'latestRoute.toDepartment', 'routes.fromDepartment', 'routes.toDepartment', 'routes.creator', 'attachments', 'notes.creator'])->latest()->get();
        
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

    private function prepareJobResponse(JobMarsho $job, string $message)
    {
        $job->load(['pengaju', 'area', 'latestRoute.toDepartment', 'routes.fromDepartment', 'routes.toDepartment', 'routes.creator', 'attachments', 'notes.creator']);
        $html = View::make('jobs.partials.job_card', ['job' => $job])->render();
        JobUpdated::dispatch($job, $html);
        return response()->json(['job' => $job, 'html' => $html, 'message' => $message]);
    }

    public function store(Request $request)
    {
        $request->validate(['area_id' => 'required|exists:areas,id', 'list_job' => 'required|string', 'to_department_id' => 'required|exists:marsho_departments,id', 'note' => 'nullable|string|max:500', 'attachments' => 'nullable|array|max:3', 'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,bmp,svg,webp,pdf,doc,docx|max:5120']);
        $jobIdString = JobMarsho::generateJobId();
        $job = JobMarsho::create(['id_job' => $jobIdString, 'pengaju_id' => Auth::id(), 'area_id' => $request->area_id, 'list_job' => $request->list_job, 'tanggal_job_mulai' => Carbon::now(), 'status' => 'to_be_scheduled']);
        $route = $job->routes()->create(['to_department_id' => $request->to_department_id, 'note' => $request->note ?: 'Job created.', 'created_by' => Auth::id()]);
        if ($request->hasFile('attachments')) {
            $attachmentNumber = 1;
            foreach ($request->file('attachments') as $file) {
                $newFileName = "{$jobIdString}_{$attachmentNumber}." . $file->getClientOriginalExtension();
                $path = $file->storeAs('job_attachments/open', $newFileName, 'public');
                $job->attachments()->create(['job_id' => $job->id, 'job_route_id' => $route->id, 'file_path' => $path, 'file_name' => $newFileName, 'uploaded_by' => Auth::id()]);
                $attachmentNumber++;
            }
        }
        return $this->prepareJobResponse($job, 'Job created successfully!');
    }

    public function setScheduled(JobMarsho $job)
    {
        $job->update(['status' => 'scheduled']);
        return $this->prepareJobResponse($job, 'Job marked as Scheduled.');
    }

    public function setPreparation(JobMarsho $job)
    {
        $job->update(['status' => 'preparation']);
        return $this->prepareJobResponse($job, 'Job moved to Preparation.');
    }

    public function start(JobMarsho $job)
    {
        $job->update(['status' => 'on_going']);
        return $this->prepareJobResponse($job, 'Job status updated to On Going!');
    }

    public function forward(Request $request, JobMarsho $job)
    {
        $request->validate(['to_department_id' => 'required|exists:marsho_departments,id', 'note' => 'required|string|max:500']);
        $job->routes()->create(['job_id' => $job->id, 'from_department_id' => $job->latestRoute->to_department_id, 'to_department_id' => $request->to_department_id, 'note' => $request->note, 'created_by' => Auth::id()]);
        return $this->prepareJobResponse($job, 'Job forwarded successfully!');
    }

    public function complete(Request $request, JobMarsho $job)
    {
        $request->validate(['note' => 'required|string|max:500', 'attachments' => 'nullable|array|max:3', 'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,bmp,svg,webp,pdf,doc,docx|max:5120']);
        $job->update(['status' => 'completed', 'tanggal_job_selesai' => Carbon::now()]);
        $latestRouteId = $job->latestRoute->id;
        $job->notes()->create(['job_id' => $job->id, 'job_route_id' => $latestRouteId, 'note' => $request->note, 'created_by' => Auth::id()]);
        if ($request->hasFile('attachments')) {
            $attachmentNumber = $job->closing_attachments->count() + 1;
            foreach ($request->file('attachments') as $file) {
                $newFileName = "{$job->id_job}_{$attachmentNumber}." . $file->getClientOriginalExtension();
                $path = $file->storeAs('job_attachments/closed', $newFileName, 'public');
                $job->attachments()->create(['job_id' => $job->id, 'job_route_id' => $latestRouteId, 'file_path' => $path, 'file_name' => $newFileName, 'uploaded_by' => Auth::id()]);
                $attachmentNumber++;
            }
        }
        SendJobCompletedEmail::dispatch($job);
        return $this->prepareJobResponse($job, 'Job marked as completed!');
    }

    public function close(Request $request, JobMarsho $job)
    {
        $job->update(['status' => 'closed', 'penutup_id' => Auth::id(), 'closed_at' => Carbon::now()]);
        return $this->prepareJobResponse($job, 'Job has been closed.');
    }
}