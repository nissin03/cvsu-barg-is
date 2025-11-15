<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\College;
use Illuminate\Http\Request;

class AdminCourseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $courses = Course::with('college')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('courses.name', 'like', '%' . $search . '%')
                        ->orWhere('courses.code', 'like', '%' . $search . '%')
                        ->orWhereHas('college', function ($collegeQuery) use ($search) {
                            $collegeQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderBy('courses.name')
            ->paginate(10);

        return view('admin.course.index', compact('courses'));
    }

    public function create()
    {
        $colleges = College::all();
        return view('admin.course.create', compact('colleges'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'college_id' => 'required|exists:colleges,id'
        ]);

        Course::create($request->all());

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course)
    {
        $colleges = College::orderBy('name', 'asc')->get();
        return view('admin.course.edit', compact('course', 'colleges'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:courses,code,' . $course->id,
            'college_id' => 'required|exists:colleges,id'
        ]);

        $course->update($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course archived successfully.');
    }
    public function archive(Request $request)
    {
        $search = $request->input('search');

        $courses = Course::onlyTrashed()
            ->with(['college' => function ($query) {
                $query->withTrashed();
            }])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('college', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('admin.course.archive', compact('courses', 'search'));
    }

    /**
     * Restore an archived course
     */
    public function restore($id)
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->restore();

        return redirect()->route('admin.courses.archive')
            ->with('success', 'Course restored successfully.');
    }

    /**
     * Permanently delete a course
     */
    public function forceDelete($id)
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->forceDelete();

        return redirect()->route('admin.courses.archive')
            ->with('success', 'Course permanently deleted.');
    }
}
