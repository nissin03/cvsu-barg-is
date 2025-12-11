<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $positions = Position::query()
            ->when(
                $search,
                fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
            )
            ->orderBy('name')
            ->paginate(10);

        return view('admin.positions.index', compact('positions'));
    }

    public function create()
    {
        return view('admin.positions.create');
    }

    public function store(Request $request)
    {
        if ($request->has('positions')) {
            $validated = $request->validate([
                'positions' => ['required', 'array', 'min:1'],
                'positions.*.name' => ['required', 'string', 'max:255'],
                'positions.*.code' => ['required', 'string', 'max:50'],
            ]);

            foreach ($validated['positions'] as $data) {
                Position::create($data);
            }

            return redirect()->route('positions.index')
                ->with('success', 'Positions created successfully.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
        ]);

        Position::create($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position created successfully.');
    }

    public function edit(Position $position)
    {
        return view('admin.positions.edit', compact('position'));
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
        ]);

        $position->update($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        // Archive (soft delete)
        $position->delete();

        return redirect()->route('positions.index')
            ->with('success', 'Position archived successfully.');
    }

    public function archive(Request $request)
    {
        $search = $request->input('search');

        $positions = Position::onlyTrashed()
            ->when(
                $search,
                fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
            )
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('admin.positions.archive', compact('positions'));
    }

    public function restore($id)
    {
        $position = Position::onlyTrashed()->findOrFail($id);
        $position->restore();

        return redirect()->route('positions.archive')
            ->with('success', 'Position restored successfully.');
    }

    public function forceDelete($id)
    {
        $position = Position::onlyTrashed()->findOrFail($id);
        $position->forceDelete();

        return redirect()->route('positions.archive')
            ->with('success', 'Position permanently deleted.');
    }
}
