<?php

namespace App\Events;


use Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
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
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-notification'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'product' => [
                'name' => $this->product->name,
                'quantity' => $this->quantity ?? 'N/A',
            ]
        ];

    }

}
