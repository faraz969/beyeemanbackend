<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Notification::where('recipient_id', $user->id);

        // Filter by read status
        if ($request->has('is_read') && $request->is_read !== '') {
            $query->where('is_read', $request->is_read);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = Notification::where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = Notification::where('recipient_id', $user->id)
            ->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        Notification::where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Get a specific notification
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $notification = Notification::where('recipient_id', $user->id)
            ->findOrFail($id);

        // Mark as read when viewing
        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }
}
