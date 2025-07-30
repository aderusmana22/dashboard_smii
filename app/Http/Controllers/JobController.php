<?php
namespace App\Http\Controllers;
use App\Models\JobMarsho;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class JobController extends Controller {
    public function index() {
        $user = Auth::user()->load('department');
        if ($user) {
            $user->is_super_admin = $user->hasRole('Super Admin'); // Ganti dengan logika role Anda
        }
        $jobs = JobMarsho::with(['pengaju', 'penutup', 'latestRoute.toDepartment'])->latest()->get();
        $groupedJobs = $jobs->groupBy('status');
        $departments = Department::orderBy('department_name')->pluck('department_name', 'id');
        return view('jobs.index', [
            'openJobs' => $groupedJobs->get('open', collect()),
            'onProcessJobs' => $groupedJobs->get('on_process', collect()),
            'completedJobs' => $groupedJobs->get('completed', collect()),
            'closedJobs' => $groupedJobs->get('closed', collect()),
            'user' => $user,
            'departments' => $departments,
        ]);
    }
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'area' => 'required|string|max:255',
            'list_job' => 'required|string',
            'to_department_id' => 'required|exists:departments,id',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        DB::beginTransaction();
        try {
            $job = JobMarsho::create(['id_job' => JobMarsho::generateJobId(), 'pengaju_id' => Auth::id(), 'area' => $request->area, 'list_job' => $request->list_job, 'tanggal_job_mulai' => Carbon::today(), 'status' => 'open']);
            $job->routes()->create(['from_department_id' => null, 'to_department_id' => $request->to_department_id, 'note' => 'Initial job creation.', 'created_by' => Auth::id()]);
            DB::commit();
            $job->load(['pengaju', 'latestRoute.toDepartment']);
            return response()->json($job, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create job.'], 500);
        }
    }
    public function start(JobMarsho $job) {
        if ($job->status !== 'open') return response()->json(['message' => 'Job cannot be started.'], 403);
        $job->update(['status' => 'on_process']);
        activity()->performedOn($job)->causedBy(Auth::user())->log('Job was started');
        $job->load(['pengaju', 'latestRoute.toDepartment']);
        return response()->json($job);
    }
    public function forward(Request $request, JobMarsho $job) {
        $validator = Validator::make($request->all(), ['to_department_id' => 'required|exists:departments,id', 'note' => 'required|string|max:1000']);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        if ($job->status !== 'on_process') return response()->json(['message' => 'Only on-process jobs can be forwarded.'], 403);
        $latestRoute = $job->latestRoute;
        $job->routes()->create(['from_department_id' => $latestRoute->to_department_id, 'to_department_id' => $request->to_department_id, 'note' => $request->note, 'created_by' => Auth::id()]);
        $toDeptName = Department::find($request->to_department_id)->department_name ?? 'Unknown';
        activity()->performedOn($job)->causedBy(Auth::user())->withProperty('note', $request->note)->log("Job was forwarded to {$toDeptName}");
        $job->load(['pengaju', 'latestRoute.toDepartment']);
        return response()->json($job);
    }
    public function complete(Request $request, JobMarsho $job) {
        $validator = Validator::make($request->all(), ['note' => 'required|string|max:1000']);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        if ($job->status !== 'on_process') return response()->json(['message' => 'Only on-process jobs can be completed.'], 403);
        $job->update(['status' => 'completed', 'tanggal_job_selesai' => Carbon::today()]);
        $job->notes()->create(['note' => 'Completion Note: ' . $request->note, 'created_by' => Auth::id()]);
        activity()->performedOn($job)->causedBy(Auth::user())->withProperty('note', $request->note)->log('Job was completed');
        $job->load(['pengaju', 'latestRoute.toDepartment']);
        return response()->json($job);
    }
    public function close(JobMarsho $job) {
        if ($job->status !== 'completed') return response()->json(['message' => 'Only completed jobs can be closed.'], 403);
        if ($job->pengaju_id !== Auth::id() && !(Auth::user()->is_super_admin ?? false)) return response()->json(['message' => 'Only the requester can close the job.'], 403);
        $job->update(['status' => 'closed', 'penutup_id' => Auth::id(), 'closed_at' => Carbon::now()]);
        activity()->performedOn($job)->causedBy(Auth::user())->log('Job was closed and archived');
        $job->load(['pengaju', 'latestRoute.toDepartment', 'penutup']);
        return response()->json($job);
    }
}