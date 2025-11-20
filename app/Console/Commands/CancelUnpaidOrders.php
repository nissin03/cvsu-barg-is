<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Notifications\OrderCanceledNotification;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-unpaid-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel unpaid orders after a given timeframe';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $expirationTime = Carbon::now()->subHours(24);
        $expirationDate = Carbon::now()->subHours(24)->startOfDay();
        // $expirationTime = Carbon::now()->subSeconds(10);
        $orders = Order::where('status', 'reserved')
            ->whereDate('reservation_date', '<', $expirationDate)
            ->whereHas(
                'transaction',
                fn($query) =>
                $query->where('status', 'unpaid')
            )
            ->get();

        $canceledCount = 0;

        foreach ($orders as $order) {
            try {
                DB::transaction(function () use ($order) {
                    foreach ($order->orderItems as $item) {
                        if ($item->variant_id) {
                            $variant = ProductAttributeValue::find($item->variant_id);
                            if ($variant) {
                                $variant->quantity += $item->quantity;
                                $variant->stock_status = 'instock';
                                $variant->save();
                            }
                        } else {
                            $product = Product::find($item->product_id);
                            if ($product) {
                                $product->quantity += $item->quantity;
                                $product->stock_status = 'instock';
                                $product->save();
                            }
                        }
                    }
                    $order->update([
                        'status' => 'canceled',
                        'canceled_date' => now(),
                        'canceled_reason' => 'Your reservation was not claimed or paid within 24 hours after the reservation date.',
                        'updated_by' => null,
                    ]);
                    // notify user
                    if ($order->user) {
                        $order->user->notify(new OrderCanceledNotification($order));
                    }
                });

                $this->info("Canceled Order #{$order->id}");
                $canceledCount++;
            } catch (\Exception $e) {
                $this->error("Failed to cancel Order #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Auto-cancel completed. {$canceledCount} orders canceled.");
        return Command::SUCCESS;
    }
}
