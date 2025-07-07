<?php

namespace App\Http\Controllers;

use App\Models\KanbanActivityLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Mengurutkan dari yang terlama (ID terkecil) ke terbaru
        $query = KanbanActivityLog::oldest();

        // Filter berdasarkan event
        if ($request->filled('event_filter')) {
            $query->where('event', $request->input('event_filter'));
        }

        // Filter berdasarkan causer (user yang melakukan aksi)
        if ($request->filled('causer_filter')) {
            $query->where('causer_id', $request->input('causer_filter'))
                  ->where('causer_type', User::class);
        }

        // Filter berdasarkan subject (Task Job ID) yang sudah dioptimalkan
        if ($request->filled('subject_filter')) {
            $subjectId = $request->input('subject_filter');
            
            $query->whereHasMorph(
                'subject', // Nama relasi polymorphic
                [Task::class], // Hanya cari di model Task
                function ($q) use ($subjectId) {
                    // Tambahkan kondisi pada model Task
                    $q->where('id_job', $subjectId)->orWhere('id', $subjectId);
                }
            );
        }

        // Filter berdasarkan rentang tanggal
        if ($request->filled('date_from_filter')) {
            $query->whereDate('created_at', '>=', $request->input('date_from_filter'));
        }
        if ($request->filled('date_to_filter')) {
            $query->whereDate('created_at', '<=', $request->input('date_to_filter'));
        }

        // Hanya log yang terkait dengan Task
        $query->forTasks();

        // OPTIMASI PERFORMA: Mengatasi N+1 problem dengan eager loading bersarang
        // Menggunakan nama relasi yang benar: 'pengaju' dan 'department'
        $activities = $query->with([
            'causer', 
            'subject.pengaju', 
            'subject.department'
        ])->paginate(25)->withQueryString();

        // Data untuk filter dropdown
        $eventNames = KanbanActivityLog::forTasks()->distinct()->pluck('event')->filter()->sort();
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('activity_logs.index', compact('activities', 'eventNames', 'users', 'request'));
    }

    /**
     * Display activity logs for a specific Task.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\View\View
     */
    public function showForTask(Task $task)
    {
        // Mengubah ke oldest() agar konsisten dengan halaman index
        $activities = $task->activities()->oldest()->with(['causer'])->paginate(15);

        return view('activity_logs.for_task', compact('task', 'activities'));
    }
}