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

    public function index(Request $request)
    {
        // Get the 'archived' value from the query string, defaulting to 0
        $archived = $request->query('archived', 0);
        
        // Apply the 'archived' filter before pagination
        $facilities = Facility::where('archived', $archived)
                              ->orderBy('created_at', 'DESC')
                              ->paginate(5);
        
        // Return the view with facilities and the archived filter status
        return view('admin.facilities.index', compact('facilities', 'archived'));
    }


    public function create()
    {
        return view('admin.facilities.create');
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
            'prices.*.is_based_on_days' => 'required|boolean',

            'facility_attributes' => 'nullable|array',
            'facility_attributes.*.room_name' => 'nullable|string|max:255',
            'facility_attributes.*.capacity' => 'nullable|integer|min:1',
            'facility_attributes.*.sex_restriction' => 'nullable|in:male,female',
        ]);


        \Log::info('Facility Creation Request:', $request->all());
        $facilityAttributes = $request->input('facility_attributes', []);

        // Ensure facility_attributes is an array
        if (is_string($facilityAttributes)) {
            try {
                $facilityAttributes = json_decode($facilityAttributes, true);
            } catch (\Exception $e) {
                $facilityAttributes = [];
            }
        }

        if (!is_array($facilityAttributes)) {
            $facilityAttributes = [];
        }

        $prices = $request->input('prices');

        // More robust price processing
        if (is_string($prices)) {
            try {
                $prices = json_decode($prices, true);
            } catch (\Exception $e) {
                $prices = [];
            }
        }

        // Ensure prices is an array
        if (!is_array($prices)) {
            $prices = [];
        }

        $facility = new Facility();
        $facility->name = $request->name;
        $facility->facility_type = $request->facility_type;
        $facility->description = $request->description;
        $facility->created_by = Auth::id();
        $facility->slug = Str::slug($request->name);
        $facility->status = $request->status ?? 1;
        $facility->featured = $request->featured ?? 0;
        $facility->rules_and_regulations = $request->rules_and_regulations;
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

        if (!empty($facilityAttributes)) {
            $facilityAttributesData = [];
            foreach ($facilityAttributes as $attribute) {
                // Add more defensive checks
                $roomName = $attribute['room_name'] ?? null;
                $capacity = $attribute['capacity'] ?? null;
                $sexRestriction = $attribute['sex_restriction'] ?? null;

                // Only add if at least one meaningful attribute is present
                if ($roomName || $capacity) {
                    $facilityAttributesData[] = [
                        'facility_id' => $facility->id,
                        'room_name' => $roomName,
                        'capacity' => $capacity,
                        'sex_restriction' => $sexRestriction,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($facilityAttributesData)) {
                FacilityAttribute::insert($facilityAttributesData);
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Price::insert($pricesData);
        }




        return response()->json(['message' => 'Facility created successfully!']);
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

    public function edit($id)
    {
        $facility =  Facility::find($id);
        $facilityAttributes = FacilityAttribute::where('facility_id', $facility->id)->get();
        $prices = Price::where('facility_id', $facility->id)->get();
        return view('admin.facilities.edit', compact('facility',  'facilityAttributes', 'prices'));
    }


   
public function update(Request $request, $id)
{
    // Find the existing facility by ID
    $facility = Facility::findOrFail($id);

    // Validate the incoming request
    $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('facilities')->ignore($id), // Ignore unique check for unchanged name
        ],
        'facility_type' => 'required|string|in:individual,whole_place,both',
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        'requirements' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
        'sex_restriction' => 'nullable|in:male,female',
        'prices' => 'nullable|array',
        'prices.*.name' => 'nullable|string',
        'prices.*.value' => 'nullable|numeric',
        'prices.*.price_type' => 'nullable|string|in:individual,whole',
        'prices.*.is_based_on_days' => 'nullable|boolean',
        'facility_attributes' => 'nullable|array',
        'facility_attributes.*.room_name' => 'nullable|string|max:255',
        'facility_attributes.*.capacity' => 'nullable|integer|min:1',
        'facility_attributes.*.sex_restriction' => 'nullable|in:male,female',
    ]);

    // Log incoming request for debugging
    \Log::info('Facility Update Request:', $request->all());

    // Retrieve input data
    $facilityAttributes = $request->input('facility_attributes', []);
    $prices = $request->input('prices', []);

    // Ensure attributes and prices are arrays
    $facilityAttributes = is_string($facilityAttributes) ? json_decode($facilityAttributes, true) : $facilityAttributes;
    $prices = is_string($prices) ? json_decode($prices, true) : $prices;

    // Only update the fields that have been changed
    if ($request->has('name') && $facility->name !== $request->name) {
        $facility->name = $request->name;
    }

    if ($request->has('facility_type') && $facility->facility_type !== $request->facility_type) {
        $facility->facility_type = $request->facility_type;
    }

    if ($request->has('description') && $facility->description !== $request->description) {
        $facility->description = $request->description;
    }

    if ($request->has('slug') && $facility->slug !== $request->slug) {
        $facility->slug = Str::slug($request->slug);
    }

    // Handle file uploads only if a new file is uploaded
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = Carbon::now()->timestamp . '.' . $image->extension();
        $this->GenerateFacilityThumbnailsImage($image, $imageName);
        $facility->image = 'facilities/' . $imageName;
    }

    if ($request->hasFile('images')) {
        $gallery_arr = [];
        $current_timestamp = Carbon::now()->timestamp;
        $gallery_images = '';
        $files = $request->file('images');
        foreach ($files as $index => $file) {
            $fileExtension = $file->getClientOriginalExtension();
            if (in_array($fileExtension, ['jpg', 'png', 'jpeg'])) {
                $fileName = $current_timestamp . "_" . ($index + 1) . '.' . $fileExtension;
                $this->GenerateFacilityThumbnailsImage($file, $fileName);
                $gallery_arr[] = 'facilities/' . $fileName;
            }
        }
        $gallery_images = implode(',', $gallery_arr);
        $facility->images = $gallery_images;
    }

    if ($request->hasFile('requirements')) {
        $requirementsFile = $request->file('requirements');
        $requirementsFileName = Carbon::now()->timestamp . '-requirements.' . $requirementsFile->getClientOriginalExtension();
        $destinationPath = storage_path('app/public/facilities/');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        $requirementsFile->move($destinationPath, $requirementsFileName);
        $facility->requirements = $requirementsFileName;
    }

    // Save the updated facility
    $facility->save();

    // Handle facility attributes and prices updates
    if (!empty($facilityAttributes)) {
        // Update or insert facility attributes
        $facilityAttributesData = [];
        foreach ($facilityAttributes as $attribute) {
            $facilityAttributesData[] = [
                'facility_id' => $facility->id,
                'room_name' => $attribute['room_name'],
                'capacity' => $attribute['capacity'],
                'sex_restriction' => $attribute['sex_restriction'],
                'updated_at' => now(),
            ];
        }

        FacilityAttribute::where('facility_id', $facility->id)->delete(); // Remove old attributes
        if (!empty($facilityAttributesData)) {
            FacilityAttribute::insert($facilityAttributesData); // Insert new attributes
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
                'is_based_on_days' => $price['is_based_on_days'],  // Ensure it's included
                'updated_at' => now(),
            ];
        }
        Price::where('facility_id', $facility->id)->delete(); // Delete old prices
        Price::insert($pricesData); // Insert new prices
    }
    

    // Return success message
    return response()->json(['message' => 'Facility updated successfully!']);
}

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

        // Fetch the facilities being restored
        $facilities = Facility::whereIn('id', $facilityIds)->get();

        // Restore facilities by setting `archived` to 0
        Facility::whereIn('id', $facilityIds)->update(['archived' => 0]);

        return response()->json([
            'status' => 'Facilities restored successfully!',
            'facilities' => $facilities, // Pass back the restored facilities
        ]);
    }



    public function showFacilities()
    {
        $facilities = Facility::all();
        $archivedFacilities = Facility::where('archived', 1)
                                    ->orderBy('created_at', 'DESC')
                                    ->paginate(10); // Paginate archived facilities

        // Pass only archived facilities to the view
        return view('admin.facilities.archive.index', compact('archivedFacilities', 'facilities'));
    }
}
