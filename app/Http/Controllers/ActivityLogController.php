<?php

namespace App\Http\Controllers;

use App\Models\JobMarsho;
use App\Models\MarshoActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan halaman utama log aktivitas dengan filter.
     */
    public function index(Request $request)
    {
        $query = MarshoActivityLog::forJobs()->with([
            'causer',
            'subject.pengaju',
            'subject.area'
        ]);

        if ($request->filled('subject_filter')) {
            $jobId = $request->input('subject_filter');
            $query->whereHas('subject', function ($q) use ($jobId) {
                $q->where('id_job', 'like', '%' . $jobId . '%');
            });
        }

        if ($request->filled('event_filter')) {
            $query->where('event', $request->input('event_filter'));
        }

        if ($request->filled('causer_filter')) {
            $query->where('causer_id', $request->input('causer_filter'));
        }

        if ($request->filled('date_from_filter')) {
            $query->whereDate('created_at', '>=', $request->input('date_from_filter'));
        }
        if ($request->filled('date_to_filter')) {
            $query->whereDate('created_at', '<=', $request->input('date_to_filter'));
        }

        $activities = $query->latest()->paginate(20);

        $users = User::orderBy('name')->pluck('name', 'id');
        $eventNames = MarshoActivityLog::forJobs()->select('event')->distinct()->pluck('event');

        return view('activity-logs.index', compact('activities', 'users', 'eventNames', 'request'));
    }

    /**
     * Menampilkan log aktivitas untuk satu Job spesifik.
     */
    public function showForJob(JobMarsho $job)
    {
        $activities = $job->activities()->with('causer')->latest()->paginate(15);

        return view('activity-logs.show', compact('job', 'activities'));
    }
}