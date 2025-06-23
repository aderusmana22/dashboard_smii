<?php

namespace App\Http\Controllers;

use App\Models\KanbanActivityLog; // Gunakan model kustom Anda
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity; // Atau gunakan ini jika tidak membuat model kustom

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
        $query = KanbanActivityLog::latest(); // Atau Activity::latest();

        // Filter berdasarkan event
        if ($request->filled('event_filter')) {
            $query->where('event', $request->input('event_filter'));
        }

        // Filter berdasarkan causer (user yang melakukan aksi)
        if ($request->filled('causer_filter')) {
            $query->where('causer_id', $request->input('causer_filter'))
                  ->where('causer_type', User::class); // Asumsi causer selalu User
        }

        // Filter berdasarkan subject (Task ID atau ID Job)
        if ($request->filled('subject_filter')) {
            $subjectId = $request->input('subject_filter');
            // Coba cari Task berdasarkan id_job atau id
            $task = Task::where('id_job', $subjectId)->orWhere('id', $subjectId)->first();
            if ($task) {
                $query->where('subject_id', $task->id)->where('subject_type', Task::class);
            } else {
                // Jika tidak ditemukan, mungkin user memasukkan ID numerik langsung
                // Ini kurang ideal karena bisa ambigu, tapi bisa ditambahkan jika perlu
                // $query->where('subject_id', $subjectId);
            }
        }

        // Filter berdasarkan rentang tanggal
        if ($request->filled('date_from_filter')) {
            $query->whereDate('created_at', '>=', $request->input('date_from_filter'));
        }
        if ($request->filled('date_to_filter')) {
            $query->whereDate('created_at', '<=', $request->input('date_to_filter'));
        }

        // Hanya log yang terkait dengan Task
        $query->forTasks(); // Menggunakan scope dari model KanbanActivityLog

        $activities = $query->with(['causer', 'subject'])->paginate(25)->withQueryString();

        // Data untuk filter dropdown
        $eventNames = KanbanActivityLog::forTasks()->distinct()->pluck('event')->filter()->sort();
        $users = User::orderBy('name')->pluck('name', 'id'); // Untuk filter causer

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
        // Ambil log aktivitas hanya untuk task ini
        // Model Task sudah memiliki method activities() dari trait LogsActivity
        $activities = $task->activities()->latest()->with(['causer'])->paginate(15);

        return view('activity_logs.for_task', compact('task', 'activities'));
    }
}