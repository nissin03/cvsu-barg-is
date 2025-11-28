<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;


class StockUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    protected $product;
    protected $message;

    public function __construct(Product $product, string $message)
    {
        $this->product = $product;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }
    public function toArray($notifiable): array
    {
        return [
            'product_id'   => $this->product->id,
            'title'        => 'Stock Update',
            'body'         => "Good news! {$this->message}",
            'url'          => route('shop.product.details', $this->product->slug ?? $this->product->id),
            'icon'         => 'fas fa-box',
            'meta'         => [
                'product_name' => $this->product->name,
                'updated_at'   => now()->toISOString(),
            ],
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Stock Available: ' . $this->product->name)
            ->line('We are excited to inform you that new stock is available for the product: ' . $this->product->name . '.')
            ->action('View Product', url('/admin/product/edit/{id}' . $this->product->id))
            ->line('Thank you for shopping with us!');
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id'         => $this->id ?? uniqid(),
            'data'       => $this->toArray($notifiable),
            'created_at' => now()->toISOString(),
        ]);
    }
}
