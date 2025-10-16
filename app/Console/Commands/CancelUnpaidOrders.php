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
        $expirationTime = Carbon::now()->subHours(24);
        // $expirationTime = Carbon::now()->subSeconds(10);
        $orders = Order::where('status', 'reserved')
            ->where('created_at', '<', $expirationTime)
            ->whereHas('transaction', function ($query) {
                $query->where('status', 'unpaid');
            })
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

                    $order->status = 'canceled';
                    $order->canceled_date = Carbon::now();
                    $order->canceled_reason = 'Your order was automatically canceled because it was not paid within 24 hours.';
                    $order->updated_by = null;
                    $order->save();

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

        if ($canceledCount > 0) {
            $this->info("Successfully canceled {$canceledCount} unpaid orders.");
        } else {
            $this->info("No unpaid orders found to cancel.");
        }

        return Command::SUCCESS;
    }
}
