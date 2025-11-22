<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Facility;
use Illuminate\Http\Request;
use App\Models\AddonReservation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddonsController extends Controller
{
    public function index(Request $request)
    {
        $query = Addon::with('user', 'facility');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('facility', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Facility filter
        if ($request->filled('facility')) {
            $query->where('facility_id', $request->facility);
        }

        if ($request->filled('price_type')) {
            $query->where('price_type', $request->price_type);
        }

        if ($request->filled('availability')) {
            $query->where('is_available', $request->availability === 'available');
        }

        if ($request->filled('refundable')) {
            $query->where('is_refundable', $request->refundable === 'yes');
        }
        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->billing_cycle);
        }
        if ($request->filled('quantity_based')) {
            $query->where('is_based_on_quantity', $request->quantity_based === 'yes');
        }

        $sortBy = $request->input('sort_by', 'newest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'ASC');
                break;
            case 'name_asc':
                $query->orderBy('name', 'ASC');
                break;
            case 'name_desc':
                $query->orderBy('name', 'DESC');
                break;
            case 'price_low':
                $query->orderBy('base_price', 'ASC');
                break;
            case 'price_high':
                $query->orderBy('base_price', 'DESC');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'DESC');
                break;
        }

        $addons = $query->paginate(10)->withQueryString();
        $count = $addons->total();

        $facilities = Facility::orderBy('name')->get();


        if ($request->ajax()) {
            return response()->json([
                'addons' => view('partials._addons-table', compact('addons'))->render(),
                'pagination' => view('partials._addons-pagination', compact('addons'))->render(),
                'count' => $count
            ]);
        }


        return view('admin.add-ons.index',  compact('addons', 'facilities'));
    }

    public function create()
    {
        return view('admin.add-ons.create');
    }

    public function getAddonNames()
    {
        $names = Addon::pluck('name')->toArray();
        return response()->json(['names' => $names]);
    }

    public function store(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'price_type' => 'required|in:per_unit,flat_rate,per_night,per_item',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:per_day,per_contract',
            'show' => 'required|in:both',
            'is_available' => 'sometimes|boolean',
            'is_refundable' => 'sometimes|boolean',
            'is_based_on_quantity' => 'sometimes|boolean',
            'quantity' => 'nullable|integer|min:1',
        ];

        switch ($request->price_type) {
            case 'per_unit':
                $validationRules['capacity'] = 'required|integer|min:1';
                $validationRules['quantity_night'] = 'nullable';
                $validationRules['quantity_item'] = 'nullable';
                break;

            case 'per_night':
                $validationRules['capacity'] = 'nullable';
                $validationRules['quantity_night'] = 'nullable|integer|min:1';
                $validationRules['quantity_item'] = 'nullable';
                break;

            case 'per_item':
                $validationRules['capacity'] = 'nullable';
                $validationRules['quantity_night'] = 'nullable';
                $validationRules['quantity_item'] = 'required|integer|min:1';
                break;

            case 'flat_rate':
                $validationRules['capacity'] = 'nullable';
                $validationRules['quantity_night'] = 'nullable';
                $validationRules['quantity_item'] = 'nullable';
                break;
        }

        $validated = $request->validate($validationRules);

        $validated['is_available'] = $request->has('is_available');
        $validated['is_refundable'] = $request->has('is_refundable');
        $validated['is_based_on_quantity'] = $request->has('is_based_on_quantity');
        $validated['user_id'] = Auth::id();

        switch ($validated['price_type']) {
            case 'per_unit':
                $validated['is_based_on_quantity'] = false;
                $validated['is_refundable'] = false;

                break;

            case 'flat_rate':
                $validated['is_based_on_quantity'] = false;
                $validated['capacity'] = null;
                $validated['quantity'] = null;
                break;

            case 'per_night':
                $validated['is_refundable'] = false;
                $validated['capacity'] = null;
                $validated['is_based_on_quantity'] = true;
                break;

            case 'per_item':
                $validated['is_refundable'] = false;
                $validated['capacity'] = null;
                break;
        }

        unset($validated['quantity_night']);
        unset($validated['quantity_item']);

        $addon = Addon::create($validated);

        return redirect()->route('admin.addons')
            ->with('success', 'Addon created successfully.');
    }

    public function edit($id)
    {
        $addon = Addon::findOrFail($id);
        $facilities = Facility::all();
        return view('admin.add-ons.edit', compact('addon', 'facilities'));
    }

    public function update(Request $request, $id)
    {
        $addon = Addon::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_type' => 'required|in:per_unit,flat_rate,per_night,per_item',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:per_day,per_contract',
            'show' => 'required|in:both',
            'is_available' => 'sometimes|boolean',
            'is_refundable' => 'sometimes|boolean',
            'is_based_on_quantity' => 'sometimes|boolean',
            'capacity' => 'nullable|integer|min:1',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validated['price_type'] === 'per_item') {
            $validated['billing_cycle'] = 'per_contract';
        }

        switch ($validated['price_type']) {
            case 'per_unit':
                $validated['is_based_on_quantity'] = false;
                $validated['is_refundable'] = false;
                $validated['is_available'] = $request->has('is_available');
                $validated['capacity'] = $request->input('capacity', 1);
                $validated['quantity'] = 1;
                break;

            case 'flat_rate':
                $validated['is_based_on_quantity'] = false;
                $validated['capacity'] = null;
                $validated['quantity'] = null;
                $validated['is_available'] = $request->has('is_available');
                $validated['is_refundable'] = $request->has('is_refundable');
                break;

            case 'per_night':
                $validated['is_based_on_quantity'] = false;
                $validated['is_refundable'] = false;
                $validated['capacity'] = null;
                $validated['quantity'] = null;
                $validated['is_available'] = $request->has('is_available');
                break;

            case 'per_item':
                $validated['is_refundable'] = false;
                $validated['capacity'] = null;
                $validated['is_available'] = $request->has('is_available');
                $validated['is_based_on_quantity'] = $request->has('is_based_on_quantity');
                $validated['quantity'] = $request->input('quantity', 1);
                break;
        }

        $addon->update($validated);

        return redirect()->route('admin.addons')
            ->with('success', 'Addon updated successfully.');
    }

    public function destroy($id)
    {
        $addon = Addon::findOrFail($id);
        $addon->delete();

        return redirect()->route('admin.addons')
            ->with('success', 'Addon archived successfully.');
    }

    public function archive(Request $request)
    {
        $query = Addon::onlyTrashed()->with(['user', 'facility']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $addons = $query->latest('deleted_at')->paginate(10);

        return view('admin.add-ons.archive', compact('addons'));
    }

    public function restore($id)
    {
        $addon = Addon::onlyTrashed()->findOrFail($id);
        $addon->restore();

        return redirect()->route('admin.addons.archive')
            ->with('success', 'Addon restored successfully.');
    }

    public function forceDelete($id)
    {
        $addon = Addon::onlyTrashed()->findOrFail($id);
        $addon->forceDelete();

        return redirect()->route('admin.addons.archive')
            ->with('success', 'Addon permanently deleted.');
    }
}
