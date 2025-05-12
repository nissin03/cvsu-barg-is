<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications;
        return view('admin.index', compact('notifications'));
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

    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'status' => 'success',
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
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
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }
}
