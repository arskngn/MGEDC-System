<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\NotificationLog;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getUnreadCount(Request $request)
    {
        $lastId = $request->query('last_id', 0);
        $userId = Auth::id();
        
        $newNotifications = SystemNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'unread_count' => SystemNotification::getUnreadCount($userId),
            'new_notifications' => $newNotifications,
        ]);
    }

    public function index()
    {
        $userId = Auth::id();
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;

        // Auto-mark welcome notifications older than 1 day as read
        SystemNotification::where('user_id', $userId)
            ->where('type', 'welcome')
            ->where('is_read', false)
            ->where('created_at', '<=', now()->subDay())
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $notifications = SystemNotification::where('user_id', $userId)
            ->orderByRaw("CASE 
                WHEN type = 'welcome' AND is_read = 0 AND created_at > ? THEN 0 
                ELSE 1 
                END ASC", [now()->subDay()])
            ->latest()
            ->paginate($perPage);
            
        $logs = NotificationLog::with(['customer', 'user'])
            ->latest()
            ->paginate($perPage);
            
        return view('notifications.index', compact('notifications', 'logs'));
    }

    public function markAsRead(SystemNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function markWelcomeAsRead()
    {
        SystemNotification::where('user_id', Auth::id())
            ->where('type', 'welcome')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function destroy(SystemNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        SystemNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function deleteAll()
    {
        SystemNotification::where('user_id', Auth::id())->delete();

        return response()->json(['success' => true]);
    }
}
