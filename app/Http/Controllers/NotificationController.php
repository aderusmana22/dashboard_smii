<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class NotificationController extends Controller
{
    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('notification_id');
        $currentUser = Auth::user();

        if ($notificationId) {
            // Tandai satu notifikasi sebagai dibaca
            $notification = $currentUser->notifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
                $notification->update(['read_at' => now()]);
                return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
            }
        } else {
            // Tandai semua notifikasi sebagai dibaca
            $currentUser->unreadNotifications->markAsRead();
            $currentUser->unreadNotifications()->update(['read_at' => now()]);
            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function clearAll()
    {
        try {
            DB::table('notifications')->truncate(); // Hapus semua notifikasi
            return response()->json(['message' => 'All notifications deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete notifications.'], 500);
        }
    }

    /* Requisition Log History */
    public function log()
    {
        $logs = Activity::all();
        return view('page.requisition.log-history', compact('logs'));
    }

    public function getDataLog(Request $request)
    {
        // Ambil data log dari database
        $logs = Activity::select('subject_id','event', 'description', 'created_at')->get();
        // Kembalikan data dalam format JSON
        return response()->json($logs);
    }
}
