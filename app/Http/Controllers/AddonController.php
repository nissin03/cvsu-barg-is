<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AddonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Addon::query();
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $addons = $query->latest()->paginate(10);

        return view('admin.addons.index', compact('addons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.addons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('addons', 'name'),
            ],
            'price_type'  => 'required|in:per_unit,flat_rate,per_night',
            'description' => 'nullable|string',
            'base_price'  => 'required|numeric|min:0',
            'capacity'    => 'nullable|integer|min:1',
            'show'        => 'required|in:both,staff',
        ]);

        $data['is_available']        = $request->boolean('is_available');
        $data['is_based_on_quantity'] = $request->boolean('is_based_on_quantity');
        $data['is_refundable']       = $request->boolean('is_refundable');
        $data['user_id']             = Auth::id();

        switch ($data['price_type']) {
            case 'per_unit':
            case 'per_night':
                $data['is_refundable'] = false;

                if ($data['is_based_on_quantity']) {
                    $request->validate([
                        'capacity' => 'required|integer|min:1'
                    ]);
                    $data['capacity'] = $request->capacity;
                } else {
                    $data['capacity'] = null;
                }
                break;

            case 'flat_rate':
                $data['is_based_on_quantity'] = false;
                $data['capacity'] = null;
                break;
        }

        Addon::create($data);
        return redirect()->route('admin.addons')->with('success', 'Addon created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Addon $addon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Addon $addon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Addon $addon)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Addon $addon)
    {
        //
    }
}
