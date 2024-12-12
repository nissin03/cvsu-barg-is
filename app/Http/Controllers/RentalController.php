<?php

namespace App\Http\Controllers;


use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation;
use App\Models\DormitoryRoom;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Facility;



class RentalController extends Controller

{
    public function index()
    {
        // Fetch all facilities, assuming you want to display all
        $facilities = Facility::where('status', true)->orderBy('created_at', 'DESC')->paginate(10);
    
        return view('rental', compact('facilities'));
    }
    
    

    public function show($rental_slug)
    {
       
        $facility = Facility::with('facilityAttributes.prices')->where('slug', $rental_slug)->firstOrFail();
        $individualAttributes = $facility->facilityAttributes->where('price_type', 'individual');
        $wholeAttributes = $facility->facilityAttributes->where('price_type', 'whole');

        $individualPrice = $individualAttributes->isNotEmpty() && $individualAttributes->first()->prices->isNotEmpty()
            ? $individualAttributes->first()->prices->first()->value
            : 0;  
        
        $wholePrice = $wholeAttributes->isNotEmpty() && $wholeAttributes->first()->prices->isNotEmpty()
            ? $wholeAttributes->first()->prices->first()->value
            : 0;  
    
   
        return view('rentals_details', compact('facility', 'individualAttributes', 'wholeAttributes', 'individualPrice', 'wholePrice'));
    }
    

    public function checkout(Request $request, $rental_id)
    {
       
        // Check if user is authenticated
        if (!Auth::check()) {
            \Log::warning('User is not authenticated. Redirecting to login.');
            return redirect()->route("login");
        }


        \Log::info('Checkout Request Data:', $request->all());

        // Retrieve rental details based on rental_id
        $rental = Rental::find($rental_id);
        if (!$rental) {
            \Log::error("Rental not found for rental_id: {$rental_id}");
            return redirect()->back()->with('error', 'Rental not found');
        }

        \Log::info("Rental found: ", ['rental_id' => $rental->id, 'rental_name' => $rental->name]);

        // Capture the necessary inputs
        $internal_quantity = $request->input('internal_quantity', 0);
        $external_quantity = $request->input('external_quantity', 0);
        $total_price = $request->input('total_price', 0);
        $usage_type = $request->input('usage_type', 'individual_group');
        $reservation_type = $request->input('reservation_type', 'shared');
        $total_price_ih2 = $request->input('total_price_ih2', 0);  // Capture total_price_ih2

        \Log::info('Captured Inputs:', [
            'internal_quantity' => $internal_quantity,
            'external_quantity' => $external_quantity,
            'total_price' => $total_price,
            'usage_type' => $usage_type,
            'reservation_type' => $reservation_type,
            'total_price_ih2' => $total_price_ih2
        ]);

        $dormitoryRoom = DormitoryRoom::where('rental_id', $rental->id)->first();

        // Retrieve dormitory room details if applicable
        if (in_array($rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II'])) {
            $dormitoryRoom = DormitoryRoom::where('rental_id', $rental->id)->first();
            \Log::info('Dormitory room found for rental:', ['dormitory_room_id' => $dormitoryRoom->id]);
        }

        // Handle total price calculation based on reservation type
        if (in_array($rental->name, ['International House II'])) {
            // For International House II, solo and shared reservation types are applicable
            if ($reservation_type == 'solo') {
                $total_price_ih2 = $rental->price * $dormitoryRoom->room_capacity;
                \Log::info('Solo reservation type selected for International House II. Total price calculated for solo:', ['total_price' => $total_price]);
            } else if ($reservation_type == 'shared') {
                $total_price = $rental->price;
                \Log::info('Shared reservation type selected for International House II. Total price set for shared:', ['total_price' => $total_price]);
            } else {
                \Log::error("Invalid reservation type for International House II. Returning back.");
                return redirect()->back();
            }
        } else {
            // For Male and Female Dormitories, only shared reservation type is allowed
            if ($reservation_type != 'shared') {
                \Log::error("Invalid reservation type for Male or Female Dormitory. Returning back.");
                return redirect()->back()->with('error', 'Reservation type for Male and Female Dormitories must be shared.');
            }
            $total_price = $rental->price; // Set price for shared reservation
            \Log::info('Shared reservation type selected for Male/Female Dormitory. Total price set for shared:', ['total_price' => $total_price]);
        }

        // Calculate pool_quantity (internal + external)
        $pool_quantity = $internal_quantity + $external_quantity;
        
        \Log::info('Pool quantity calculated:', ['pool_quantity' => $pool_quantity]);

        // Pass the variables to the view
        return view('rentals_checkout', compact(
            "rental",
            "dormitoryRoom",
            "pool_quantity",
            "internal_quantity",
            "external_quantity",
            "total_price",
            "usage_type",
            "total_price_ih2"
        ));
    }



    public function placeReservation(Request $request, $rentalId)
    {
        // Ensure the user is logged in
        if (!Auth::check()) {
            return redirect()->route("login");
        }
    
        $rental = Rental::find($rentalId);
        if (!$rental) {
            return redirect()->back()->with('error', 'Rental not found');
        }
    
        $userSex = Auth::user()->sex;
    
        if ($rental->sex !== 'all' && $rental->sex !== $userSex) {
            Log::warning('Sex mismatch. Rental Sex: ' . $rental->sex . ', User Sex: ' . $userSex);
            return redirect()->back()->withErrors(['sex' => 'You cannot add this facility to the reservation due to sex eligibility (male/female).'])->withInput();
        }
    
        // Define base validation rules
        $rules = [
            'internal_quantity' => 'required|integer|min:0',
            'external_quantity' => 'required|integer|min:0',
            'qualification' => 'required|file|mimes:pdf,doc,docx',
            'total_price' => 'required|numeric|min:0',
            'total_price_ih2' => 'nullable|numeric|min:0',
            'time_slot' => 'required|string',
            'usage_type' => 'required|in:individual_group,exclusive_use', // Validation for usage_type
        ];
    
        // Conditional validation rules based on rental name
        if ($rental->name === 'International House II') {
            $rules['ih_start_date'] = 'required|date|after_or_equal:tomorrow';
            $rules['ih_end_date'] = 'required|date|after:ih_start_date';
        } elseif (in_array($rental->name, ['Male Dormitory', 'Female Dormitory'])) {
            $rules['reservation_date'] = 'nullable'; // Make it optional
        } else {
            $rules['reservation_date'] = 'required|date|after_or_equal:today';
        }
    
        $validatedData = $request->validate($rules);
    
        // Capture quantities and usage_type from the request
        $internal_quantity = $request->input('internal_quantity', 0);
        $external_quantity = $request->input('external_quantity', 0);
        $usage_type = $request->input('usage_type');
    
        // Calculate pool_quantity based on usage_type
        if ($usage_type === 'exclusive_use') {
            // Ensure pool_quantity is exactly equal to the rental capacity for exclusive use
            if ($internal_quantity + $external_quantity !== $rental->capacity) {
                return back()->withErrors(['pool_quantity' => 'For exclusive use, the total quantity must be equal to the rental capacity.'])->withInput();
            }
            $pool_quantity = $rental->capacity; // Set pool_quantity to rental capacity
        } else {
            $pool_quantity = $internal_quantity + $external_quantity;
        }
    
        // Calculate total price for International House II
        if ($rental->name === 'International House II') {
            $startDate = Carbon::parse($validatedData['ih_start_date']);
            $endDate = Carbon::parse($validatedData['ih_end_date']);
            $days = $startDate->diffInDays($endDate);
    
            $calculatedPrice = $days * $validatedData['total_price'];
    
            if ($validatedData['total_price'] != $calculatedPrice) {
                return back()->withErrors(['total_price' => 'Invalid total price calculation.'])->withInput();
            }
    
            $dormitoryRoom = DormitoryRoom::where('rental_id', $rentalId)->first();
            if (!$dormitoryRoom) {
                $dormitoryRoom = DormitoryRoom::create([
                    'rental_id' => $rentalId,
                    'room_number' => 'Default Room',
                    'room_capacity' => 1,
                    'start_date' => $validatedData['ih_start_date'],
                    'end_date' => $validatedData['ih_end_date'],
                    'ih_start_date' => $validatedData['ih_start_date'],
                    'ih_end_date' => $validatedData['ih_end_date'],
                ]);
            } else {
                $dormitoryRoom->update([
                    'ih_start_date' => $validatedData['ih_start_date'],
                    'ih_end_date' => $validatedData['ih_end_date'],
                ]);
            }
        }
    
        // Proceed with saving the reservation
        $reservation = Reservation::create([
            'user_id' => Auth::user()->id,
            'rental_id' => $rentalId,
            'reservation_date' => $validatedData['reservation_date'] ?? null,
            'time_slot' => $validatedData['time_slot'],
            'rent_status' => 'pending',
            'payment_status' => 'pending',
            'total_price' => $validatedData['total_price'],
            'pool_quantity' => $pool_quantity, // Assign computed pool_quantity
            'internal_quantity' => $internal_quantity,
            'external_quantity' => $external_quantity,
            'usage_type' => $usage_type, // Save the usage_type
        ]);
    
        // Handle qualification file upload
        if ($request->hasFile('qualification')) {
            $file = $request->file('qualification');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/rentals/files'), $filename);
    
            $rental->qualification = $filename;
            $rental->save();
        }
    
        // Redirect with success message
        return redirect()->route('rentals.checkout', ['rental_id' => $rental->id])
            ->with('success', 'Reservation successfully placed!');
    }
    


    // // public function checkPoolCapacity($rentalId, $date)
    // // {
    // //     $rental = Rental::find($rentalId);
    // //     if (!$rental || $rental->name !== 'Swimming Pool') {
    // //         return response()->json(['error' => 'Invalid rental or rental type'], 400);
    // //     }

    // //     $currentReservations = Reservation::where('rental_id', $rentalId)
    // //         ->where('reservation_date', $date)
    // //         ->sum('pool_quantity');

    // //     return response()->json([
    // //         'remaining_capacity' => max(0, $rental->capacity - $currentReservations),
    // //     ]);
    // }
    public function checkPoolCapacity($rentalId, $date)
    {
        $rental = Rental::find($rentalId);

        // Validate rental and ensure it's a Swimming Pool
        if (!$rental || $rental->name !== 'Swimming Pool') {
            return response()->json(['error' => 'Invalid rental or rental type'], 400);
        }

        // Check if there are any exclusive_use reservations for the given date
        $exclusiveReservation = Reservation::where('rental_id', $rentalId)
            ->where('reservation_date', $date)
            ->where('usage_type', 'exclusive_use')
            ->exists();

        if ($exclusiveReservation) {
            // If exclusive_use exists, the capacity is fully reserved
            return response()->json(['remaining_capacity' => 0]);
        }

        // Sum up pool quantities for other types of reservations
        $currentReservations = Reservation::where('rental_id', $rentalId)
            ->where('reservation_date', $date)
            ->sum('pool_quantity');

        // Calculate remaining capacity
        $remainingCapacity = max(0, $rental->capacity - $currentReservations);

        return response()->json([
            'remaining_capacity' => $remainingCapacity,
        ]);
    }


    public function getReservations($rentalId)
    {
        $reservations = Reservation::where('rental_id', $rentalId)
            ->where('user_id', Auth::user()->id)
            ->get(['reservation_date', 'rent_status']);

        return response()->json($reservations);
    }
}
