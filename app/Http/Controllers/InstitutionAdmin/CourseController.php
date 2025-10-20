<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Course, Department};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $institution = Auth::user()->institution;
        $courses = Course::whereHas('department', function($q) use ($institution) {
            $q->where('institution_id', $institution->id);
        })->with('department')->latest()->paginate(10);
        
        return view('institution-admin.courses.index', compact('courses', 'institution'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $institution = Auth::user()->institution;
        if ($institution->departments()->count() === 0) {
            return redirect()->route('institution-admin.departments.create')
                ->with('error', 'Please create a Department first before adding courses.');
        }
        $departments = $institution->departments;
        
        return view('institution-admin.courses.create', compact('institution', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $institution = Auth::user()->institution;
        
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'duration_years' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string|max:1000',
        ]);

        // Verify department belongs to institution
        $department = Department::where('id', $request->department_id)
            ->where('institution_id', $institution->id)
            ->firstOrFail();

        $course = Course::create([
            'department_id' => $department->id,
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'duration_years' => $request->duration_years,
            'description' => $request->description,
        ]);

        return redirect()->route('institution-admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $this->authorize('view', $course);
        
        $course->load('department');
        
        return view('institution-admin.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        
        $institution = Auth::user()->institution;
        $departments = $institution->departments;
        
        return view('institution-admin.courses.edit', compact('course', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        
        $institution = Auth::user()->institution;
        
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'duration_years' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string|max:1000',
        ]);

        // Verify department belongs to institution
        $department = Department::where('id', $request->department_id)
            ->where('institution_id', $institution->id)
            ->firstOrFail();

        $course->update([
            'department_id' => $department->id,
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'duration_years' => $request->duration_years,
            'description' => $request->description,
        ]);

        return redirect()->route('institution-admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        
        $course->delete();

        return redirect()->route('institution-admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
