<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Facility;
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
    return view('admin.facilities.create');
}
public function reservations()
{
    return view('admin.facilities.reservations');
}
public function events()
{
    return view('admin.facilities.reservations-events');
}
public function reservationHistory()
{
    return view('admin.facilities.reservations-history');
}



public function store(Request $request)
{

    $request->validate([
        'name' => 'required|string|max:255',
        'facility_type' => 'required|string|in:individual,whole_place,both',
        'slug' => 'unique:facilities,slug',
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        'requirements' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
        'sex_restriction' => 'nullable|in:male,female',
        'prices' => 'nullable|array',
        'prices.*.name' => 'required|string',
        'prices.*.value' => 'required|numeric',
        'prices.*.price_type' => 'required|string|in:individual,whole',
        'prices.*.is_based_on_days' => 'nullable|boolean',
        'prices.*.date_from' => 'nullable|date|required_if:prices.*.is_based_on_days,true',
        'prices.*.date_to' => 'nullable|date|required_if:prices.*.is_based_on_days,true|after_or_equal:prices.*.date_from',

        // 'whole_capacity' => $request->facility_type === 'whole_place' ? 'required|numeric|min:1' : 'nullable',
        'whole_capacity' => $request->facility_type === 'whole_place' ||
            ($request->facility_type === 'both' && empty($request->input('facility_attributes', [])))
            ? 'required|numeric|min:1'
            : 'nullable',
        'facility_attributes' => 'nullable|array',
        'facility_attributes.*.room_name' => 'nullable|string|max:255',
        'facility_attributes.*.capacity' => 'nullable|integer|min:1',
        'facility_attributes.*.sex_restriction' => 'nullable|in:male,female',
    ]);

    $facility = new Facility();
    $this->save($facility, $request);
    $this->handleImage($facility, $request);
    $facility->save();

    if ($request->facility_type === 'whole_place') {
        FacilityAttribute::create([
            'facility_id' => $facility->id,
            'room_name' => null,
            'capacity' => null,
            'whole_capacity' => $request->whole_capacity,
            'sex_restriction' => null,
        ]);
    } elseif ($request->facility_type === 'individual') {
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
    } elseif ($request->facility_type === 'both') {
        $facilityAttributes = $request->input('facility_attributes', []);

        if (!empty($facilityAttributes)) {

            foreach ($facilityAttributes as $attribute) {
                FacilityAttribute::create([
                    'facility_id' => $facility->id,
                    'room_name' => $attribute['room_name'] ?? null,
                    'capacity' => $attribute['capacity'] ?? null,
                    'whole_capacity' =>  $request->whole_capacity ?? null,
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

    if (is_array($request->prices)) {
        $pricesData = [];
        foreach ($request->prices as $price) {
            $pricesData[] = [
                'facility_id' => $facility->id,
                'name' => $price['name'],
                'value' => $price['value'],
                'price_type' => $price['price_type'],
                'is_based_on_days' => $price['is_based_on_days'] ?? false,
                'is_quantity_fields' => $price['is_quantity_fields'] ?? false,
                'date_from' => isset($price['is_based_on_days']) && $price['is_based_on_days'] ? $price['date_from'] : null,
                'date_to' => isset($price['is_based_on_days']) && $price['is_based_on_days'] ? $price['date_to'] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Price::insert($pricesData);
    }
    return response()->json(['message' => 'Facility created successfully!', 'action' => 'create']);
}

public function edit($id)
{
    $facility =  Facility::find($id);
    $facilityAttributes = FacilityAttribute::where('facility_id', $facility->id)->get();
    $prices = Price::where('facility_id', $facility->id)->get();
    return view('admin.facilities.edit', compact('facility',  'facilityAttributes', 'prices'));
}

public function update(Request $request, $id)
{
    $facility = Facility::findOrFail($id);
    if ($request->has('sex_restriction') && is_null($request->sex_restriction)) {
        $request->merge(['sex_restriction' => '']);
    }
    $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('facilities')->ignore($id),
        ],
        'facility_type' => 'required|string|in:individual,whole_place,both',
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        'requirements' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
        // 'sex_restriction' => 'nullable|in:male,female',
        'prices' => 'nullable|array',
        'prices.*.name' => 'nullable|string',
        'prices.*.value' => 'nullable|numeric',
        'prices.*.price_type' => 'nullable|string|in:individual,whole',
        'prices.*.is_based_on_days' => 'nullable|boolean',
        'prices.*.date_from' => 'nullable|date|required_if:prices.*.is_based_on_days,true',
        'prices.*.date_to' => 'nullable|date|required_if:prices.*.is_based_on_days,true|after_or_equal:prices.*.date_from',

        'whole_capacity' => $request->facility_type === 'whole_place' ||
            ($request->facility_type === 'both' && empty($request->input('facility_attributes', [])))
            ? 'required|numeric|min:1'
            : 'nullable',
        'facility_attributes' => 'nullable|array',
        'facility_attributes.*.room_name' => 'nullable|string|max:255',
        'facility_attributes.*.capacity' => 'nullable|integer|min:1',
        'facility_attributes.*.sex_restriction' => 'nullable|in:male,female,null',

    ]);

    $this->save($facility, $request);
    $this->handleImage($facility, $request);

    $facility->save();
    FacilityAttribute::where('facility_id', $facility->id)->delete();
    if ($request->facility_type === 'whole_place') {
        FacilityAttribute::create([
            'facility_id' => $facility->id,
            'room_name' => null,
            'capacity' => null,
            'whole_capacity' => $request->whole_capacity,
            'sex_restriction' => null,
        ]);
    } elseif ($request->facility_type === 'individual') {
        $facilityAttributes = $request->input('facility_attributes', []);
        if (!empty($facilityAttributes)) {
            $validAttributes = array_filter($facilityAttributes, function ($attr) {
                return $attr['sex_restriction'] !== null;
            });
            $this->createFacilityAttributes($facility, $facilityAttributes,  $validAttributes);
        }
    } elseif ($request->facility_type === 'both') {
        $facilityAttributes = $request->input('facility_attributes', []);

        if (!empty($facilityAttributes)) {
            $validAttributes = array_filter($facilityAttributes, function ($attr) {
                return $attr['room_name'] !== null && $attr['capacity'] !== null;
            });
            $this->createFacilityAttributes($facility, $facilityAttributes);
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



    if (is_array($request->prices)) {
        $pricesData = [];
        foreach ($request->prices as $price) {
            $pricesData[] = [
                'facility_id' => $facility->id,
                'name' => $price['name'],
                'value' => $price['value'],
                'price_type' => $price['price_type'],
                'is_based_on_days' => $price['is_based_on_days'],
                'is_quantity_fields' => $price['is_quantity_fields'] ?? false,
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

        \Log::info('Saving image to: ' . $destinationPath . '/' . $imageName);
    } catch (\Exception $e) {
        \Log::error('Image processing failed: ' . $e->getMessage());
    }
}
}
