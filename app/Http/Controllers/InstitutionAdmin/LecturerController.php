<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Department;
use App\Models\CourseUnitYear;
use App\Models\Lecturer as LecturerProfile;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LecturerController extends Controller
{
    public function index()
    {
        $institution = auth()->user()->institution;
        
        $lecturers = $institution->users()
            ->where('role', 'lecturer')
            ->with(['department', 'courseUnitYears.course'])
            ->paginate(15);
            
        return view('institution-admin.lecturers.index', compact('lecturers'));
    }

    public function create()
    {
        $institution = auth()->user()->institution;
        $departments = $institution->departments()->get();
        $courses = $institution->departments()->with('courses')->get()->pluck('courses')->flatten();
        $coursesForJs = $courses->map(function($course){
            return [
                'id' => $course->id,
                'name' => $course->name,
                'department' => optional($course->department)->name,
                'lab_required' => (bool)($course->lab_required ?? false),
            ];
        })->values();
        
        // Year-aware unit options per course from mappings
$courseUnitYears = CourseUnitYear::with(['course.department','unit'])
            ->whereHas('course.department', function($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            })->get();
        $cuyForJs = $courseUnitYears->filter(function($m) {
            return $m->unit !== null; // Only include mappings with valid units
        })->map(function($m) {
            return [
                'course_id' => $m->course_id,
                'unit_id' => $m->unit_id,
                'unit_code' => $m->unit->code ?? '',
                'unit_name' => $m->unit->name ?? '',
                'academic_year' => $m->academic_year,
                'semester' => $m->semester,
            ];
        })->values();

        // Fallback: all units list (for when mappings are missing) - scoped to institution
        $unitsForJs = Unit::where('institution_id', $institution->id)
            ->orderBy('code')
            ->get(['id','code','name','year_level'])
            ->map(function($u){
            return [
                'id' => $u->id,
                'code' => $u->code,
                'name' => $u->name,
                'year_level' => $u->year_level,
            ];
        });
        
        return view('institution-admin.lecturers.create', compact('departments', 'courses', 'coursesForJs', 'cuyForJs', 'unitsForJs'));
    }

    public function store(Request $request)
    {
        $institution = auth()->user()->institution;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
            'department_id' => 'required|exists:departments,id',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
'course_assignments' => 'array',
            'course_assignments.*.course_id' => 'required|exists:courses,id',
            'course_assignments.*.unit_id' => 'required|exists:units,id',
'course_assignments.*.academic_year' => 'required|in:Y1,Y2,Y3,Y4,Y5',
            'course_assignments.*.semester' => 'nullable|in:S1,S2',
            'course_assignments.*.is_lab_only' => 'boolean',
            'course_assignments.*.notes' => 'nullable|string|max:500'
        ]);

        // Verify department belongs to institution
        $department = Department::where('id', $request->department_id)
            ->where('institution_id', $institution->id)
            ->firstOrFail();

        // Create a matching Lecturer profile for FK consistency
$lecturerProfile = LecturerProfile::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $lecturer = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'lecturer',
            'employee_id' => $request->employee_id,
            'lecturer_id' => $lecturerProfile->id,
            'institution_id' => $institution->id,
            'department_id' => $request->department_id,
            'phone' => $request->phone,
            'email_verified_at' => now(),
        ]);

        // Attach unit-year assignments if provided
        if ($request->course_assignments) {
            $cuyData = [];
            foreach ($request->course_assignments as $assignment) {
                // Verify course belongs to institution and mapping exists for unit/year
                $course = Course::where('id', $assignment['course_id'])
                    ->whereHas('department', function($query) use ($institution) {
                        $query->where('institution_id', $institution->id);
                    })
                    ->first();
                if (!$course) { continue; }

                $cuy = CourseUnitYear::where('course_id', $assignment['course_id'])
                    ->where('unit_id', $assignment['unit_id'])
                    ->where('academic_year', $assignment['academic_year'])
                    ->first();
                if (!$cuy) {
                    // Create mapping on the fly if missing
                    $cuy = CourseUnitYear::firstOrCreate([
                        'course_id' => $assignment['course_id'],
                        'unit_id' => $assignment['unit_id'],
                        'academic_year' => $assignment['academic_year'],
                    ], [
                        'semester' => $assignment['semester'] ?? null,
                    ]);
                } else {
                    // Update semester if changed (allow clearing by setting to null)
                    $newSemester = !empty($assignment['semester']) ? $assignment['semester'] : null;
                    if ($cuy->semester !== $newSemester) {
                        $cuy->semester = $newSemester;
                        $cuy->save();
                    }
                }

                // Check if this unit is already assigned to another lecturer
                $existingAssignment = DB::table('course_unit_year_user')
                    ->where('course_unit_year_id', $cuy->id)
                    ->where('user_id', '!=', $lecturer->id)
                    ->first();

                if ($existingAssignment) {
                    $existingLecturer = DB::table('users')->where('id', $existingAssignment->user_id)->first();
                    $unit = DB::table('units')->where('id', $assignment['unit_id'])->first();
                    return back()->withErrors([
                        'course_assignments' => "Unit '{$unit->code} - {$unit->name}' in course '{$course->name}' is already assigned to lecturer '{$existingLecturer->name}'. Each unit can only be taught by one lecturer per course."
                    ])->withInput();
                }

                $cuyData[$cuy->id] = [
                    'is_lab_only' => $assignment['is_lab_only'] ?? false,
                    'notes' => $assignment['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            $lecturer->courseUnitYears()->sync($cuyData);
        }

        return redirect()->route('institution-admin.lecturers.index')
            ->with('success', 'Lecturer created successfully.');
    }

    public function show(User $lecturer)
    {
        $this->authorize('view', $lecturer);
        
        $lecturer->load(['department', 'institution', 'courseUnitYears.course.department', 'courseUnitYears.unit']);
        
        // Derive courses via courseUnitYears mapping
        $courses = $lecturer->courseUnitYears->pluck('course')->filter();
        $stats = [
            'total_courses' => $courses->unique('id')->count(),
            'departments' => $courses->pluck('department.name')->filter()->unique()->count(),
            'active_timetables' => 0 // TODO: implement when timetable entries are ready
        ];
        
        return view('institution-admin.lecturers.show', compact('lecturer', 'stats', 'courses'));
    }

    public function edit(User $lecturer)
    {
        $this->authorize('update', $lecturer);
        
        $institution = auth()->user()->institution;
        $departments = $institution->departments()->get();
        $courses = $institution->departments()->with('courses')->get()->pluck('courses')->flatten();
        $coursesForJs = $courses->map(function($course){
            return [
                'id' => $course->id,
                'name' => $course->name,
                'department' => optional($course->department)->name,
                'lab_required' => (bool)($course->lab_required ?? false),
            ];
        })->values();

        // Build mappings and unit lists for JS
        $courseUnitYears = CourseUnitYear::with(['course.department','unit'])
            ->whereHas('course.department', function($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            })->get();
        $cuyForJs = $courseUnitYears->filter(function($m) {
            return $m->unit !== null; // Only include mappings with valid units
        })->map(function($m) {
            return [
                'course_id' => $m->course_id,
                'unit_id' => $m->unit_id,
                'unit_code' => $m->unit->code ?? '',
                'unit_name' => $m->unit->name ?? '',
                'academic_year' => $m->academic_year,
                'semester' => $m->semester,
            ];
        })->values();
        $unitsForJs = Unit::where('institution_id', $institution->id)
            ->orderBy('code')
            ->get(['id','code','name','year_level'])
            ->map(function($u){
            return [
                'id' => $u->id,
                'code' => $u->code,
                'name' => $u->name,
                'year_level' => $u->year_level,
            ];
        });

        $lecturerCourseAssignments = $lecturer->courseUnitYears->map(function($m) {
            return [
                'course_id' => $m->course_id,
                'unit_id' => $m->unit_id,
                'academic_year' => $m->academic_year,
                'is_lab_only' => $m->pivot->is_lab_only,
                'notes' => $m->pivot->notes,
                'course_name' => optional($m->course)->name,
                'unit_name' => optional($m->unit)->name,
                'unit_code' => optional($m->unit)->code,
            ];
        })->toArray();
        
        $allUnits = Unit::where('institution_id', $institution->id)
            ->orderBy('code')
            ->get();
        return view('institution-admin.lecturers.edit', compact('lecturer', 'departments', 'courses', 'coursesForJs', 'cuyForJs', 'unitsForJs', 'lecturerCourseAssignments', 'allUnits'));
    }

    public function update(Request $request, User $lecturer)
    {
        $this->authorize('update', $lecturer);
        
        $institution = auth()->user()->institution;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($lecturer->id)],
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_id')->ignore($lecturer->id)],
            'department_id' => 'required|exists:departments,id',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
'course_assignments' => 'array',
            'course_assignments.*.course_id' => 'required|exists:courses,id',
            'course_assignments.*.unit_id' => 'required|exists:units,id',
'course_assignments.*.academic_year' => 'required|in:Y1,Y2,Y3,Y4,Y5',
            'course_assignments.*.semester' => 'nullable|in:S1,S2',
            'course_assignments.*.is_lab_only' => 'boolean',
            'course_assignments.*.notes' => 'nullable|string|max:500'
        ]);

        // Verify department belongs to institution
        $department = Department::where('id', $request->department_id)
            ->where('institution_id', $institution->id)
            ->firstOrFail();

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
'employee_id' => $request->employee_id,
            'department_id' => $request->department_id,
            'phone' => $request->phone,
        ];

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $lecturer->update($updateData);

        // Update unit-year assignments
        if ($request->has('course_assignments')) {
            $cuyData = [];
            foreach ($request->course_assignments ?? [] as $assignment) {
                // Verify course belongs to institution and mapping exists
                $course = Course::where('id', $assignment['course_id'])
                    ->whereHas('department', function($query) use ($institution) {
                        $query->where('institution_id', $institution->id);
                    })
                    ->first();
                if (!$course) { continue; }

                $cuy = CourseUnitYear::where('course_id', $assignment['course_id'])
                    ->where('unit_id', $assignment['unit_id'])
                    ->where('academic_year', $assignment['academic_year'])
                    ->first();
                if (!$cuy) {
                    $cuy = CourseUnitYear::firstOrCreate([
                        'course_id' => $assignment['course_id'],
                        'unit_id' => $assignment['unit_id'],
                        'academic_year' => $assignment['academic_year'],
                    ], [
                        'semester' => $assignment['semester'] ?? null,
                    ]);
                } else {
                    // Update semester if changed (allow clearing by setting to null)
                    $newSemester = !empty($assignment['semester']) ? $assignment['semester'] : null;
                    if ($cuy->semester !== $newSemester) {
                        $cuy->semester = $newSemester;
                        $cuy->save();
                    }
                }

                // Check if this unit is already assigned to another lecturer
                $existingAssignment = DB::table('course_unit_year_user')
                    ->where('course_unit_year_id', $cuy->id)
                    ->where('user_id', '!=', $lecturer->id)
                    ->first();

                if ($existingAssignment) {
                    $existingLecturer = DB::table('users')->where('id', $existingAssignment->user_id)->first();
                    $unit = DB::table('units')->where('id', $assignment['unit_id'])->first();
                    return back()->withErrors([
                        'course_assignments' => "Unit '{$unit->code} - {$unit->name}' in course '{$course->name}' is already assigned to lecturer '{$existingLecturer->name}'. Each unit can only be taught by one lecturer per course."
                    ])->withInput();
                }

                $cuyData[$cuy->id] = [
                    'is_lab_only' => $assignment['is_lab_only'] ?? false,
                    'notes' => $assignment['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            $lecturer->courseUnitYears()->sync($cuyData);
        }

        return redirect()->route('institution-admin.lecturers.index')
            ->with('success', 'Lecturer updated successfully.');
    }

    public function destroy(User $lecturer)
    {
        $this->authorize('delete', $lecturer);
        
        // Soft-deactivate instead of hard delete
        $lecturer->update(['is_active' => false]);

        return redirect()->route('institution-admin.lecturers.index')
            ->with('success', 'Lecturer deactivated successfully.');
    }

    public function deactivate(User $lecturer)
    {
        $this->authorize('update', $lecturer);

        $lecturer->update(['is_active' => false]);

        return redirect()->route('institution-admin.lecturers.index')
            ->with('success', 'Lecturer deactivated successfully.');
    }

    public function activate(User $lecturer)
    {
        $this->authorize('update', $lecturer);

        $lecturer->update(['is_active' => true]);

        return redirect()->route('institution-admin.lecturers.index')
            ->with('success', 'Lecturer activated successfully.');
    }
}