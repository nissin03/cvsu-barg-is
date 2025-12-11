<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Event;
use App\Models\Order;
use App\Models\Course;
use App\Models\College;
use App\Models\Position;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ProfileHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\Storage;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function index()
    {
        return view('user.index');
    }

    public function about()
    {
        return view('about.index');
    }

    public function orders()
    {
        $orders = Order::where('user_id', Auth::user()->id)
            ->whereNotIn('status', ['canceled', 'pickedup'])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('user.orders', compact('orders'));
    }


    // public function canceled_order()
    // {
    //     $canceledOrders = Auth::user()->orders()
    //         ->where('status', 'canceled')
    //         ->whereHas('transaction', function ($query) {
    //             $query->where('status', 'unpaid');
    //         })
    //         ->latest()
    //         ->paginate(10);

    //     return view('user.canceled_order', compact('canceledOrders'));
    // }

    public function order_details($order_id)
    {
        // $order = Order::where('user_id', Auth::user()->id)->where('id', $order_id)->first();
        $order = Order::where('user_id', Auth::user()->id)
            ->where('id', $order_id)
            ->with(['user.college', 'user.course', 'orderItems.product', 'orderItems.variant', 'transaction'])
            ->first();
        if ($order) {
            $orderItems = OrderItem::where('order_id', $order->id)->orderBy('id')->paginate(12);
            $transaction = Transaction::where('order_id', $order->id)->first();
            return view('user.order_details', compact('order', 'orderItems', 'transaction'));
        } else {
            return redirect()->route('login');
        }
    }

    public function order_history()
    {
        $userId = Auth::user()->id;

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $orders = Order::where('user_id', $userId)
            ->whereIn('status', ['canceled', 'pickedup'])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.order_history', compact('orders'));
    }

    public function show_profile()
    {
        $user = Auth::user()->load('college', 'course');
        return view('user.profile', compact('user'));
    }

    public function profile_edit($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('user.profile')->with('error', 'User not found.');
        }

        $colleges = College::all();
        $courses  = Course::all();
        $positions = Position::all();

        return view('user.profile-edit', compact('user', 'colleges', 'courses', 'positions'));
    }


    public function profile_update(Request $request)
    {
        $user = Auth::user();
        $role = $request->input('role', $user->role);

        $validationRules = [
            'phone_number' => 'required|numeric|digits:10',
            'sex'          => 'required|in:male,female',
        ];

        if ($user->role_change_allowed) {
            $validationRules['role'] = 'required|string|in:student,employee,non-employee';
        }

        if ($role === 'student') {
            $validationRules['year_level'] = 'required|string|in:1st Year,2nd Year,3rd Year,4th Year,5th Year';
            $validationRules['college_id'] = 'required|exists:colleges,id';
            $validationRules['course_id']  = 'required|exists:courses,id';
        } elseif ($role === 'employee') {
            $validationRules['position_id'] = 'required|exists:positions,id';
        }

        $validatedData = $request->validate($validationRules);

        if ($user->role !== $role && $user->role_change_allowed) {
            $user->role = $role;
            $user->role_change_allowed = false;
        }

        $user->phone_number = $validatedData['phone_number'];
        $user->sex          = $validatedData['sex'];

        if ($role === 'student') {
            $user->year_level = $validatedData['year_level'];
            $user->college_id = $validatedData['college_id'];
            $user->course_id  = $validatedData['course_id'];
            $user->position_id = null; // clear if previously employee
        } elseif ($role === 'employee') {
            $user->position_id = $validatedData['position_id'];
            $user->year_level  = null;
            $user->college_id  = null;
            $user->course_id   = null;
        } else {
            // non-employee
            $user->year_level  = null;
            $user->college_id  = null;
            $user->course_id   = null;
            $user->position_id = null;
        }

        $user->save();

        if (session()->has('url.intended')) {
            $intendedUrl = session()->pull('url.intended');
            return redirect()->to($intendedUrl)
                ->with('profile_completed', 'Your profile has been successfully updated!');
        }

        if (Cart::instance('cart')->count() > 0) {
            return redirect()->route('cart.checkout')->with('success', 'Profile updated successfully.');
        }

        return redirect()->route('shop.index')->with('success', 'Profile updated successfully.');
    }





    public function profile_image_edit()
    {
        $user = Auth::user();
        return view('user.profile-edit-image', compact('user'));
    }


    public function profile_image_update(Request $request)
    {
        try {
            $request->validate([
                'profile_image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            ]);

            $user = Auth::user();

            if ($request->hasFile('profile_image')) {
                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                $imagePath = $request->file('profile_image')->store('profile_images', 'public');

                $user->profile_image = $imagePath;
                $user->save();

                return redirect()->route('user.profile')
                    ->with('success', 'Profile image updated successfully.');
            }

            return redirect()->route('user.profile')
                ->with('error', 'Please select a valid image file.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Validation failed. Please check your input.');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating your profile image. Please try again.');
        }
    }

    public function profile_image_delete()
    {
        try {
            $user = Auth::user();

            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);

                $user->profile_image = null;
                $user->save();

                return redirect()->route('user.profile')
                    ->with('success', 'Profile image deleted successfully.');
            }

            return redirect()->route('user.profile')
                ->with('error', 'No profile image found to delete.');
        } catch (Exception $e) {
            return redirect()->route('user.profile')
                ->with('error', 'An error occurred while deleting your profile image. Please try again.');
        }
    }

    public function getCollegeCourses(College $college)
    {
        $courses = $college->courses->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->name
            ];
        });

        return response()->json($courses);
    }
    public function rebook_canceled_order($orderId)
    {
        $user = Auth::user();

        if ($user->utype === 'USR' && ProfileHelper::isProfileIncomplete($user)) {
            return redirect()->route('user.profile', ['swal' => 1])->with([
                'message' => 'Please complete your profile to proceed with the checkout.'
            ]);
        }

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'canceled')
            ->whereHas('transaction', function ($query) {
                $query->where('status', 'unpaid');
            })
            ->with(['orderItems.product.attributeValues.productAttribute', 'orderItems.variant', 'transaction'])
            ->first();

        if (!$order) {
            return redirect()->route('user.canceled-orders')
                ->with('error', 'Order not found or not available for re-booking.');
        }

        // Check if order was auto-canceled (has canceled_reason and updated_by is null)
        if (empty($order->canceled_reason) || !is_null($order->updated_by)) {
            return redirect()->route('user.order.details', $orderId)
                ->with('error', 'This order cannot be re-booked as it was manually canceled.');
        }

        // Check if within 24 hours of CANCELLATION (not creation)
        if (!$order->canceled_date) {
            return redirect()->route('user.canceled-orders')
                ->with('error', 'This order cannot be re-booked.');
        }

        $hoursSinceCanceled = Carbon::parse($order->canceled_date)->diffInHours(now());
        if ($hoursSinceCanceled > 24) {
            return redirect()->route('user.order.details', $orderId)
                ->with('error', 'The re-booking period has expired. This order was canceled more than 24 hours ago.');
        }

        try {
            Cart::instance('cart')->destroy();
            $addedItems = 0;
            $skippedItems = [];

            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;

                // Validate product exists and is in stock
                if (!$product) {
                    $skippedItems[] = "Product #{$orderItem->product_id} no longer exists";
                    continue;
                }

                // Check if product has variants
                if ($orderItem->variant_id) {
                    $variant = ProductAttributeValue::find($orderItem->variant_id);

                    if (!$variant || $variant->quantity < $orderItem->quantity || $variant->stock_status === 'outofstock') {
                        $skippedItems[] = "{$product->name} - variant out of stock";
                        continue;
                    }

                    // Get variant attributes
                    $variantAttributes = $variant->getAttributesArray();
                    $attributeValues = array_values($variantAttributes);
                    $variantNameSuffix = ' - ' . implode(', ', $attributeValues);

                    Cart::instance('cart')->add([
                        'id' => $product->id,
                        'name' => $product->name . $variantNameSuffix,
                        'qty' => $orderItem->quantity,
                        'price' => $variant->price,
                        'options' => [
                            'product_id' => $product->id,
                            'is_variant' => true,
                            'variant_id' => $variant->id,
                            'variant_quantity' => $variant->quantity,
                            'variant_attributes' => $variantAttributes
                        ]
                    ])->associate('App\Models\Product');

                    $addedItems++;
                } else {
                    // Product without variant
                    if ($product->quantity < $orderItem->quantity || $product->stock_status === 'outofstock') {
                        $skippedItems[] = "{$product->name} - out of stock";
                        continue;
                    }

                    Cart::instance('cart')->add([
                        'id' => $product->id,
                        'name' => $product->name,
                        'qty' => $orderItem->quantity,
                        'price' => $product->price,
                        'options' => [
                            'product_id' => $product->id,
                            'is_variant' => false,
                            'variant_attributes' => null
                        ]
                    ])->associate('App\Models\Product');

                    $addedItems++;
                }
            }

            if ($addedItems === 0) {
                return redirect()->route('cart.index')
                    ->with('error', 'None of the items from this order are currently available.');
            }

            $message = "Successfully added {$addedItems} item(s) to your cart.";
            if (count($skippedItems) > 0) {
                $message .= " Some items were unavailable: " . implode(', ', $skippedItems);
            }

            return redirect()->route('cart.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Rebook order failed', [
                'order_id' => $orderId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('user.canceled-orders')
                ->with('error', 'Failed to re-book order. Please try again.');
        }
    }
}
