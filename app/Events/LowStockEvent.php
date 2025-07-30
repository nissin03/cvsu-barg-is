<?php

namespace App\Events;

use Log;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;


class LowStockEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $product;
    public $quantity;

    public function __construct($product, $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
        $this->notifyAdmins();
    }


    protected function notifyAdmins(): void
    {
        try {
            $admins = User::where('utype', 'ADM')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new LowStockNotification($this->product));
                Log::info('Admin notification sent for product ID: ' . $this->product->id);
            }
        } catch (\Exception $e) {
            Log::error('Error sending admin notification: ' . $e->getMessage());
        }
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-notification'),
        ];
    }

    public function broadcastWith()
    {
        $variants = [];

        if ($this->product->relationLoaded('attributeValues') || $this->product->attributeValues) {
            $attributeValues = $this->product->attributeValues()->with('productAttribute')->get();

            foreach ($attributeValues as $attributeValue) {
                if ($attributeValue->productAttribute) {
                    $variants[] = $attributeValue->productAttribute->name . ': ' . $attributeValue->value;
                }
            }
        }

        return [
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'variant' => count($variants) ? implode(', ', $variants) : "Doesn't have any variant",
                'quantity' => $this->quantity ?? 'N/A',
            ]
        ];
    }
}
