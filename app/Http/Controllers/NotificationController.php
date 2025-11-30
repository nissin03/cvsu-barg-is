<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function allNotifications(Request $request): JsonResponse
    {
        if (!$request->ajax()) {
            abort(403, 'Direct access not allowed');
        }

        $perPage = $request->get('per_page', 15);
        $filter = $request->get('filter', 'all');

        $notifications = $this->notificationService->getUserNotifications(
            Auth::user(),
            $perPage,
            $filter
        );

        return response()->json([
            'status' => 'success',
            'data' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]
        ]);
    }

    public function unread(): JsonResponse
    {
        $notifications = Auth::user()->unreadNotifications()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function markAsRead($id): JsonResponse
    {
        $success = $this->notificationService->markAsRead(Auth::user(), $id);

        if (!$success) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found or already read'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'unreadCount' => $this->notificationService->getUnreadCount(Auth::user())
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $this->notificationService->markAllAsRead(Auth::user());

        return response()->json([
            'status' => 'success',
            'unreadCount' => $this->notificationService->getUnreadCount(Auth::user())
        ]);
    }

    public function markMultipleAsRead(Request $request): JsonResponse
    {
        $notificationIds = $request->input('notification_ids');

        if (!$notificationIds || empty($notificationIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No notification IDs provided.'
            ], 400);
        }

        $count = $this->notificationService->markMultipleAsRead(Auth::user(), $notificationIds);

        return response()->json([
            'status' => 'success',
            'marked_count' => $count,
            'unreadCount' => $this->notificationService->getUnreadCount(Auth::user())
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $success = $this->notificationService->deleteNotification(Auth::user(), $id);

        if (!$success) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification removed successfully',
            'unreadCount' => $this->notificationService->getUnreadCount(Auth::user())
        ]);
    }

    public function destroyAll(): JsonResponse
    {
        $user = Auth::user();
        // $this->notificationService->deleteAllNotifications(Auth::user());
        $unreadCount = $this->notificationService->getUnreadCount($user);
        return response()->json([
            'status' => 'success',
            'message' => 'All notifications removed successfully',
            // 'unreadCount' => 0
            'unreadCount' => $unreadCount,
            'totalCount' => 0

        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(Auth::user());

        return response()->json([
            'status' => 'success',
            'count' => $count
        ]);
    }
}
