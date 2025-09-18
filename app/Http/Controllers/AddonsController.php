<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Facility;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddonsController extends Controller
{
    public function index(Request $request)
    {
        $query = Addon::with('user');
 
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        $addons = $query->latest()->paginate(10);
        
        return view('admin.add-ons.index', compact('addons'));
    }

    public function create()
    {
        $facilities = Facility::all();
        return view('admin.add-ons.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_type' => 'required|in:per_unit,flat_rate,per_night',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_based_on_quantity' => 'sometimes|boolean',
            'is_available' => 'sometimes|boolean',
            'show' => 'required|in:both,staff',
            'is_refundable' => 'sometimes|boolean',
            'capacity' => 'nullable|integer',
        ]);

        if ($validated['price_type'] === 'per_unit') {
            $validated['is_based_on_quantity'] = $request->has('is_based_on_quantity');
            $validated['is_refundable'] = false;

            if ($validated['is_based_on_quantity']) {
                $request->validate([
                    'capacity' => 'required|integer'
                ]);
                $validated['capacity'] = $request->capacity;
            } else {
                $validated['capacity'] = null;
            }
        } elseif ($validated['price_type'] === 'per_night') {
            $validated['is_based_on_quantity'] = $request->has('is_based_on_quantity');
            $validated['is_refundable'] = false;

            if ($validated['is_based_on_quantity']) {
                $request->validate([
                    'capacity' => 'required|integer'
                ]);
                $validated['capacity'] = $request->capacity;
            } else {
                $validated['capacity'] = null;
            }
        } else {
            $validated['is_based_on_quantity'] = false;
            $validated['is_refundable'] = $request->has('is_refundable');
            $validated['capacity'] = null;
        }

        $validated['is_available'] = $request->has('is_available');
        $validated['user_id'] = Auth::id();

        Addon::create($validated);

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
            'price_type' => 'required|in:per_unit,flat_rate,per_night',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_based_on_quantity' => 'sometimes|boolean',
            'is_available' => 'sometimes|boolean',
            'show' => 'required|in:both,staff',
            'is_refundable' => 'sometimes|boolean',
            'capacity' => 'nullable|integer',
        ]);

        if ($validated['price_type'] === 'per_unit') {
            $validated['is_based_on_quantity'] = $request->has('is_based_on_quantity');
            $validated['is_refundable'] = false;

            if ($validated['is_based_on_quantity']) {
                $request->validate([
                    'capacity' => 'required|integer|min:1'
                ]);
                $validated['capacity'] = $request->capacity;
            } else {
                $validated['capacity'] = null;
            }
        } elseif ($validated['price_type'] === 'per_night') {
            $validated['is_based_on_quantity'] = $request->has('is_based_on_quantity');
            $validated['is_refundable'] = false;

            if ($validated['is_based_on_quantity']) {
                $request->validate([
                    'capacity' => 'required|integer|min:1'
                ]);
                $validated['capacity'] = $request->capacity;
            } else {
                $validated['capacity'] = null;
            }
        } else {
            $validated['is_based_on_quantity'] = false;
            $validated['is_refundable'] = $request->has('is_refundable');
            $validated['capacity'] = null;
        }

        $validated['is_available'] = $request->has('is_available');

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
