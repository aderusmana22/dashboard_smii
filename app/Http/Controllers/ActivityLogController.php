<?php

namespace App\Http\Controllers;

use App\Models\JobMarsho;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Tambahkan ini
use Spatie\Activitylog\Models\Activity; // <-- Gunakan model Activity langsung

class ActivityLogController extends Controller
{
    /**
     * Menampilkan halaman utama log aktivitas dengan filter.
     * Dibatasi berdasarkan hak akses pengguna.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Mulai query dasar dengan relasi yang dibutuhkan
        $query = Activity::with([
            'causer', // User yang menyebabkan event
            'subject' // Model yang terkait (dalam hal ini JobMarsho)
        ]);

        // Hanya tampilkan log yang subject-nya adalah JobMarsho
        $query->where('subject_type', JobMarsho::class);

        // --- BATASAN HAK AKSES ---
        // Jika user BUKAN Super Admin, batasi query
        if (!$user->isSuperAdmin()) {
            // Tambahkan kondisi: hanya tampilkan log di mana subject (JobMarsho)
            // memiliki 'pengaju_id' yang sama dengan ID user yang sedang login.
            $query->whereHasMorph('subject', [JobMarsho::class], function ($jobQuery) use ($user) {
                $jobQuery->where('pengaju_id', $user->id);
            });
        }
        // Super Admin akan melewati blok ini dan melihat semua log.
        // --- AKHIR BATASAN HAK AKSES ---


        // --- FILTER DINAMIS ---
        // Terapkan filter di atas query yang sudah dibatasi hak aksesnya
        if ($request->filled('subject_filter')) {
            $jobId = $request->input('subject_filter');
            // Filter berdasarkan id_job di dalam relasi subject
            $query->whereHasMorph('subject', [JobMarsho::class], function ($jobQuery) use ($jobId) {
                $jobQuery->where('id_job', 'like', '%' . $jobId . '%');
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
        // --- AKHIR FILTER DINAMIS ---


        // --- PERSIAPAN DATA UNTUK DROPDOWN FILTER ---
        // Clone query SEBELUM pagination untuk mendapatkan data filter yang relevan
        $filterQuery = clone $query;

        // Ambil ID unik dari user yang menyebabkan event HANYA dari data yang sudah difilter
        $causerIds = $filterQuery->pluck('causer_id')->unique()->filter();
        $users = User::whereIn('id', $causerIds)->orderBy('name')->pluck('name', 'id');

        // Ambil nama event yang unik HANYA dari data yang sudah difilter
        $eventNames = $filterQuery->select('event')->distinct()->pluck('event');
        // --- AKHIR PERSIAPAN DATA DROPDOWN ---


        // Ambil hasil akhir dengan pagination
        $activities = $query->latest()->paginate(20)->withQueryString();

        return view('activity-logs.index', compact('activities', 'users', 'eventNames', 'request'));
    }

    /**
     * Menampilkan log aktivitas untuk satu Job spesifik.
     * (Tidak perlu diubah karena sudah spesifik per job, namun pastikan ada otorisasi di level route jika perlu)
     */
    public function showForJob(JobMarsho $job)
    {
        // Otorisasi tambahan (opsional tapi direkomendasikan)
        // Pastikan hanya super admin atau pengaju job yang bisa melihat halaman ini.
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $job->pengaju_id !== $user->id) {
            abort(403, 'You are not authorized to view this page.');
        }

        $activities = $job->activities()->with('causer')->latest()->paginate(15);

        return view('activity-logs.show', compact('job', 'activities'));
    }
}