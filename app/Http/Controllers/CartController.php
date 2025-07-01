<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\PreOrder;
use App\Models\OrderItem;
use App\Models\CourseDept;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Events\LowStockEvent;
use Illuminate\Support\Carbon;
use App\Helpers\TimeSlotHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Notifications\LowStockNotification;
use App\Notifications\PreOrderNotification;
use Illuminate\Support\Facades\Notification;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }
    public function add_to_cart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $product = Product::find($request->id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        $userSex = Auth::user()->sex;

        if ($product->sex !== 'all' && $product->sex !== $userSex) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot add this product to the cart due to sex restrictions.'
            ], 403);
        }

        $hasVariants = $product->attributeValues()->exists();
        $variantAttributes = null;
        if ($hasVariants && (!$request->has('variant_id') || empty($request->variant_id))) {
            return redirect()->back()->withErrors(['variant_id' => 'Please select a product variant.'])->withInput();
        }

        if ($hasVariants) {
            $variant = $product->attributeValues->where('id', $request->variant_id)->first();

            if (!$variant) {
                return redirect()->back()->with('error', 'Invalid variant selected.');
            }

            if ($variant->quantity <= 0 || $variant->stock_status === 'outofstock') {
                return redirect()->back()->with('error', 'This variant is out of stock.');
            }

            if ($request->quantity > $variant->quantity) {
                return redirect()->back()->with('error', 'Cannot add more than available stock for this variant.');
            }

            $variantAttributes = $variant->getAttributesArray();
            $attributeValues = array_values($variantAttributes);
            $variantNameSuffix = ' - ' . implode(', ', $attributeValues);

            Cart::instance('cart')->add([
                'id' => $product->id,
                'name' => $product->name . $variantNameSuffix,
                'qty' => $request->quantity,
                'price' => $variant->price,
                'options' => [
                    'product_id' => $product->id,
                    'is_variant' => true,
                    'variant_id' => $variant->id,
                    'variant_quantity' => $variant->quantity,
                    'variant_attributes' => $variantAttributes
                ]
            ])->associate('App\Models\Product');
        } else {
            if ($request->quantity > $product->quantity) {
                return redirect()->back()->with('error', 'Cannot add more than available stock.');
            }

            Cart::instance('cart')->add([
                'id' => $product->id,
                'name' => $product->name,
                'qty' => $request->quantity,
                'price' => $product->price,
                'options' => [
                    'product_id' => $product->id,
                    'is_variant' => false,
                    'variant_attributes' => null
                ]
            ])->associate('App\Models\Product');
        }
        return response()->json(['incomplete_profile' => true, 'message' => 'Your password has been successfully updated. Please complete your profile to proceed.'], 200);
    }

    public function increase_cart_quantity($rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);
        if (!$cartItem) {
            return response()->json(['error' => 'The item does not exist in the cart.']);
        }

        $newQty = $cartItem->qty + 1;
        $maxQty = $cartItem->options['is_variant'] ? $cartItem->options['variant_quantity'] : $cartItem->model->quantity;

        if ($newQty > $maxQty) {
            return response()->json(['error' => 'Cannot increase quantity. Stock is limited.']);
        }

        $product = Product::find($cartItem->options['product_id']);

        if (isset($cartItem->options['variant_id'])) {
            $variant = ProductAttributeValue::find($cartItem->options['variant_id']);
            if ($variant && $cartItem->qty < $variant->quantity) {
                $newQty = $cartItem->qty + 1;
                Cart::instance('cart')->update($rowId, $newQty);
            } else {
                return response()->json(['error' => 'Cannot increase quantity. Variant stock is limited.']);
            }
        } else {
            if ($cartItem->qty < $product->quantity) {
                $newQty = $cartItem->qty + 1;
                Cart::instance('cart')->update($rowId, $newQty);
            } else {
                return response()->json(['error' => 'Cannot increase quantity. Product stock is limited.']);
            }
        }

        return response()->json([
            'success' => true,
            'newQty' => $newQty,
            'subtotal' => Cart::subtotal(),
            'total' => Cart::total(),
            'itemTotal' => number_format($cartItem->price * $newQty, 2)
        ]);
    }

    public function decrease_cart_quantity($rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);
        if (!$cartItem) {
            return response()->json(['error' => 'The item does not exist in the cart.']);
        }

        $newQty = $cartItem->qty - 1;

        if ($newQty < 1) {
            return response()->json(['error' => 'Quantity cannot be less than 1.']);
        }

        Cart::instance('cart')->update($rowId, $newQty);

        return response()->json([
            'success' => true,
            'newQty' => $newQty,
            'subtotal' => Cart::subtotal(),
            'total' => Cart::total(),
            'itemTotal' => number_format($cartItem->price * $newQty, 2)
        ]);
    }


    public function updateVariant(Request $request, $rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);
        if (!$cartItem) {
            return redirect()->back()->with('error', 'The cart does not contain the specified item.');
        }

        $product = Product::with('attributeValues.productAttribute')->find($cartItem->options['product_id']);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        $variantAttributes = [];
        $variantIds = [];
        foreach ($request->input('attribute', []) as $attributeId => $variantId) {
            $attributeValue = $product->attributeValues->where('id', $variantId)->first();
            if ($attributeValue && $attributeValue->productAttribute) {
                $variantAttributes[$attributeValue->productAttribute->name] = $attributeValue->value;
                $variantIds[] = $variantId;
            }
        }

        $matchingVariant = $product->attributeValues()->whereIn('id', $variantIds)->first();
        if ($matchingVariant) {
            Cart::instance('cart')->update($rowId, [
                'id' => $product->id,
                'name' => $product->name . ' - ' . implode(', ', $variantAttributes),
                'price' => $matchingVariant->price,
                'options' => array_merge($cartItem->options->toArray(), [
                    'variant_id' => $matchingVariant->id,
                    'variant_attributes' => $variantAttributes,
                    'is_variant' => true,
                    'variant_quantity' => $matchingVariant->quantity,
                ]),

            ]);
            return redirect()->back()->with('success', 'Variant updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Selected variant is not available.');
        }
    }

    public function remove_item($rowId)
    {
        if (!Cart::instance('cart')->get($rowId)) {
            return redirect()->back()->with('error', 'The item does not exist in the cart.');
        }
        Cart::instance('cart')->remove($rowId);
        return redirect()->back();
    }

    public function empty_cart()
    {
        Cart::instance('cart')->destroy();
        return redirect()->back();
    }

    public function updateQuantity($action, $rowId)
    {
        try {
            $item = Cart::get($rowId);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found'
                ]);
            }

            $currentQty = $item->qty;
            $newQty = $action === 'increase' ? $currentQty + 1 : $currentQty - 1;

            if ($newQty < 1) {
                Cart::remove($rowId);
            } else {
                Cart::update($rowId, $newQty);
            }

            Cart::setGlobalTax(Cart::getGlobalTax());

            return response()->json([
                'success' => true,
                'newQty' => $newQty,
                'subtotal' => Cart::subtotal(),
                'total' => Cart::total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();

        if ($user->utype === 'USR' && $this->isProfileIncomplete($user)) {
            return redirect()->route('user.profile', ['swal' => 1])->with([
                'message' => 'Please complete your profile to proceed with the checkout.'
            ]);
        }
        $hasReservedOrder = Order::where('user_id', $user->id)
            ->where('status', 'reserved')
            ->exists();

        if ($hasReservedOrder) {
            Cart::instance('cart')->destroy();
            return redirect()->route('cart.index')->with('error', 'You already have a reserved order. Please complete or cancel it before placing another.');
        }

        $total = (float) str_replace(',', '', Cart::instance('cart')->total());
        if ($total <= 0) {
            return redirect()->route('cart.index')
                ->with('warning', 'Your cart is empty. Add items before checking out.');
        }
        $timeSlots = TimeSlotHelper::time();

        return view('checkout', compact('user', 'timeSlots'));
    }

    private function validateTimeSlot(Request $request)
    {
        $allowedSlots = TimeSlotHelper::time();
        if (!in_array($request->input('time_slot'), $allowedSlots)) {
            throw new \Exception('Invalid time slot selected.');
        }
    }
    private const MAX_SLOT_COUNT = 50;
    public function place_an_order(Request $request)
    {
        $user = Auth::user();

        $this->setTotalAmount();

        try {
            $this->validateTimeSlot($request);
            $this->preventOverBooking($request);

            $order = new Order();
            $order->user_id = $user->id;
            $order->total = Session::get('checkout')['total'];
            $order->reservation_date = $request->input('reservation_date');
            $order->time_slot = $request->input('time_slot');
            $order->status = 'reserved';
            $order->save();

            foreach (Cart::instance('cart')->content() as $item) {
                $orderItem = new OrderItem();
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->price = $item->price;
                $orderItem->quantity = $item->qty;
                $this->handleStockAndNotification($item, $orderItem);
                $orderItem->variant_id = $item->options->variant_id ?? null;
                $orderItem->save();
            }

            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->amount_paid = 0;
            $transaction->change = 0;
            $transaction->status = "unpaid";
            $transaction->save();

            Cart::instance('cart')->destroy();
            Session::forget('checkout');
            Session::put('order_id', $order->id);

            return redirect()->route('cart.order.confirmation');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to place order. ' . $e->getMessage());
        }
    }

    private function handleStockAndNotification($item, $orderItem): void
    {
        if ($item->options->has('is_variant') && $item->options->has('variant_id')) {
            $variant = ProductAttributeValue::find($item->options->variant_id);

            if (!$variant || $variant->quantity < $item->qty) {
                throw new \Exception('Insufficient variant stock for product: ' . $item->name);
            }

            $variant->quantity -= $item->qty;
            $variant->stock_status = $variant->quantity <= 0 ? 'outofstock' : 'instock';
            $variant->save();

            if ($variant->quantity <= 20) {
                $product = $variant->product;
                $product->load('attributeValues.productAttribute');
                $admins = User::where('utype', 'ADM')->get();
                Notification::send($admins, new LowStockNotification($product, $variant->quantity));
            }

            $orderItem->variant_id = $variant->id;
        } else {
            $product = Product::find($item->id);

            if (!$product || $product->quantity < $item->qty) {
                throw new \Exception('Insufficient stock for product: ' . $item->name);
            }

            $product->quantity -= $item->qty;
            $product->stock_status = $product->quantity <= 0 ? 'outofstock' : 'instock';
            $product->save();

            if ($product->quantity <= 20) {
                $product->load('attributeValues.productAttribute');
                $admins = User::where('utype', 'ADM')->get();
                Notification::send($admins, new LowStockNotification($product, $product->quantity));
            }

            $orderItem->variant_id = null;
        }
    }

    private function isProfileIncomplete($user)
    {
        if ($user->role === 'student') {
            return !$user->name || !$user->email || !$user->phone_number || !$user->year_level || !$user->department || !$user->course;
        } else {
            return !$user->name || !$user->email || !$user->phone_number;
        }
    }

    public function setTotalAmount()
    {
        if (Cart::instance('cart')->content()->count() > 0) {
            $subtotal = (float) str_replace(',', '', Cart::instance('cart')->subtotal());
            $total = (float) str_replace(',', '', Cart::instance('cart')->total());

            Session::put('checkout', [
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
            ]);
        } else {
            Session::forget('checkout');
        }
    }

    private function preventOverBooking(Request $request)
    {
        $selectedDate = $request->input('reservation_date');
        $selectedTime = $request->input('time_slot');

        $currentCount = Order::where('reservation_date', $selectedDate)
            ->where('time_slot', $selectedTime)
            ->where('status', 'reserved')
            ->count();

        if ($currentCount >= self::MAX_SLOT_COUNT) {
            throw new \Exception('Selected time slot is fully booked.');
        }
    }


    public function order_confirmation()
    {
        if (Session::has('order_id')) {
            $order = Order::find(Session::get('order_id'));
            return view('order-confirmation', compact('order'));
        }
        return redirect()->route('cart.index');
    }

    public function getAvailableTimeSlots(Request $request)
    {
        $date = $request->query('date');
        $timeSlots = TimeSlotHelper::time();
        $slotCounts = [];
        foreach ($timeSlots as $slot) {
            $count = Order::where('reservation_date', $date)
                ->where('time_slot', $slot)
                ->where('status', 'reserved')
                ->count();

            $slotCounts[$slot] = max(self::MAX_SLOT_COUNT - $count, 0);
        }
        return response()->json($slotCounts);
    }
}
