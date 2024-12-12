<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Facility;
use App\Models\Availability;
use Illuminate\Http\Request;
use App\Models\FacilityAttribute;
use Illuminate\Support\Facades\Auth;

class UserFacilityController extends Controller
{

    public function index()
    {
        try {
            $facilities = Facility::with(['prices', 'facilityAttributes'])
                ->where('archived', 0)
                ->get();

            return view('user.facilities.index', compact('facilities'));
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => 'Failed to fetch facilities.']);
        }
    }

    public function show($slug)
    {
        $facility = Facility::with('facilityAttributes', 'prices')->where('slug', $slug)->firstOrFail();
        if (!$facility) {
            return redirect()->back()->with('error', 'Facility not found.');
        }
        $individualPrice = $facility->individualPrice();

        return view('user.facilities.details', compact('facility', 'individualPrice'));
    }

    // public function updateTotalPrice(Request $request)
    // {
  
    //     $priceId = $request->input('price_id');
    //     $totalPrice = $request->input('total_price');

 
    //     $price = Price::find($priceId);

    //     if (!$price) {
    //         return response()->json(['error' => 'Invalid price selection'], 400);
    //     }

    //     return response()->json([
    //         'total_price' => $totalPrice
    //     ]);
    // }

    public function reserve(Request $request)
    {
        // dd($request->all());

        $facility = Facility::find($request->facility_id);
        if (!$facility) {
            return redirect()->back()->with('error', 'Facility not found.');
        }
    
        $facilityAttribute = $facility->facilityAttributes()->first();

        $individualPrice = $facility->individualPrice();
        session()->put('reservation_data', [
            'facility_attributes_name' => $facilityAttribute->room_name,
            'facility_name' => $facility->name,
            'total_price' => $individualPrice,
        ]); 
        
        return redirect()->route('facility.checkout');
        
    }
    

    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        $reservationData = session('reservation_data');
        
        if (!$reservationData) {
            return redirect()->route('user.facilities.index')->with('error', 'No reservation data found.');
        }
        $facility = Facility::where('slug', $reservationData['facility_name'])->first();
    

        return view('user.facilities.checkout', compact('user', 'reservationData', 'facility'));
    }

    


    // public function post_checkout(Request $request)
    // {
    //     // Validate the incoming data
    //     $validatedData = $request->validate([
    //         'usage_type' => 'required|string',
    //         'total_price' => 'required|numeric',
    //         'individual_value' => 'nullable|array',
    //         'individual_value.*' => 'nullable|numeric',
    //         'whole_price_value' => 'nullable|numeric',
    //     ]);

    //     // Store the values or process the logic
    //     $usageType = $validatedData['usage_type'];
    //     $totalPrice = $validatedData['total_price'];
    //     $individualValues = $validatedData['individual_value'] ?? [];
    //     $wholePriceValue = $validatedData['whole_price_value'] ?? null;

    //     // Example: Store the data in the database or process it as needed
    //     $order = new Order(); // Assuming you have an `Order` model
    //     $order->usage_type = $usageType;
    //     $order->total_price = $totalPrice;
    //     $order->whole_price_value = $wholePriceValue;
    //     $order->save();

    //     // Process individual values (if any)
    //     if (!empty($individualValues)) {
    //         foreach ($individualValues as $priceId => $quantity) {
    //             $order->individualPrices()->attach($priceId, ['quantity' => $quantity]);
    //         }
    //     }

    //     // Return a response (e.g., success message)
    //     return redirect()->route('order.success'); // Example success redirect
    // }





}
