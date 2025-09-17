<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderCanceledNotification extends Notification implements ShouldQueue
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
                  ->subject('Order Canceled')
                  ->line('Your order #' . $this->order->id . ' has been canceled.')
                  ->line('If you have any questions, please contact our support team.')
                  ->action('View Order Details', route('user.order.details', $this->order->id))
                  ->line('We apologize for any inconvenience.');
      }

      /**
       * Get the array representation of the notification.
       */
      public function toArray($notifiable): array
      {
            return [
                  'order_id' => $this->order->id,
                  'title' => 'Order Canceled',
                  'body' => 'Your order #' . $this->order->id . ' has been canceled.',
                  'url' => route('user.order.details', $this->order->id),
                  'icon' => 'fas fa-times-circle',
                  'meta' => [
                        'total_amount' => $this->order->total_amount,
                        'status' => $this->order->status,
                        'canceled_at' => $this->order->updated_at->toISOString(),
                  ]
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
