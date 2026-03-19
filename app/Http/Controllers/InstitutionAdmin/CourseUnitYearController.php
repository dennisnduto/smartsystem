<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\{CourseUnitYear, Course, Department, Unit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CourseUnitYearController extends Controller
{
    public function index(Request $request)
    {
        $institution = Auth::user()->institution;

        $query = CourseUnitYear::query()
            ->with(['course.department', 'unit'])
            ->whereHas('course.department', function($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            })
            ->orderByDesc('id');

        if ($request->filled('department_id')) {
            $query->whereHas('course', function($q) use ($request) {
                $q->where('department_id', $request->integer('department_id'));
            });
        }
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->integer('course_id'));
        }
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->input('academic_year'));
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->input('semester'));
        }

        $mappings = $query->paginate(15)->withQueryString();

        $departments = $institution->departments()->with('courses')->get();
        $courses = $departments->pluck('courses')->flatten();

        return view('institution-admin.course-unit-years.index', compact('mappings', 'departments', 'courses'));
    }

    public function create()
    {
        $this->authorize('create', CourseUnitYear::class);
        $institution = Auth::user()->institution;
        if ($institution->departments()->withCount('courses')->get()->sum('courses_count') === 0) {
            return redirect()->route('institution-admin.courses.create')
                ->with('error', 'Please create a Course/Program first before mapping units to years.');
        }
        $departments = $institution->departments()->with('courses')->get();
        $courses = $departments->pluck('courses')->flatten();
        $units = Unit::where('institution_id', $institution->id)
            ->orderBy('name')
            ->get();
        $years = ['Y1' => 'Year 1','Y2' => 'Year 2','Y3' => 'Year 3','Y4' => 'Year 4','Y5' => 'Year 5'];

        return view('institution-admin.course-unit-years.create', compact('departments','courses','units','years'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', CourseUnitYear::class);
        $institution = Auth::user()->institution;

        $request->validate([
            'course_id' => ['required','exists:courses,id'],
            'unit_id' => ['required','exists:units,id'],
'academic_year' => ['required', Rule::in(['Y1','Y2','Y3','Y4','Y5'])],
            'semester' => ['required', Rule::in(['S1','S2'])],
        ]);

        // Ensure course belongs to this institution
        $course = Course::where('id', $request->course_id)
            ->whereHas('department', fn($q) => $q->where('institution_id', $institution->id))
            ->firstOrFail();

        // Prevent duplicates
        $exists = CourseUnitYear::where('course_id', $course->id)
            ->where('unit_id', $request->unit_id)
            ->where('academic_year', $request->academic_year)
            ->where('semester', $request->semester)
            ->exists();
        if ($exists) {
            return back()->withErrors(['academic_year' => 'This unit is already mapped to that course and year.'])->withInput();
        }

        CourseUnitYear::create([
            'course_id' => $course->id,
            'unit_id' => $request->unit_id,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
        ]);

        return redirect()->route('institution-admin.course-unit-years.index')->with('success', 'Mapping created.');
    }

    public function edit(CourseUnitYear $course_unit_year)
    {
        $this->authorize('update', $course_unit_year);
        $institution = Auth::user()->institution;

        // extra guard: ensure within institution
        abort_unless(optional($course_unit_year->course->department)->institution_id === $institution->id, 403);

        $departments = $institution->departments()->with('courses')->get();
        $courses = $departments->pluck('courses')->flatten();
        $units = Unit::where('institution_id', $institution->id)
            ->orderBy('name')
            ->get();
        $years = ['Y1' => 'Year 1','Y2' => 'Year 2','Y3' => 'Year 3','Y4' => 'Year 4','Y5' => 'Year 5'];
        $semesters = ['S1' => 'Semester 1', 'S2' => 'Semester 2'];

        return view('institution-admin.course-unit-years.edit', [
            'mapping' => $course_unit_year,
            'departments' => $departments,
            'courses' => $courses,
            'units' => $units,
            'years' => $years,
            'semesters' => $semesters,
        ]);
    }

    public function update(Request $request, CourseUnitYear $course_unit_year)
    {
        $this->authorize('update', $course_unit_year);
        $institution = Auth::user()->institution;
        abort_unless(optional($course_unit_year->course->department)->institution_id === $institution->id, 403);

        $request->validate([
            'course_id' => ['required','exists:courses,id'],
            'unit_id' => ['required','exists:units,id'],
'academic_year' => ['required', Rule::in(['Y1','Y2','Y3','Y4','Y5'])],
            'semester' => ['required', Rule::in(['S1','S2'])],
        ]);

        // Ensure course belongs to this institution
        $course = Course::where('id', $request->course_id)
            ->whereHas('department', fn($q) => $q->where('institution_id', $institution->id))
            ->firstOrFail();

        $duplicate = CourseUnitYear::where('course_id', $course->id)
            ->where('unit_id', $request->unit_id)
            ->where('academic_year', $request->academic_year)
            ->where('semester', $request->semester)
            ->where('id', '!=', $course_unit_year->id)
            ->exists();
        if ($duplicate) {
            return back()->withErrors(['academic_year' => 'This unit is already mapped to that course and year.'])->withInput();
        }

        $course_unit_year->update([
            'course_id' => $course->id,
            'unit_id' => $request->unit_id,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
        ]);

        return redirect()->route('institution-admin.course-unit-years.index')->with('success', 'Mapping updated.');
    }

    public function destroy(CourseUnitYear $course_unit_year)
    {
        $this->authorize('delete', $course_unit_year);
        $course_unit_year->delete();
        return redirect()->route('institution-admin.course-unit-years.index')->with('success', 'Mapping deleted.');
    }
}
