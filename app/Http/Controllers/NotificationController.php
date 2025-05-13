<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function allNotifications()
    {
        return response()->json(Auth::user()->notifications);
    }

    public function unread()
    {
        return response()->json(Auth::user()->unreadNotifications);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => 'success',
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }

    public function markMultipleAsRead(Request $request)
    {
        $notificationIds = $request->input('notification_ids');
        if (!$notificationIds || empty($notificationIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No notification IDs provided.',
            ], 400);
        }

        $notifications = Auth::user()->notifications()->whereIn('id', $notificationIds)->get();

        if ($notifications->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No matching notifications found.',
            ], 404);
        }

        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'success',
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }

    // Add this to your NotificationController or create a new controller

    public function destroy($id)
    {
        try {
            $notification = Auth::user()->notifications()->where('id', $id)->first();

            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found'
                ], 404);
            }
            $notification->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Notification removed successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Could not delete notification'
            ], 500);
        }
    }

    public function destroyAll()
    {
        Auth::user()->notifications()->delete();

        return response()->json([
            'status' => 'success',
            'unreadCount' => 0,
        ]);
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications->count(),
        ]);
    }
}
