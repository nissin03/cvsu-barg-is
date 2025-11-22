<?php

namespace App\Http\Controllers;

use App\Models\Signature;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $signatures = Signature::where('is_archived', false)
            ->orderBy('order_by')
            ->paginate(10);

        return view('admin.signature.index', compact('signatures'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.signature.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'position'    => 'required|string|max:255',
            'category'    => 'required|in:facility,product',
            'report_type' => 'required|in:sales,product,inventory,users,all',
            'label'       => 'required|string|max:255',
            'is_active'   => 'nullable|boolean',
            'is_archived' => 'nullable|boolean',
        ]);

        $degreeList = [
            'phd' => 'PhD',
            'ma'  => 'MA',
            'ba'  => 'BA',
            'mba' => 'MBA',
            'cpa' => 'CPA',
            'md'  => 'MD',
            'dmd' => 'DMD',
            'llb' => 'LLB',
            'llm' => 'LLM',
            'edd' => 'EdD',
            'jd'  => 'JD',
            'rn'  => 'RN',
            'ms'  => 'MS',
            'bs'  => 'BS',
            'bsc' => 'BSc',
            'msc' => 'MSc',
            'dpt' => 'DPT',
            'drph' => 'DrPH',
            'dds' => 'DDS',
            'do'  => 'DO',
            'maed' => 'MAEd',
            'med' => 'MEd',
            'bfa' => 'BFA',
            'mpa' => 'MPA',
            'mph' => 'MPH',
            'msn' => 'MSN',
            'bsa' => 'BSA',
        ];

        $name = Str::of($validated['name'])->trim();

        if ($name->contains(',')) {
            $main = Str::of($name->before(','))->trim()->upper();
            $degree = Str::of($name->after(','))->trim();

            $degreeKey = strtolower((string) $degree);
            $degreeFormatted = $degreeList[$degreeKey] ?? Str::upper((string) $degree);

            $validated['name'] = "$main, $degreeFormatted";
        } else {
            $validated['name'] = $name->upper();
        }

        $nextOrder = Signature::where('category', $validated['category'])
            ->where('report_type', $validated['report_type'])
            ->max('order_by');

        $validated['order_by'] = $nextOrder ? $nextOrder + 1 : 1;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_archived'] = $request->has('is_archived') ? 1 : 0;

        Signature::create($validated);

        return redirect()
            ->route('admin.signatures.index')
            ->with('success', 'Signature created successfully.');
    }





    /**
     * Display the specified resource.
     */
    public function show(Signature $signature)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Signature $signature)
    {
        return view('admin.signature.edit', compact('signature'));
    }

    public function update(Request $request, Signature $signature)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'category' => 'required|in:facility,product',
            'report_type' => 'required|in:sales,product,inventory,users,all',
            'label' => 'required|string|max:255',
            'order_by' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_archived' => 'boolean',
        ]);

        $degreeList = [
            'phd' => 'PhD',
            'ma'  => 'MA',
            'ba'  => 'BA',
            'mba' => 'MBA',
            'cpa' => 'CPA',
            'md'  => 'MD',
            'dmd' => 'DMD',
            'llb' => 'LLB',
            'llm' => 'LLM',
            'edd' => 'EdD',
            'jd'  => 'JD',
            'rn'  => 'RN',
            'ms'  => 'MS',
            'bs'  => 'BS',
            'bsc' => 'BSc',
            'msc' => 'MSc',
            'dpt' => 'DPT',
            'drph' => 'DrPH',
            'dds' => 'DDS',
            'do'  => 'DO',
            'maed' => 'MAEd',
            'med' => 'MEd',
            'bfa' => 'BFA',
            'mpa' => 'MPA',
            'mph' => 'MPH',
            'msn' => 'MSN',
            'bsa' => 'BSA',
        ];

        $name = Str::of($validated['name'])->trim();

        if ($name->contains(',')) {
            $main = Str::of($name->before(','))->trim()->upper();
            $degree = Str::of($name->after(','))->trim();
            $degreeKey = strtolower((string) $degree);
            $degreeFormatted = $degreeList[$degreeKey] ?? Str::upper((string) $degree);
            $validated['name'] = "$main, $degreeFormatted";
        } else {
            $validated['name'] = $name->upper();
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_archived'] = $request->has('is_archived') ? 1 : 0;

        $signature->update($validated);

        return redirect()
            ->route('admin.signatures.index')
            ->with('success', 'Signature updated successfully');
    }


    public function destroy($id)
    {
        $signature = Signature::findOrFail($id);
        $signature->delete();

        return redirect()->route('admin.signatures.index')
            ->with('success', 'Signature archived successfully.');
    }

    public function archive(Request $request)
    {
        $query = Signature::onlyTrashed();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%")
                ->orWhere('label', 'like', "%{$search}%");
        }

        $signatures = $query->latest('deleted_at')->paginate(10);

        return view('admin.signature.archive', compact('signatures'));
    }

    public function restore($id)
    {
        $signature = Signature::onlyTrashed()->findOrFail($id);
        $signature->restore();

        return redirect()->route('admin.signatures.archive')
            ->with('success', 'Signature restored successfully.');
    }

    public function forceDelete($id)
    {
        $signature = Signature::onlyTrashed()->findOrFail($id);
        $signature->forceDelete();

        return redirect()->route('admin.signatures.archive')
            ->with('success', 'Signature permanently deleted.');
    }
}
