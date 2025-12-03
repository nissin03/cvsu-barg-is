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
    public function __construct($product, $quantity)
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

    protected function getVariant()
    {
        $variants = [];

        $attributeValues =  $this->product->attributeValues()->with('productAttribute')->get();

        foreach ($attributeValues as $attributeValue) {
            if ($attributeValue->productAttribute) {
                $variants[] = $attributeValue->productAttribute->name . ': ' . $attributeValue->value;
            }
        }

        return count($variants) ? implode(', ', $variants) : "Doesn't have any variant";
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
            'title' => 'Low Stock Alert',
            'body' => "{$this->product->name} stock is low. Only {$this->quantity} left in stock.",
            'url' => route('admin.product.edit', $this->product->id),
            'icon' => 'fas fa-exclamation-triangle',
            'name' => $this->product->name, // Keep for backward compatibility
            'variant' => $this->getVariant(),
            'quantity' => $this->quantity,
            'message' => "{$this->product->name} stock is low. Only {$this->quantity} left in stock.", // Keep for backward compatibility
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
            'variant' => $this->getVariant(),
            'quantity' => $this->quantity,
            'message' => "{$this->product->name} stock is low. Only {$this->quantity} left in stock.",
        ]);
    }
}
