<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::orderByDesc('active')->orderBy('name')->paginate(15);
        return view('admin.discounts.index', compact('discounts'));
    }

    public function create()
    {
        return view('admin.discounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'percent' => 'required|numeric|min:0|max:100',
            'applies_to' => 'required|in:all,venue_only',
            'requires_proof' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $validated['requires_proof'] = $request->boolean('requires_proof');
        $validated['active'] = $request->boolean('active');
        Discount::create($validated);
        return redirect()->route('discounts.index')->with('status', 'Discount created');
    }

    public function edit(Discount $discount)
    {
        return view('admin.discounts.edit', compact('discount'));
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'percent' => 'required|numeric|min:0|max:100',
            'applies_to' => 'required|in:all,venue_only',
            'requires_proof' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);
        $validated['requires_proof'] = $request->boolean('requires_proof');
        $validated['active'] = $request->boolean('active');
        $discount->update($validated);
        return redirect()->route('discounts.index')->with('status', 'Discount updated');
    }

    public function archive(Discount $discount)
    {
        $discount->active = false;
        $discount->save();
        return redirect()->route('discounts.index')->with('status', 'Discount archived');
    }

    public function archived()
    {
        $discounts = Discount::where('active', false)->orderBy('name')->paginate(15);
        return view('admin.discounts.archived', compact('discounts'));
    }

    public function restore(Discount $discount)
    {
        $discount->active = true;
        $discount->save();
        return redirect()->route('discounts.archived')->with('status', 'Discount restored');
    }

    public function restoreBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:discounts,id'
        ]);

        $restoredIds = [];

        foreach ($request->ids as $id) {
            $discount = Discount::find($id);
            if ($discount && !$discount->active) {
                $discount->active = true;
                $discount->save();
                $restoredIds[] = $id;
            }
        }

        $count = count($restoredIds);

        return response()->json([
            'status' => "{$count} discount" . ($count > 1 ? 's' : '') . " restored successfully",
            'restoredIds' => $restoredIds
        ]);
    }
}
