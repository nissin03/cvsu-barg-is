<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index(Request $request, $order = null)
    {
        $search = $request->input('order-user-search');

        $orders = Order::with(['transaction'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->get();

        $selectedOrder = null;
        $orderItems = collect();

        if ($order) {
            $selectedOrder = Order::with(['orderItems.product.category', 'transaction'])->find($order);
            if ($selectedOrder) {
                $orderItems = $selectedOrder->orderItems;
            }
        }

        return view('admin.pos.pos-index', compact('orders', 'selectedOrder', 'orderItems', 'search'));
    }
}
