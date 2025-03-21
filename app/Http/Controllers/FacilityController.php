<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacilityUpdateRequest;
use App\Models\Price;
use App\Models\Facility;
use App\Models\Availability;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Models\FacilityAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Requests\FacilityRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Laravel\Facades\Image;

class FacilityController extends Controller
{

    // public function index(Request $request)
    // {
    //     $archived = $request->query('archived', 0);

    //     $facilities = Facility::with('facilityAttributes', 'prices','user', 'rental', 'dormitoryRoom')
    //         ->when($request->search, function($query) use ($request) {

    //             return $query->whereHas('user', function ($q) use ($request) {
    //                 $q->where('name', 'like', '%' . $request->search . '%')
    //                 ->orWhere('email', 'like', '%' . $request->search . '%');
    //             });
    //         })
    //         ->where('archived', $archived)
    //         ->orderBy('created_at', 'DESC')
    //         ->paginate(5);
    //     return view('admin.facilities.index', compact('facilities', 'archived'));
    // }


    public function index(Request $request)
    {
        $archived = $request->query('archived', 0);

        $facilities = Facility::with('facilityAttributes', 'prices')
            ->where('archived', $archived)
            ->orderBy('created_at', 'DESC')
            ->paginate(5);
        return view('admin.facilities.index', compact('facilities', 'archived'));
    }


    public function create()
    {
        $rooms = FacilityAttribute::all();
        $prices = Price::all();
        return view('admin.facilities.create', compact('rooms', 'prices'));
    }
    public function reservations()
    {
        $availabilities = Availability::all();
        return view('admin.facilities.reservations', compact('availabilities'));
    }
    public function events($availability_id)
    {
        $availability = Availability::findorFail($availability_id);
        return view('admin.facilities.reservations-events', compact('availability'));
    }
    public function reservationHistory($availability_id)
    {
        $availability = Availability::findorFail($availability_id);
        return view('admin.facilities.reservations-history', compact('availability'));
    }

    public function store(FacilityRequest $request)
    {
        $facility = new Facility();
        $this->save($facility, $request);
        $this->handleImage($facility, $request);
        $facility->save();

        $this->handleFacilityAttributes($facility, $request);
        $this->handlePrices($facility, $request);

        // dd($request->all());
        return response()->json(['message' => 'Facility created successfully!', 'action' => 'create']);
    }


    private function handleFacilityAttributes(Facility $facility, $request)
    {
        if ($request->facility_type === 'whole_place') {
            FacilityAttribute::create([
                'facility_id' => $facility->id,
                'room_name' => null,
                'capacity' => null,
                'whole_capacity' => $request->whole_capacity,
                'sex_restriction' => null,
            ]);
        } elseif ($request->facility_type === 'individual') {
            $this->createIndividualAttributes($facility, $request);
        } elseif ($request->facility_type === 'both') {
            $this->createBothTypeAttributes($facility, $request);
        }
    }

    private function createIndividualAttributes(Facility $facility, $request)
    {
        $facilityAttributes = $request->input('facility_attributes', []);
        if (!empty($facilityAttributes)) {
            foreach ($facilityAttributes as $attribute) {
                FacilityAttribute::create([
                    'facility_id' => $facility->id,
                    'room_name' => $attribute['room_name'] ?? null,
                    'capacity' => $attribute['capacity'] ?? null,
                    'whole_capacity' => null,
                    'sex_restriction' => $attribute['sex_restriction'] ?? null,
                ]);
            }
        }
    }

    private function createBothTypeAttributes(Facility $facility, $request)
    {
        $facilityAttributes = $request->input('facility_attributes', []);

        if (!empty($facilityAttributes)) {
            foreach ($facilityAttributes as $attribute) {
                FacilityAttribute::create([
                    'facility_id' => $facility->id,
                    'room_name' => $attribute['room_name'] ?? null,
                    'capacity' => $attribute['capacity'] ?? null,
                    'whole_capacity' => $request->whole_capacity ?? null,
                    'sex_restriction' => $attribute['sex_restriction'] ?? null,
                ]);
            }
        } else {
            FacilityAttribute::create([
                'facility_id' => $facility->id,
                'room_name' => null,
                'capacity' => null,
                'whole_capacity' => $request->whole_capacity,
                'sex_restriction' => null,
            ]);
        }
    }

    private function handlePrices(Facility $facility, $request)
    {
        $priceType = $request->input('price_type', 'individual');

        if (is_array($request->prices)) {
            $pricesData = [];
            foreach ($request->prices as $price) {
                $pricesData[] = [
                    'facility_id' => $facility->id,
                    'name' => $price['name'],
                    'value' => $price['value'],
                    // 'price_type' => $price['price_type'],
                    'price_type' => $priceType, 
                    'is_based_on_days' => filter_var($price['is_based_on_days'], FILTER_VALIDATE_BOOLEAN),
                    'is_there_a_quantity' => $price['is_there_a_quantity'] ?? false,
                    'date_from' => isset($price['is_based_on_days']) && $price['is_based_on_days'] ? $price['date_from'] : null,
                    'date_to' => isset($price['is_based_on_days']) && $price['is_based_on_days'] ? $price['date_to'] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Price::insert($pricesData);
        }
    }
    public function edit($id)
    {
        $facility =  Facility::find($id);
        $facilityAttributes = FacilityAttribute::where('facility_id', $facility->id)->first();
        // dd($facilityAttributes);
        $prices = Price::where('facility_id', $facility->id)->get();
        // dd($prices);
        return view('admin.facilities.edit', compact('facility',  'facilityAttributes', 'prices'));
    }

    public function update(FacilityUpdateRequest $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:facilities,name,' . $id,
        ]);
        


        $facility = Facility::findOrFail($id);
        $request->merge([
            'sex_restriction' => $request->sex_restriction ?? '',
            'name' => $request->name ?: $facility->name,
        ]);
    
        $this->save($facility, $request);
        $this->handleImage($facility, $request);
        $facility->save();
    
        // Get facility attributes from request
        $facilityAttributes = $request->input('facility_attributes', []);
    
        if ($request->facility_type === 'whole_place') {
            // Delete attributes only if facility_type has changed to "whole_place"
            FacilityAttribute::where('facility_id', $facility->id)->delete();
    
            FacilityAttribute::create([
                'facility_id' => $facility->id,
                'room_name' => null,
                'capacity' => null,
                'whole_capacity' => $request->whole_capacity,
                'sex_restriction' => null,
            ]);
        } elseif ($request->facility_type === 'individual' || $request->facility_type === 'both') {
            // Only delete existing attributes if new ones are being provided
            if (!empty($facilityAttributes)) {
                FacilityAttribute::where('facility_id', $facility->id)->delete();
    
                $validAttributes = array_filter($facilityAttributes, function ($attr) {
                    return isset($attr['room_name']) && isset($attr['capacity']);
                });
    
                $this->createFacilityAttributes($facility, $validAttributes);
            }
        }
    
        if (is_array($request->prices)) {
            $pricesData = [];
            foreach ($request->prices as $price) {
                $pricesData[] = [
                    'facility_id' => $facility->id,
                    'name' => $price['name'],
                    'value' => $price['value'],
                    'price_type' => $price['price_type'],
                    'is_based_on_days' => filter_var($price['is_based_on_days'], FILTER_VALIDATE_BOOLEAN),
                    // 'is_there_a_quantity' => filter_var($price['is_there_a_quantity'], FILTER_VALIDATE_BOOLEAN),
                    'is_there_a_quantity' => $price['is_there_a_quantity'] ?? false,
                    'date_from' => isset($price['is_based_on_days']) && $price['is_based_on_days'] ? $price['date_from'] : null,
                    'date_to' => isset($price['is_based_on_days']) && $price['is_based_on_days'] ? $price['date_to'] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
    
            Price::where('facility_id', $facility->id)->delete();
            Price::insert($pricesData);
        }
    
        return response()->json(['message' => 'Facility updated successfully!', 'action' => 'update']);
    }
    

    private function createFacilityAttributes($facility, $facilityAttributes)
    {
        foreach ($facilityAttributes as $attribute) {
            if (isset($attribute['room_name']) && isset($attribute['capacity'])) {
                // Explicitly handle sex_restriction
                $sexRestriction = isset($attribute['sex_restriction']) &&
                    in_array($attribute['sex_restriction'], ['male', 'female'])
                    ? $attribute['sex_restriction']
                    : null;

                if (isset($attribute['room_name'], $attribute['capacity'])) {
                    FacilityAttribute::create([
                        'facility_id' => $facility->id,
                        'room_name' => $attribute['room_name'],
                        'capacity' => $attribute['capacity'],
                        'sex_restriction' => $sexRestriction,
                    ]);
                }
            }
        }
    }

    private function save(Facility $facility, Request $request)
    {
        $facility->name = $request->name;
        $facility->facility_type = $request->facility_type;
        $facility->description = $request->description;
        $facility->created_by = Auth::id();
        $facility->slug = Str::slug($request->name);
        $facility->status = $request->status ?? 1;
        $facility->featured = $request->featured ?? 0;
        $facility->rules_and_regulations = $request->rules_and_regulations;
    }

    private function handleImage(Facility $facility, Request $request)
    {
        $current_timestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateFacilityThumbnailsImage($image, $imageName);
            $facility->image = 'facilities/' . $imageName;
        }
        $gallery_arr = [];
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            $allowedFileExtension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');

            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedFileExtension);

                if ($gcheck) {
                    $gFileName = $current_timestamp . "." . $counter . '.' . $gextension;
                    $this->GenerateFacilityThumbnailsImage($file, $gFileName);
                    array_push($gallery_arr, 'facilities/' . $gFileName);
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
            $facility->images = $gallery_images;
        }

        if ($request->hasFile('requirements')) {
            $requirementsFile = $request->file('requirements');
            $requirementsFileName = $current_timestamp . '-requirements.' . $requirementsFile->getClientOriginalExtension();
            if (Facility::where('requirements', $requirementsFileName)->exists()) {
                return redirect()->back()->withErrors(['requirements' => 'The Requirements file name already exists. Please rename the file.'])->withInput();
            }
            $destinationPath = storage_path('app/public/facilities/');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            $requirementsFile->move($destinationPath, $requirementsFileName);
            $facility->requirements = $requirementsFileName;
        }
        $facility->save();
    }

    // archive codes  
    public function archivedFacilities($id)
    {
        try {
            $facility = Facility::findOrFail($id);
            $facility->archived = 1;
            $facility->archived_at = \Carbon\Carbon::now();
            $facility->save();

            // Return a JSON response for AJAX
            return response()->json(['success' => true, 'message' => 'Facility archived successfully!', 'facility_id' => $id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to archive the facility.']);
        }
    }

    public function restoreFacilities(Request $request)
    {
        $facilityIds = $request->input('ids');
        $facilities = Facility::whereIn('id', $facilityIds)
            ->update(['archived' => 0, 'archived_at' => null]);

        return response()->json([
            'status' => 'Facilities restored successfully',
            'restoredIds' => $facilityIds
        ]);
    }



    public function showFacilities()
    {
        // Fetch only facilities that are archived
        $archivedFacilities = Facility::where('archived', 1)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);


        $facilities = Facility::all();

        // Pass both variables to the view
        return view('admin.facilities.archive.index', compact('archivedFacilities', 'facilities'));
    }



    public function GenerateFacilityThumbnailsImage($image, $imageName)
    {

        try {
            $destinationPathThumbnail = storage_path('app/public/facilities/thumbnails');
            $destinationPath = storage_path('app/public/facilities');

            File::makeDirectory($destinationPathThumbnail, 0755, true, true);
            File::makeDirectory($destinationPath, 0755, true, true);

            $img = Image::read($image->getRealPath());
            $img->cover(689, 689, "center");
            $img->resize(689, 689, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $imageName);

            $img->resize(204, 204, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPathThumbnail . '/' . $imageName);

            Log::info('Saving image to: ' . $destinationPath . '/' . $imageName);
        } catch (\Exception $e) {
            Log::error('Image processing failed: ' . $e->getMessage());
        }
    }

 
    public function updateStatus(Request $request, $id)
    {
        // Fetch the availability record
        $availability = Availability::findOrFail($id);

        // Capture the old values for history
        $oldPaymentStatus = $availability->payment_status;
        $oldStatus = $availability->status;

        // Update the payment and rent status
        $availability->update([
            'payment_status' => $request->payment_status,
            'status' => $request->rent_status,
        ]);

        // Record the history of changes in the ReservationHistory table
        Facility::create([
            'availability_id' => $availability->id,
            'old_payment_status' => $oldPaymentStatus,
            'new_payment_status' => $request->payment_status,
            'old_rent_status' => $oldStatus,
            'new_rent_status' => $request->rent_status,
            'updated_at' => now(),
            'user_email' => $availability->user->email,
            // 'admin_email' => auth()->admin()->email,
        ]);

        // Redirect back with a success message
        return redirect()->route('admin.facilities.reservations')
            ->with('success', 'Reservation status updated and history saved.');
    }

    // public function showHistory($id)
    // {
    //     // Fetch the availability object based on the reservation ID
    //     $availability = Availability::findOrFail($id);

    //     // Fetch the reservation history for this availability
    //     $history = ReseHistory::where('availability_id', $id)->get();

    //     // Pass both availability and history data to the view
    //     return view('admin.reservation-history', compact('availability', 'history'));
    // }




}
