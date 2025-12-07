<?php

namespace App\Services;

use App\Models\QualificationApproval;
use App\Models\User;
use App\Notifications\StockUpdate;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewContactMessage;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\ReservationCreateNotification;
use App\Notifications\QualificationCanceledNotification;
use App\Notifications\ReservationCanceledNotification;

class NotificationService
{
    /**
     * Send notification to a specific user
     */
    public function sendToUser(User $user, $notification, array $additionalData = [])
    {
        try {
            $user->notify($notification);
            Log::info("Notification sent to user {$user->id}: " . get_class($notification));
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send notification to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to all admins
     */
    public function sendToAdmins($notification, array $additionalData = [])
    {
        try {
            $admins = User::where('utype', 'ADM')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, $notification);
                Log::info("Notification sent to " . $admins->count() . " admins: " . get_class($notification));
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to send notification to admins: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to all users
     */
    // public function sendToAllUsers($notification, array $additionalData = [])
    // {
    //     try {
    //         $users = User::where('utype', 'USR')->get();
    //         if ($users->isNotEmpty()) {
    //             Notification::send($users, $notification);
    //             Log::info("Notification sent to " . $users->count() . " users: " . get_class($notification));
    //             return true;
    //         }
    //         return false;
    //     } catch (\Exception $e) {
    //         Log::error("Failed to send notification to users: " . $e->getMessage());
    //         return false;
    //     }
    // }

    public function sendToAllUsers($notification, array $additionalData = [])
    {
        try {
            $totalUsers = User::where('utype', 'USR')->count();

            if ($totalUsers === 0) {
                Log::warning("No users found to send notification");
                return false;
            }

            if ($totalUsers < 1000) {
                $users = User::where('utype', 'USR')->get();
                Notification::send($users, $notification);
                Log::info("Notification sent to {$totalUsers} users: " . get_class($notification));
                return true;
            }

            $sentCount = 0;

            User::where('utype', 'USR')
                ->chunk(500, function ($users) use ($notification, &$sentCount) {
                    Notification::send($users, $notification);
                    $sentCount += $users->count();
                });
            Log::info("Notification queued/sent to {$sentCount} users: " . get_class($notification));
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send notification to users: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send low stock notification to admins
     */
    public function sendLowStockAlert($product, $quantity = null)
    {
        $notification = new LowStockNotification($product, $quantity);
        return $this->sendToAdmins($notification);
    }

    /**
     * Send new contact message notification to admins
     */
    public function sendNewContactMessage($contact)
    {
        $notification = new NewContactMessage($contact);
        return $this->sendToAdmins(notification: $notification);
    }

    /**
     * Send stock update notification to users
     */
    public function sendStockUpdate($product, $message)
    {
        $notification = new StockUpdate($product, $message);
        return $this->sendToAllUsers($notification);
    }

    public function sendOrderPlace(User $user, $order)
    {
        $notification = new OrderPlacedNotification($order);
        return $this->sendToUser($user, $notification);
    }

    public function sendCreateReservation(User $user, $payment)
    {
        $notification = new ReservationCreateNotification($payment);
        return $this->sendToUser($user, $notification);
    }

    public function sendCanceledReservation(User $user, $reservation, $isUserCancel = false)
    {
        $notification = new ReservationCanceledNotification($reservation, $isUserCancel);
        return $this->sendToUser($user, $notification);
    }

    public function sendCanceledQualification(User $user,  $qualification)
    {
        $notification = new QualificationCanceledNotification($qualification);
        return $this->sendToUser($user, $notification);
    }
    /**
     * Get user's notifications with pagination
     */
    public function getUserNotifications(User $user, $perPage = 15, $filter = 'all')
    {
        $query = $user->notifications();

        switch ($filter) {
            case 'unread':
                $query->whereNull('read_at');
                break;
            case 'read':
                $query->whereNotNull('read_at');
                break;
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get user's unread count
     */
    public function getUnreadCount(User $user)
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(User $user, $notificationId)
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(User $user)
    {
        return $user->unreadNotifications->markAsRead();
    }

    /**
     * Mark multiple notifications as read
     */
    public function markMultipleAsRead(User $user, array $notificationIds)
    {
        $notifications = $user->notifications()->whereIn('id', $notificationIds)->get();

        foreach ($notifications as $notification) {
            if (!$notification->read_at) {
                $notification->markAsRead();
            }
        }

        return $notifications->count();
    }

    /**
     * Delete notification
     */
    public function deleteNotification(User $user, $notificationId)
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->delete();
            return true;
        }

        return false;
    }

    /**
     * Delete all notifications for user
     */
    //   public function deleteAllNotifications(User $user)
    //   {
    //         return $user->notifications()->delete();
    //   }

    /**
     * Delete all notifications for user
     */
    public function deleteAllNotifications(User $user)
    {
        try {
            $count = $user->notifications()->count();
            Log::info("Attempting to delete {$count} notifications for user {$user->id}");
            $deleted = $user->notifications()->delete();
            Log::info("Deleted {$deleted} notifications for user {$user->id}");
            $remaining = $user->notifications()->count();
            Log::info("Remaining notifications for user {$user->id}: {$remaining}");
            return $deleted;
        } catch (\Exception $e) {
            Log::error("Failed to delete notifications for user {$user->id}: " . $e->getMessage());
            return false;
        }
    }
}
