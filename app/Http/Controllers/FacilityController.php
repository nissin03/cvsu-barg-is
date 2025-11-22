<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Price;
use App\Models\Payment;
use App\Models\Discount;
use App\Models\Facility;
use Illuminate\Support\Str;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Services\ImageProcessor;
use App\Models\FacilityAttribute;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\TransactionReservation;
use Intervention\Image\Laravel\Facades\Image;


class FacilityController extends Controller
{
    protected $imageProcessor;
    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }
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
        $facilities = $query->paginate(10)->withQueryString();

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
        // $addons = Addon::where('is_available', true)->orderBy('name')->get();
        $addons = Addon::where('is_available', true)
            ->whereNull('facility_id')
            ->orderBy('name')
            ->get();

        $discounts = Discount::where('active', 1)->orderBy('name')->get();
        return view('admin.facilities.create', compact('discounts', 'addons'));
    }

    public function store(Request $request)
    {
        $facilityAttributes = json_decode($request->facility_attributes_json, true) ?? [];
        $prices = json_decode($request->prices_json, true) ?? [];
        $rules = [
            'name' => 'required|unique:facilities,name',
            'slug' => 'nullable|unique:facilities,slug',
            'facility_type' => 'required|string|in:individual,whole_place,both',
            'description' => 'required|string|max:2000',
            'rules_and_regulations' => 'required|string|max:2000',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            // 'images' => 'nullable|array|min:1',
            'images' => 'nullable|array|min:1|max:7',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            // 'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'whole_capacity' => $this->facilityTypeRequiresWholeCapacity($request) ? 'required|numeric|min:1' : 'nullable',
            'requirements' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'facility_attributes_json' => 'nullable|string',
            // 'addons' => 'nullable|string'
        ];
        // $messages = [];
        $messages = [
            'image.required' => 'The main image is required.',
            'image.image' => 'The main image must be an image file.',
            'image.mimes' => 'The main image must be a file of type: jpeg, png, jpg, gif, svg.',
            'images.min' => 'You must upload at least 5 gallery images.',
            'images.max' => 'You cannot upload more than 7 gallery images.',
            'images.*.image' => 'All gallery images must be image files.',
            'images.*.mimes' => 'All gallery images must be files of type: jpeg, png, jpg, gif, svg.',
            'images.*.max' => 'Each gallery image may not be greater than 10MB.',

            'requirements.required' => 'The requirements file is required.',
            'requirements.file' => 'The requirements must be a file.',
            'requirements.mimes' => 'The requirements file must be a file of type: pdf, doc, docx.',
            'requirements.max' => 'The requirements file may not be greater than 2MB.',
        ];
        switch ($request->facility_type) {
            case 'individual':
                $rules['facility_attributes_json'] = 'required|string';
                $rules['facility_attributes.*.room_name'] = 'required|string|max:255';
                $rules['facility_attributes.*.capacity'] = 'required|numeric|min:1|max:50';
                $rules['facility_attributes.*.sex_restriction'] = 'required|string|in:male,female,all';
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
                $rules['facility_selection_both'] = 'required|in:whole,room';
                if (!empty($facilityAttributes) && isset($facilityAttributes[0]['capacity'])) {
                    // Individual rooms are provided
                    $rules['facility_attributes_json'] = 'required|string';
                    $rules['facility_attributes.*.room_name'] = 'required|string|max:255';
                    $rules['facility_attributes.*.capacity'] = 'required|numeric|min:1|max:50';
                    $rules['facility_attributes.*.sex_restriction'] = 'required|string|in:male,female,all';
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
            $current_timestamp = now()->timestamp;
            $requirementsFileName = null;

            if ($request->hasFile('requirements')) {
                $requirementsFile = $request->file('requirements');
                // $requirementsFileName = $current_timestamp . '-requirements.' . $requirementsFile->getClientOriginalExtension();
                $originalName = pathinfo($requirementsFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = Str::slug($originalName);
                $extension = $requirementsFile->getClientOriginalExtension();

                $requirementsFileName = $current_timestamp . '-' . $safeName . '.' . $extension;

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
                // 'requirements' => $requirementsFileName,
                'requirements' => $requirementsFileName ? 'facilities/' . $requirementsFileName : null,
            ];
            if ($validated['facility_type'] === 'both') {
                $data['facility_selection_both'] = $validated['facility_selection_both'];
            }

            $facility = Facility::create($data);
            $this->handleFacilityAttributes($facility, $facilityAttributes);
            $this->handlePrices($facility, $prices);
            // $this->handleAddons($facility, $request);

            if (in_array($facility->facility_type, ['whole_place', 'both'])) {
                $selectedDiscountIds = collect(explode(',', $request->input('selected_discounts', '')))
                    ->filter()
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();
                $facility->discounts()->sync($selectedDiscountIds);
            }

            $selectedAddonIds = collect(explode(',', $request->input('selected_addons', '')))
                ->filter()
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            Addon::whereIn('id', $selectedAddonIds)->update(['facility_id' => $facility->id]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $current_timestamp . '.' . $image->extension();

                $this->imageProcessor->process($image, $imageName, [
                    ['path' => storage_path('app/public/facilities'), 'cover' => [689, 689, 'center']],
                    ['path' => storage_path('app/public/facilities/thumbnails'), 'resize' => [300, 300]],
                ]);
                $facility->image = 'facilities/' . $imageName;
            }

            $gallery_arr = [];
            $gallery_images = "";
            $counter = 1;

            if ($request->hasFile('images')) {
                $allowedFileExtension = ['jpg', 'png', 'jpeg'];
                $files = $request->file('images');
                if (count($files) > 7) {
                    return redirect()->back()->withErrors([
                        'images' => 'You can only upload up to 7 gallery images.'
                    ])->withInput();
                }
                foreach ($files as $file) {
                    if ($counter > 7) break;
                    $gextension = $file->getClientOriginalExtension();
                    $gcheck = in_array($gextension, $allowedFileExtension);
                    if ($gcheck) {
                        $gFileName = $current_timestamp . "." . $counter . '.' . $gextension;

                        $this->imageProcessor->process($file, $gFileName, [
                            ['path' => storage_path('app/public/facilities'), 'cover' => [689, 689, 'center']],
                            ['path' => storage_path('app/public/facilities/thumbnails'), 'resize' => [300, 300]],
                        ]);
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

    private function handleAddons(Facility $facility, Request $request)
    {
        $addonsInput = $request->input('addons', '');
        if (is_array($addonsInput)) {
            $addonsString = implode(',', $addonsInput);
        } else {
            $addonsString = $addonsInput;
        }

        if (!empty($addonsString)) {
            $addonIds = array_filter(explode(',', $addonsString));
            $addonIds = array_filter(array_map('intval', $addonIds));
            $validAddonIds = Addon::whereIn('id', $addonIds)->pluck('id')->toArray();
            $facility->addons()->sync($validAddonIds);
        } else {
            $facility->addons()->detach();
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
        $facility = Facility::with(['facilityAttributes', 'prices', 'discounts', 'addons'])->findOrFail($id);
        $discounts = Discount::where('active', 1)->orderBy('name')->get();
        // $addons = Addon::all();
        $addons = Addon::where('is_available', true)
            ->where(function ($query) use ($facility) {
                $query->whereNull('facility_id')
                    ->orWhere('facility_id', $facility->id);
            })
            ->orderBy('name')
            ->get();

        return view('admin.facilities.edit', [
            'facility' => $facility,
            'facilityAttributes' => $facility->facilityAttributes,
            'prices' => $facility->prices,
            'discounts' => $discounts,
            'addons' => $addons,
        ]);
    }
    public function update(Request $request, $id, ImageProcessor $imageProcessor)
    {
        $facility = Facility::findOrFail($id);
        $facilityAttributes = collect(json_decode($request->facility_attributes_json, true) ?? [])
            ->filter(function ($attr) {
                return !empty($attr['room_name']) || !empty($attr['capacity']) || !empty($attr['sex_restriction']) || !empty($attr['whole_capacity']);
            })
            ->values()
            ->all();
        $prices = json_decode($request->prices_json, true) ?? [];
        $wholeCapacityValue = $request->input('whole_capacity');
        if ($request->facility_type === 'both' && $wholeCapacityValue && empty($facilityAttributes)) {
            $existingWholeRecord = $facility->facilityAttributes()->whereNotNull('whole_capacity')->first();
            $facilityAttributes = [[
                'id' => $existingWholeRecord ? $existingWholeRecord->id : null,
                'room_name' => null,
                'capacity' => null,
                'sex_restriction' => null,
                'whole_capacity' => $wholeCapacityValue
            ]];
        }
        if ($request->facility_type === 'whole_place' && $wholeCapacityValue) {
            $existingWholeRecord = $facility->facilityAttributes()->whereNotNull('whole_capacity')->first();

            $facilityAttributes = [[
                'id' => $existingWholeRecord ? $existingWholeRecord->id : null,
                'room_name' => null,
                'capacity' => null,
                'sex_restriction' => null,
                'whole_capacity' => $wholeCapacityValue
            ]];
        }
        if ($request->facility_type === 'both') {
            if (!$request->filled('facility_attributes_json') && !$request->filled('whole_capacity')) {
                if ($facility->facilityAttributes()->whereNotNull('capacity')->exists()) {
                    $request->merge([
                        'facility_attributes_json' => json_encode($facility->facilityAttributes()->get()->toArray())
                    ]);
                    $facilityAttributes = json_decode($request->facility_attributes_json, true);
                } elseif ($facility->facilityAttributes()->whereNotNull('whole_capacity')->exists()) {
                    $request->merge([
                        'whole_capacity' => $facility->facilityAttributes()->value('whole_capacity')
                    ]);
                }
            }
        }

        $rules = [
            'name' => ['required', Rule::unique('facilities', 'name')->ignore($facility->id),],
            'facility_type' => 'required|string|in:individual,whole_place,both',
            'description' => 'required|string|max:2000',
            'rules_and_regulations' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'images' => 'nullable|array|min:1|max:7',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'whole_capacity' => $this->facilityTypeRequiresWholeCapacity($request) ? 'required|numeric|min:1' : 'nullable',
            'requirements' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'facility_attributes_json' => 'nullable|string',
        ];

        $messages = [
            'image.image' => 'The main image must be an image file.',
            'image.mimes' => 'The main image must be a file of type: jpeg, png, jpg, gif, svg.',
            'images.min' => 'You must upload at least 5 gallery images.',
            'images.max' => 'You cannot upload more than 7 gallery images.',
            'images.*.max' => 'Each gallery image may not be greater than 10MB.',
            'images.*.image' => 'All gallery images must be image files.',
            'images.*.mimes' => 'All gallery images must be files of type: jpeg, png, jpg, gif, svg.',
            'requirements.file' => 'The requirements must be a file.',
            'requirements.mimes' => 'The requirements file must be a file of type: pdf, doc, docx.',
            'requirements.max' => 'The requirements file may not be greater than 2MB.',
        ];

        switch ($request->facility_type) {
            case 'individual':
                $rules['facility_attributes_json'] = 'required|string';
                $rules['facility_attributes.*.room_name'] = 'required|string|max:255';
                $rules['facility_attributes.*.capacity'] = 'required|numeric|min:1|max:50';
                $rules['facility_attributes.*.sex_restriction'] = 'required|string|in:male,female,all';
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
                $rules['facility_selection_both'] = 'required|in:whole,room';
                $hasIndividualRooms = collect($facilityAttributes)->contains(function ($a) {
                    return !empty($a['room_name']) || !empty($a['capacity']);
                });
                $hasWholeCapacity = $request->filled('whole_capacity') && $request->whole_capacity > 0;
                if ($hasIndividualRooms) {
                    $rules['facility_attributes_json'] = 'required|string';
                    $rules['facility_attributes.*.room_name'] = 'required|string|max:255';
                    $rules['facility_attributes.*.capacity'] = 'required|numeric|min:1|max:50';
                    $rules['facility_attributes.*.sex_restriction'] = 'required|string|in:male,female,all';
                    $messages['facility_attributes.*.room_name.required'] = 'Room Name is required when providing individual rooms.';
                    $messages['facility_attributes.*.capacity.required'] = 'Capacity is required when providing individual rooms.';
                    $messages['facility_attributes.*.sex_restriction.required'] = 'Sex Restriction is required when providing individual rooms.';
                } elseif ($hasWholeCapacity) {
                    $rules['whole_capacity'] = 'required|numeric|min:1';
                    $messages['whole_capacity.required'] = 'Whole Capacity is required when not providing individual rooms.';
                } else {
                    $rules['facility_attributes_json'] = 'required_without:whole_capacity|string';
                    $rules['whole_capacity'] = 'required_without:facility_attributes_json|numeric|min:1';
                    $messages['facility_attributes_json.required_without'] = 'Either individual rooms or whole capacity must be provided for Both Type.';
                    $messages['whole_capacity.required_without'] = 'Either individual rooms or whole capacity must be provided for Both Type.';
                }
                break;
        }

        $validated = $request->validate($rules, $messages);
        try {
            $current_timestamp = now()->timestamp;

            if ($request->hasFile('requirements')) {
                $requirementsFile = $request->file('requirements');
                $requirementsFileName = $current_timestamp . '-requirements.' . $requirementsFile->getClientOriginalExtension();

                $destinationPath = storage_path('app/public/facilities/');
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }
                $requirementsFile->move($destinationPath, $requirementsFileName);
                $facility->requirements = $requirementsFileName;
            }

            $updateData = [
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'facility_type' => $validated['facility_type'],
                'description' => $validated['description'],
                'rules_and_regulations' => $validated['rules_and_regulations'],
                'created_by' => Auth::id(),
            ];

            if ($validated['facility_type'] === 'both') {
                $updateData['facility_selection_both'] = $validated['facility_selection_both'];
            }
            $facility->update($updateData);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $current_timestamp . '.' . $image->extension();
                $imageProcessor->process($image, $imageName, [
                    ['path' => storage_path('app/public/facilities'), 'cover' => [689, 689, 'center']],
                    ['path' => storage_path('app/public/facilities/thumbnails'), 'resize' => [300, 300]],
                ]);
                $facility->image = 'facilities/' . $imageName;
            }

            $gallery_arr = [];
            $counter = 1;
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $gFileName = "{$current_timestamp}.{$counter}.{$file->extension()}";
                    $imageProcessor->process($file, $gFileName, [
                        ['path' => storage_path('app/public/facilities'), 'cover' => [689, 689, 'center']],
                        ['path' => storage_path('app/public/facilities/thumbnails'), 'resize' => [300, 300]],
                    ]);
                    $gallery_arr[] = 'facilities/' . $gFileName;
                    $counter++;
                }
                $facility->images = implode(',', $gallery_arr);
            }

            $facility->save();
            $this->syncFacilityAttributes($facility, $facilityAttributes);
            $this->syncPrices($facility, $prices);

            if (in_array($facility->facility_type, ['whole_place', 'both'])) {
                $selectedDiscountIds = collect(explode(',', $request->input('selected_discounts', '')))
                    ->filter()
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();
                $facility->discounts()->sync($selectedDiscountIds);
            } else {
                $facility->discounts()->detach();
            }
            $selectedAddonIds = collect(explode(',', $request->input('selected_addons', '')))
                ->filter()
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            Addon::where('facility_id', $facility->id)->update(['facility_id' => null]);

            Addon::whereIn('id', $selectedAddonIds)->update(['facility_id' => $facility->id]);


            return redirect()->route('admin.facilities.index')->with('success', 'Facility updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['general' => 'An error occurred while updating the facility. Please try again.'])->withInput();
        }
    }
    private function syncFacilityAttributes(Facility $facility, array $facilityAttributes)
    {
        $facilityType = $facility->facility_type;
        $wholeCapacityValue = request()->input('whole_capacity');

        $wholeCapacityAttributes = collect($facilityAttributes)->where('whole_capacity', '!=', null);
        $individualRoomAttributes = collect($facilityAttributes)->where('whole_capacity', null);

        if ($wholeCapacityAttributes->isNotEmpty()) {
            foreach ($wholeCapacityAttributes as $attr) {
                if (!empty($attr['id'])) {
                    $model = FacilityAttribute::find($attr['id']);
                    if ($model) {
                        $model->update([
                            'whole_capacity' => $attr['whole_capacity'],
                            'room_name' => null,
                            'capacity' => null,
                            'sex_restriction' => null,
                        ]);
                    }
                } else {
                    $new = FacilityAttribute::create([
                        'facility_id' => $facility->id,
                        'room_name' => null,
                        'capacity' => null,
                        'sex_restriction' => null,
                        'whole_capacity' => $attr['whole_capacity'],
                    ]);
                }
            }
            if ($facilityType === 'both' || $facilityType === 'whole_place') {
                $deletedIndividualRooms = $facility->facilityAttributes()->whereNotNull('capacity')->delete();
                \Log::info('Deleted individual room records when whole_capacity exists', ['deleted_count' => $deletedIndividualRooms]);
            }

            return;
        }


        if ($individualRoomAttributes->isNotEmpty()) {

            if (in_array($facilityType, ['both', 'individual'])) {
                $deletedWholeCapacity = $facility->facilityAttributes()->whereNotNull('whole_capacity')->delete();
                \Log::info('Deleted whole_capacity records when switching to individual rooms', ['deleted_count' => $deletedWholeCapacity]);
            }

            $existingIds = $facility->facilityAttributes()->whereNotNull('capacity')->pluck('id')->toArray();
            $processedIds = [];

            foreach ($individualRoomAttributes as $attr) {
                if (!empty($attr['id'])) {
                    $model = FacilityAttribute::find($attr['id']);
                    if ($model) {
                        $model->update([
                            'room_name' => $attr['room_name'] ?? null,
                            'capacity' => $attr['capacity'] ?? null,
                            'sex_restriction' => $attr['sex_restriction'] ?? null,
                            'whole_capacity' => null,
                        ]);
                        $processedIds[] = $model->id;
                        \Log::info('Updated individual room record', ['id' => $attr['id'], 'room_name' => $attr['room_name']]);
                    }
                } else {
                    $new = FacilityAttribute::create([
                        'facility_id' => $facility->id,
                        'room_name' => $attr['room_name'] ?? null,
                        'capacity' => $attr['capacity'] ?? null,
                        'sex_restriction' => $attr['sex_restriction'] ?? null,
                        'whole_capacity' => null,
                    ]);
                    $processedIds[] = $new->id;
                    \Log::info('Created new individual room record', ['id' => $new->id, 'room_name' => $attr['room_name']]);
                }
            }


            $toDelete = array_diff($existingIds, $processedIds);
            if (!empty($toDelete)) {
                FacilityAttribute::destroy($toDelete);
                \Log::info('Deleted unused individual room records', ['deleted_ids' => $toDelete]);
            }

            return;
        }

        \Log::warning('No facility attributes provided for sync', [
            'facility_id' => $facility->id,
            'facility_type' => $facilityType,
            'whole_capacity_input' => $wholeCapacityValue
        ]);


        if ($wholeCapacityValue && in_array($facilityType, ['both', 'whole_place'])) {
            $existing = $facility->facilityAttributes()->whereNotNull('whole_capacity')->first();
            if ($existing) {
                $existing->update(['whole_capacity' => $wholeCapacityValue]);
                \Log::info('Emergency update of existing whole_capacity record', ['whole_capacity' => $wholeCapacityValue]);
            } else {
                FacilityAttribute::create([
                    'facility_id' => $facility->id,
                    'room_name' => null,
                    'capacity' => null,
                    'sex_restriction' => null,
                    'whole_capacity' => $wholeCapacityValue,
                ]);
                \Log::info('Emergency creation of whole_capacity record', ['whole_capacity' => $wholeCapacityValue]);
            }
        }
    }

    private function syncPrices(Facility $facility, array $prices)
    {
        $existingIds = $facility->prices()->pluck('id')->toArray();
        $processedIds = [];

        foreach ($prices as $price) {
            $data = [
                'name' => $price['priceName'] ?? $price['name'],
                'value' => $price['priceValue'] ?? $price['value'],
                'price_type' => $price['priceType'] ?? 'individual',
                'is_based_on_days' => $price['isBasedOnDays'] == 1,
                'is_there_a_quantity' => $price['isThereAQuantity'] == 1,
                'date_from' => $price['isBasedOnDays'] == 1 ? $price['dateFrom'] : null,
                'date_to' => $price['isBasedOnDays'] == 1 ? $price['dateTo'] : null,
            ];

            if (!empty($price['id'])) {
                $model = Price::find($price['id']);
                if ($model) {
                    $model->update($data);
                    $processedIds[] = $model->id;
                }
            } else {
                $new = $facility->prices()->create($data);
                $processedIds[] = $new->id;
            }
        }

        $toDelete = array_diff($existingIds, $processedIds);
        Price::destroy($toDelete);
    }
    public function archivedFacilities($id)
    {
        try {
            $facility = Facility::findOrFail($id);
            $facility->archived = 1;
            $facility->archived_at = \Carbon\Carbon::now();
            $facility->save();
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
        $archivedFacilities = Facility::where('archived', 1)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        $facilities = Facility::all();
        return view('admin.facilities.archive.index', compact('archivedFacilities', 'facilities'));
    }


    public function facilityDashboard()
    {
        $dashboardData = [
            'total_reservations' => Payment::count(),
            'completed_reservations' => Payment::where('status', 'completed')->count(),
            'pending_reservations' => Payment::where('status', 'pending')->count(),
            'canceled_reservations' => Payment::where('status', 'canceled')->count(),
            'active_facilities' => Facility::where('archived', 0)->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('total_price'),
        ];

        $reservations = Payment::with([
            'user',
            'availability.facility',
            'availability.facilityAttribute',
            'transactionReservations.availability'
        ])
            ->latest()
            ->take(10)
            ->get();

        $reservations->each(function ($payment) {
            if ($payment->availability) {
                $relatedAvailabilities = Availability::whereIn(
                    'id',
                    TransactionReservation::where('payment_id', $payment->id)
                        ->pluck('availability_id')
                )->orderBy('date_from')->get();

                $payment->grouped_availabilities = $relatedAvailabilities;
            }
        });

        // Gender Data (unchanged)
        $genderData = Payment::selectRaw('users.sex as gender, COUNT(DISTINCT payments.id) as count')
            ->join('users', 'payments.user_id', '=', 'users.id')
            ->whereNotNull('users.sex')
            ->groupBy('users.sex')
            ->get();

        $genderSeries = $genderData->pluck('count')->toArray();
        $genderLabels = $genderData->pluck('gender')->map(function ($item) {
            return ucfirst($item);
        })->toArray();

        // Department Data - Using college code
        $departmentData = Payment::selectRaw('colleges.code as college_code, COUNT(DISTINCT payments.id) as count')
            ->join('users', 'payments.user_id', '=', 'users.id')
            ->join('colleges', 'users.college_id', '=', 'colleges.id')
            ->whereNotNull('users.college_id')
            ->groupBy('colleges.code', 'colleges.id')
            ->get();

        $departmentSeries = $departmentData->pluck('count')->toArray();
        $departmentLabels = $departmentData->pluck('college_code')->toArray();

        // College Data - Using course name instead of code
        $collegeData = Payment::selectRaw('courses.name as course_name, COUNT(DISTINCT payments.id) as count')
            ->join('users', 'payments.user_id', '=', 'users.id')
            ->join('courses', 'users.course_id', '=', 'courses.id')
            ->whereNotNull('users.course_id')
            ->groupBy('courses.name', 'courses.id')
            ->get();

        $collegeSeries = $collegeData->pluck('count')->toArray();
        $collegeLabels = $collegeData->pluck('course_name')->toArray();

        // Role Data (unchanged)
        $roleData = Payment::selectRaw('users.role as role, COUNT(DISTINCT payments.id) as count')
            ->join('users', 'payments.user_id', '=', 'users.id')
            ->whereNotNull('users.role')
            ->groupBy('users.role')
            ->get();

        $roleSeries = $roleData->pluck('count')->toArray();
        $roleLabels = $roleData->pluck('role')->map(function ($item) {
            return ucfirst(str_replace('-', ' ', $item));
        })->toArray();

        return view('admin.facilities.dashboard', [
            'dashboardData' => $dashboardData,
            'reservations' => $reservations,
            'gender' => [
                'series' => $genderSeries,
                'labels' => $genderLabels
            ],
            'department' => [
                'series' => $departmentSeries,
                'labels' => $departmentLabels
            ],
            'college' => [
                'series' => $collegeSeries,
                'labels' => $collegeLabels
            ],
            'role' => [
                'series' => $roleSeries,
                'labels' => $roleLabels
            ]
        ]);
    }




    public function analytics()
    {
        // Gender distribution
        $genderData = TransactionReservation::with('user')
            ->selectRaw('users.sex as gender, COUNT(*) as count')
            ->join('users', 'transaction_reservations.user_id', '=', 'users.id')
            ->groupBy('users.sex')
            ->get();

        $genderSeries = $genderData->pluck('count')->toArray();
        $genderLabels = $genderData->pluck('gender')->map(function ($item) {
            return ucfirst($item);
        })->toArray();

        // Department distribution
        $departmentData = TransactionReservation::with('user')
            ->selectRaw('users.department as department, COUNT(*) as count')
            ->join('users', 'transaction_reservations.user_id', '=', 'users.id')
            ->whereNotNull('users.department')
            ->groupBy('users.department')
            ->get();

        $departmentSeries = $departmentData->pluck('count')->toArray();
        $departmentLabels = $departmentData->pluck('department')->toArray();

        // College distribution (assuming course represents college)
        $collegeData = TransactionReservation::with('user')
            ->selectRaw('users.course as college, COUNT(*) as count')
            ->join('users', 'transaction_reservations.user_id', '=', 'users.id')
            ->whereNotNull('users.course')
            ->groupBy('users.course')
            ->get();

        $collegeSeries = $collegeData->pluck('count')->toArray();
        $collegeLabels = $collegeData->pluck('college')->toArray();

        // Role distribution
        $roleData = TransactionReservation::with('user')
            ->selectRaw('users.role as role, COUNT(*) as count')
            ->join('users', 'transaction_reservations.user_id', '=', 'users.id')
            ->groupBy('users.role')
            ->get();

        $roleSeries = $roleData->pluck('count')->toArray();
        $roleLabels = $roleData->pluck('role')->map(function ($item) {
            return ucfirst(str_replace('-', ' ', $item));
        })->toArray();

        return view('admin.facilities.analytics', [
            'gender' => [
                'series' => $genderSeries,
                'labels' => $genderLabels
            ],
            'department' => [
                'series' => $departmentSeries,
                'labels' => $departmentLabels
            ],
            'college' => [
                'series' => $collegeSeries,
                'labels' => $collegeLabels
            ],
            'role' => [
                'series' => $roleSeries,
                'labels' => $roleLabels
            ]
        ]);
    }
}
