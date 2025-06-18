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
        $search = $request->input('search');
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'DESC');

        $query = Facility::with('facilityAttributes', 'prices')
            ->where('archived', $archived);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('facility_type', 'like', "%{$search}%");
            });
            $sortColumn = 'name';
        }

        $query->orderBy($sortColumn, $sortDirection);
        $facilities = $query->paginate(5)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'facilities' => view('partials._facilities-table', compact('facilities'))->render(),
                'pagination' => view('partials._facilities-pagination', compact('facilities'))->render()
            ]);
        }

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

    // public function store(FacilityRequest $request)
    // {
    //     $facility = new Facility();
    //     $this->save($facility, $request);
    //     $this->handleImage($facility, $request);
    //     $facility->save();
    //     $this->handleFacilityAttributes($facility, $request);
    //     $this->handlePrices($facility, $request);
    //     // dd($request->all());
    //     return response()->json(['message' => 'Facility created successfully!', 'action' => 'create']);
    // }


    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:facilities,name',
            'slug' => 'nullable|unique:facilities,slug',
            'facility_type' => 'required|string|in:individual,whole_place,both',
            'description' => 'required|string|max:255',
            'rules_and_regulations' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images' => 'nullable|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'whole_capacity' => $this->facilityTypeRequiresWholeCapacity($request) ? 'required|numeric|min:1' : 'nullable',
            'requirements' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'facility_attributes_json' => 'nullable|string',
        ];

        $facilityAttributes = json_decode($request->facility_attributes_json, true) ?? [];
        $prices = json_decode($request->prices_json, true) ?? [];
        $messages = [];
        switch ($request->facility_type) {
            case 'individual':
                $rules['facility_attributes_json'] = 'required|string';
                $rules['facility_attributes.*.room_name'] = 'required|string|max:255';
                $rules['facility_attributes.*.capacity'] = 'required|numeric|min:1|max:50';
                $rules['facility_attributes.*.sex_restriction'] = 'required|string|in:male,female';
                $messages['facility_attributes_json.required'] = 'Facility Attributes are required for Individual Type. Each room must have a name, capacity, and sex restriction.';
                $messages['facility_attributes.*.room_name.required'] = 'Room Name is required for Individual Type.';
                $messages['facility_attributes.*.capacity.required'] = 'Capacity is required for Individual Type.';
                $messages['facility_attributes.*.sex_restriction.required'] = 'Sex Restriction is required for Individual Type.';
                break;
            case 'whole_place':
                $rules['whole_capacity'] = 'required|numeric|min:1';
                $messages['whole_capacity.required'] = 'Whole Capacity is required for Whole Place Type. Please provide a valid capacity.';
                break;
            case 'both':
                if (!empty($facilityAttributes) && isset($facilityAttributes[0]['capacity'])) {
                    // Individual rooms are provided
                    $rules['facility_attributes_json'] = 'required|string';
                    $rules['facility_attributes.*.room_name'] = 'required|string|max:255';
                    $rules['facility_attributes.*.capacity'] = 'required|numeric|min:1|max:50';
                    $rules['facility_attributes.*.sex_restriction'] = 'required|string|in:male,female';
                    $messages['facility_attributes_json.required'] = 'Facility Attributes are required for Both Type. Each room must have a name, capacity, and sex restriction.';
                    $messages['facility_attributes.*.room_name.required'] = 'Room Name is required for Both Type.';
                    $messages['facility_attributes.*.capacity.required'] = 'Capacity is required for Both Type.';
                    $messages['facility_attributes.*.sex_restriction.required'] = 'Sex Restriction is required for Both Type.';
                } elseif (request()->has('whole_capacity') && request()->input('whole_capacity')) {
                    $rules['facility_attributes_json'] = 'nullable|string';
                    $rules['whole_capacity'] = 'required|numeric|min:1';
                    $messages['whole_capacity.required'] = 'Whole Capacity is required when no individual room attributes are specified.';
                } else {
                    $rules['facility_attributes_json'] = 'required_without:whole_capacity';
                    $rules['whole_capacity'] = 'required_without:facility_attributes_json';
                    $messages['facility_attributes_json.required_without'] = 'Either individual rooms or whole capacity must be provided for Both Type.';
                    $messages['whole_capacity.required_without'] = 'Either individual rooms or whole capacity must be provided for Both Type.';
                }
                break;
        }
        $validated = $request->validate($rules, $messages);

        try {
            $current_timestamp = Carbon::now()->timestamp;
            $requirementsFileName = null;

            if ($request->hasFile('requirements')) {
                $requirementsFile = $request->file('requirements');
                $requirementsFileName = $current_timestamp . '-requirements.' . $requirementsFile->getClientOriginalExtension();

                if (Facility::where('requirements', $requirementsFileName)->exists()) {
                    return redirect()->back()->withErrors([
                        'requirements' => 'The Requirements file name already exists. Please rename the file.'
                    ])->withInput();
                }

                $destinationPath = storage_path('app/public/facilities/');
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }
                $requirementsFile->move($destinationPath, $requirementsFileName);
            }

            $data = [
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'facility_type' => $validated['facility_type'],
                'description' => $validated['description'],
                'rules_and_regulations' => $validated['rules_and_regulations'],
                'created_by' => Auth::id(),
                'requirements' => $requirementsFileName,
            ];

            $facility = Facility::create($data);
            $this->handleFacilityAttributes($facility, $facilityAttributes);
            $this->handlePrices($facility, $prices);

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

            $facility->save();

            return redirect()->route('admin.facilities.index')->with('success', 'Facility created successfully.');
        } catch (\Exception $e) {
            \Log::error('Facility creation error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'An error occurred while creating the facility. Please try again.'])->withInput();
        }
    }
    private function facilityTypeRequiresWholeCapacity(Request $request): bool
    {
        $facilityType = $request->input('facility_type');
        $facilityAttributes = json_decode($request->input('facility_attributes_json'), true) ?? [];

        if ($facilityType === 'whole_place') {
            return true;
        }

        if ($facilityType === 'both') {
            return empty($facilityAttributes) || !isset($facilityAttributes[0]['capacity']);
        }

        return false;
    }
    private function handleFacilityAttributes(Facility $facility, array $facilityAttributes)
    {

        if ($facility->facility_type === 'individual') {
            if (!empty($facilityAttributes)) {
                foreach ($facilityAttributes as $attribute) {
                    FacilityAttribute::create([
                        'facility_id' => $facility->id,
                        'room_name' => $attribute['room_name'] ?? null,
                        'capacity' => $attribute['capacity'] ?? null,
                        'sex_restriction' => $attribute['sex_restriction'] ?? null,
                        'whole_capacity' => null,
                    ]);
                }
            }
        } elseif ($facility->facility_type === 'whole_place') {
            FacilityAttribute::create([
                'facility_id' => $facility->id,
                'room_name' => null,
                'capacity' => null,
                'whole_capacity' => request()->input('whole_capacity'),
                'sex_restriction' => null,
            ]);
        } elseif ($facility->facility_type === 'both') {
            if (!empty($facilityAttributes) && isset($facilityAttributes[0]['capacity'])) {
                foreach ($facilityAttributes as $attribute) {
                    FacilityAttribute::create([
                        'facility_id' => $facility->id,
                        'room_name' => $attribute['room_name'] ?? null,
                        'capacity' => $attribute['capacity'] ?? null,
                        'sex_restriction' => $attribute['sex_restriction'] ?? null,
                        'whole_capacity' => null,
                    ]);
                }
            } elseif (request()->has('whole_capacity') && request()->input('whole_capacity')) {
                FacilityAttribute::create([
                    'facility_id' => $facility->id,
                    'room_name' => null,
                    'capacity' => null,
                    'sex_restriction' => null,
                    'whole_capacity' => request()->input('whole_capacity'),
                ]);
            } else {
                \Log::warning('Neither rooms nor whole capacity provided for "both" facility type', [
                    'facility_id' => $facility->id,
                    'facility_attributes' => $facilityAttributes,
                    'whole_capacity' => request()->input('whole_capacity')
                ]);
            }
        }
    }

    private function handlePrices(Facility $facility, array $prices)
    {
        $pricesData = [];

        foreach ($prices as $price) {
            $pricesData[] = [
                'facility_id' => $facility->id,
                'name' => $price['priceName'] ?? $price['name'],
                'value' => $price['priceValue'] ?? $price['value'],
                'price_type' => $price['priceType'] ?? 'individual',
                'is_based_on_days' => $price['isBasedOnDays'] == 1,
                'is_there_a_quantity' => $price['isThereAQuantity'] == 1,
                'date_from' => ($price['isBasedOnDays'] == 1) ? $price['dateFrom'] : null,
                'date_to' => ($price['isBasedOnDays'] == 1) ? $price['dateTo'] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($pricesData)) {
            Price::insert($pricesData);
        }
    }
    public function edit($id)
    {
        $facility =  Facility::find($id);
        $facilityAttributes = FacilityAttribute::where('facility_id', $facility->id)->first();
        $prices = Price::where('facility_id', $facility->id)->get();
        return view('admin.facilities.edit', compact('facility',  'facilityAttributes', 'prices'));
    }

    // public function update(FacilityUpdateRequest $request, $id)
    // {
    //     $request->validate([
    //         'name' => 'required|unique:facilities,name,' . $id,
    //     ]);

    //     $facility = Facility::findOrFail($id);
    //     $request->merge([
    //         'sex_restriction' => $request->sex_restriction ?? '',
    //         'name' => $request->name ?: $facility->name,
    //     ]);

    //     $this->save($facility, $request);
    //     $this->handleImage($facility, $request);
    //     $facility->save();

    //     // Get facility attributes from request
    //     $facilityAttributes = $request->input('facility_attributes', []);

    //     if ($request->facility_type === 'whole_place') {
    //         // Delete attributes only if facility_type has changed to "whole_place"
    //         FacilityAttribute::where('facility_id', $facility->id)->delete();

    //         FacilityAttribute::create([
    //             'facility_id' => $facility->id,
    //             'room_name' => null,
    //             'capacity' => null,
    //             'whole_capacity' => $request->whole_capacity,
    //             'sex_restriction' => null,
    //         ]);
    //     } elseif ($request->facility_type === 'individual' || $request->facility_type === 'both') {
    //         // Only delete existing attributes if new ones are being provided
    //         if (!empty($facilityAttributes)) {
    //             FacilityAttribute::where('facility_id', $facility->id)->delete();

    //             $validAttributes = array_filter($facilityAttributes, function ($attr) {
    //                 return isset($attr['room_name']) && isset($attr['capacity']);
    //             });

    //             $this->createFacilityAttributes($facility, $validAttributes);
    //         }
    //     }

    //     if (is_array($request->prices)) {
    //         $pricesData = [];
    //         foreach ($request->prices as $price) {
    //             $pricesData[] = [
    //                 'facility_id' => $facility->id,
    //                 'name' => $price['name'],
    //                 'value' => $price['value'],
    //                 'price_type' => $price['price_type'],
    //                 'is_based_on_days' => filter_var($price['is_based_on_days'], FILTER_VALIDATE_BOOLEAN),
    //                 // 'is_there_a_quantity' => filter_var($price['is_there_a_quantity'], FILTER_VALIDATE_BOOLEAN),
    //                 'is_there_a_quantity' => $price['is_there_a_quantity'] ?? false,
    //                 'date_from' => isset($price['is_based_on_days']) && $price['is_based_on_days'] ? $price['date_from'] : null,
    //                 'date_to' => isset($price['is_based_on_days']) && $price['is_based_on_days'] ? $price['date_to'] : null,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ];
    //         }

    //         Price::where('facility_id', $facility->id)->delete();
    //         Price::insert($pricesData);
    //     }

    //     return response()->json(['message' => 'Facility updated successfully!', 'action' => 'update']);
    // }


    // private function createFacilityAttributes($facility, $facilityAttributes)
    // {
    //     foreach ($facilityAttributes as $attribute) {
    //         if (isset($attribute['room_name']) && isset($attribute['capacity'])) {
    //             // Explicitly handle sex_restriction
    //             $sexRestriction = isset($attribute['sex_restriction']) &&
    //                 in_array($attribute['sex_restriction'], ['male', 'female'])
    //                 ? $attribute['sex_restriction']
    //                 : null;

    //             if (isset($attribute['room_name'], $attribute['capacity'])) {
    //                 FacilityAttribute::create([
    //                     'facility_id' => $facility->id,
    //                     'room_name' => $attribute['room_name'],
    //                     'capacity' => $attribute['capacity'],
    //                     'sex_restriction' => $sexRestriction,
    //                 ]);
    //             }
    //         }
    //     }
    // }

    // private function save(Facility $facility, Request $request)
    // {
    //     $facility->name = $request->name;
    //     $facility->facility_type = $request->facility_type;
    //     $facility->description = $request->description;
    //     $facility->created_by = Auth::id();
    //     $facility->slug = Str::slug($request->name);
    //     $facility->rules_and_regulations = $request->rules_and_regulations;
    // }

    // private function handleImage(Facility $facility, Request $request)
    // {
    //     $current_timestamp = Carbon::now()->timestamp;

    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         $imageName = $current_timestamp . '.' . $image->extension();
    //         $this->GenerateFacilityThumbnailsImage($image, $imageName);
    //         $facility->image = 'facilities/' . $imageName;
    //     }
    //     $gallery_arr = [];
    //     $gallery_images = "";
    //     $counter = 1;

    //     if ($request->hasFile('images')) {
    //         $allowedFileExtension = ['jpg', 'png', 'jpeg'];
    //         $files = $request->file('images');

    //         foreach ($files as $file) {
    //             $gextension = $file->getClientOriginalExtension();
    //             $gcheck = in_array($gextension, $allowedFileExtension);

    //             if ($gcheck) {
    //                 $gFileName = $current_timestamp . "." . $counter . '.' . $gextension;
    //                 $this->GenerateFacilityThumbnailsImage($file, $gFileName);
    //                 array_push($gallery_arr, 'facilities/' . $gFileName);
    //                 $counter++;
    //             }
    //         }
    //         $gallery_images = implode(',', $gallery_arr);
    //         $facility->images = $gallery_images;
    //     }

    //     if ($request->hasFile('requirements')) {
    //         $requirementsFile = $request->file('requirements');
    //         $requirementsFileName = $current_timestamp . '-requirements.' . $requirementsFile->getClientOriginalExtension();
    //         if (Facility::where('requirements', $requirementsFileName)->exists()) {
    //             return redirect()->back()->withErrors(['requirements' => 'The Requirements file name already exists. Please rename the file.'])->withInput();
    //         }
    //         $destinationPath = storage_path('app/public/facilities/');
    //         if (!File::exists($destinationPath)) {
    //             File::makeDirectory($destinationPath, 0755, true);
    //         }
    //         $requirementsFile->move($destinationPath, $requirementsFileName);
    //         $facility->requirements = $requirementsFileName;
    //     }
    //     $facility->save();
    // }

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
}
