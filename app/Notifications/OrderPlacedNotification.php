<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reservation Confirmed')
            ->line("Your reservation #{$this->order->id} is confirmed.")
            ->line('Total Amount: ₱' . number_format($this->order->total_amount, 2))
            ->line('Please proceed to the cashier on your reserved date and timeslot to pick up your order. Kindly prepare the exact amount.')
            ->action('View Reservation Details', route('user.order.details', $this->order->id))
            ->line('Thank you for choosing us!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'title' => 'Order Placed Successfully!',
            'body' => "Your reservation #{$this->order->id} is confirmed. Please proceed to the cashier on your reserved date and timeslot.",
            'url' => route('user.order.details', $this->order->id),
            'icon' => 'fas fa-check-circle',
            'order_number' => $this->order->id,
            'message' => "Reservation #{$this->order->id} confirmed. See you on your scheduled date!",
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id ?? uniqid(),
            'data' => $this->toArray($notifiable),
            'created_at' => now()->toISOString(),
        ]);
    }
}
