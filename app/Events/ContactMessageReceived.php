<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use App\Notifications\NewContactMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ContactMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $contactMessage;

    /**
     * Create a new event instance.
     */
    public function __construct($contact)
    {
        $this->contactMessage = $contact;
        $this->notifyAdmins();
    }

    protected function notifyAdmins(): void
    {
        try {
            $admins = User::where('utype', 'ADM')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewContactMessage($this->contactMessage));
                Log::info('Admin notification sent for contact ID: ' . $this->contactMessage->id);
            }
        } catch (\Exception $e) {
            Log::error('Error sending admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-notification'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'contactMessage' => [
                'id' => $this->contactMessage->id,
                'name' => $this->contactMessage->name,
                'email' => $this->contactMessage->email,
                'message' => $this->contactMessage->message,
                'created_at' => $this->contactMessage->created_at->toIso8601String(),
            ]
        ];
    }
}
