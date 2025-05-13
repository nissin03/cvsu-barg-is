<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class LowStockNotification extends Notification
{
    use Queueable;

    public $product;
    public $quantity;

    /**
     * Create a new notification instance.
     */
    public function __construct($product)
    {
        $this->product = $product;
        $this->quantity = $product->quantity;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'quantity' => $this->quantity,
            'message' => "{$this->product->name} stock is low. Only {$this->quantity} left in stock.",
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'quantity' => $this->quantity,
            'message' => "{$this->product->name} stock is low. Only {$this->quantity} left in stock.",
        ]);
    }
}
