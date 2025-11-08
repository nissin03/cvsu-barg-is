<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\College;
use Illuminate\Http\Request;

class AdminCollegeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $colleges = College::when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        })
            ->orderBy('name')
            ->paginate(15);

        return view('admin.college.index', compact('colleges'));
    }

    public function create()
    {
        return view('admin.college.create');
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'colleges.*.name' => 'required|string|max:255|unique:colleges,name',
            'colleges.*.code' => 'required|string|max:10|unique:colleges,code',
        ], [
            'colleges.*.name.unique' => 'The college name ":input" already exists.',
            'colleges.*.code.unique' => 'The college code ":input" already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $colleges = [];
            $collegeNames = [];

            foreach ($request->colleges as $collegeData) {
                // Check if college already exists (additional safety check)
                if (College::where('name', $collegeData['name'])->exists()) {
                    continue; // Skip this college
                }

                $college = College::create([
                    'name' => $collegeData['name'],
                    'code' => strtoupper($collegeData['code'])
                ]);

                $colleges[] = $college;
                $collegeNames[] = $collegeData['name'];
            }

            if (empty($colleges)) {
                return response()->json([
                    'success' => false,
                    'message' => 'All colleges you tried to add already exist in the database.'
                ], 422);
            }

            $message = count($collegeNames) > 1
                ? 'Colleges added successfully: ' . implode(', ', $collegeNames)
                : 'College added successfully!';

            return response()->json([
                'success' => true,
                'message' => $message,
                'colleges' => $colleges
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving colleges: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $college = College::findOrFail($id);
        return view('admin.college.edit', compact('college'));
    }

    public function update(Request $request, $id)
    {
        $college = College::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:colleges,name,' . $college->id,
            'code' => 'required|string|max:10|unique:colleges,code,' . $college->id,
        ], [
            'name.unique' => 'The college name ":input" already exists.',
            'code.unique' => 'The college code ":input" already exists.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $college->update([
                'name' => $request->name,
                'code' => strtoupper($request->code)
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'College updated successfully!',
                    'college' => $college
                ]);
            }

            return redirect()->route('admin.colleges.index')
                ->with('success', 'College updated successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating college: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error updating college: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function archive(Request $request)
    {
        $search = $request->input('search');

        $colleges = College::onlyTrashed()
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            })
            ->paginate(10);

        return view('admin.college.archive', compact('colleges'));
    }

    public function destroy($id)
    {
        $college = College::findOrFail($id);
        $college->delete();

        return redirect()->route('admin.colleges.index')
            ->with('success', '');
    }

    public function restore($id)
    {
        $college = College::onlyTrashed()->findOrFail($id);
        $college->restore();

        return redirect()->route('admin.colleges.archive')
            ->with('success', 'College restored successfully.');
    }

    public function forceDelete($id)
    {
        $college = College::onlyTrashed()->findOrFail($id);
        $college->forceDelete();

        return redirect()->route('admin.colleges.archive')
            ->with('success', 'College permanently deleted.');
    }
}
