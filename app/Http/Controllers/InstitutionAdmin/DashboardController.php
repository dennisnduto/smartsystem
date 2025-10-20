<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Institution, Department, User, Timetable, Room};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $institution = $user->institution;

        if (!$institution) {
            return redirect()->route('dashboard')->with('error', 'No institution assigned to your account.');
        }

        // Get institution statistics
        $stats = [
'total_departments' => $institution->departments()->count(),
            'total_schools' => $institution->schools()->count(),
'total_courses' => $institution->departments()->withCount('courses')->get()->sum('courses_count'),
'total_units' => \App\Models\Unit::count(),
            'total_lecturers' => $institution->users()->where('role', 'lecturer')->count(),
            'total_students' => $institution->users()->where('role', 'student')->count(),
            'total_rooms' => Room::whereHas('department', function($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            })->count(),
            'active_timetables' => Timetable::whereHas('department', function($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            })->where('status', 'published')->count(),
            'draft_timetables' => Timetable::whereHas('department', function($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            })->where('status', 'draft')->count(),
        ];

        // Get recent activities
        $recent_timetables = Timetable::whereHas('department', function($q) use ($institution) {
            $q->where('institution_id', $institution->id);
        })->with('department')->latest()->take(5)->get();

        // Get departments with stats
        $departments = $institution->departments()
            ->withCount(['courses', 'rooms'])
            ->latest()
            ->take(6)
            ->get();

        // Get recent users (lecturers and students)
        $recent_users = $institution->users()
            ->whereIn('role', ['lecturer', 'student'])
            ->latest()
            ->take(5)
            ->get();

        // Room availability (simplified - assume all rooms are available unless in use)
        $available_rooms = Room::whereHas('department', function($q) use ($institution) {
            $q->where('institution_id', $institution->id);
        })->take(5)->get();

        // Quick stats for charts
        $department_stats = $institution->departments()
            ->withCount(['courses', 'rooms'])
            ->get()
            ->map(function($dept) {
                return [
                    'name' => $dept->name,
                    'courses' => $dept->courses_count,
                    'rooms' => $dept->rooms_count,
                ];
            });

        return view('institution-admin.dashboard', compact(
            'institution', 
            'stats', 
            'recent_timetables', 
            'departments', 
            'recent_users', 
            'available_rooms',
            'department_stats'
        ));
    }

    public function generateTimetable(Request $request)
    {
        $user = Auth::user();
        $institution = $user->institution;

        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year' => 'nullable|in:Y1,Y2,Y3,Y4,Y5',
            'semester' => 'nullable|in:S1,S2',
        ]);

        // Create institution-wide timetable (no specific department)
        $timetable = Timetable::create([
            'name' => $request->name,
            'status' => 'draft',
            'created_by' => $user->id ?? null,
            'institution_id' => $institution->id,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
        ]);

        // Generate entries using current units, lecturer availability, and rooms
        app(\App\Services\TimetableGenerator::class)
            ->generate($timetable->name, $institution->id, $request->academic_year, $request->semester);

        return redirect()->route('institution-admin.timetables.show', $timetable)
            ->with('success', 'Timetable generated successfully.');
    }

    public function analytics()
    {
        $user = Auth::user();
        $institution = $user->institution;

        $analytics = [
            'departments_overview' => $institution->departments()->withCount(['courses', 'rooms'])->get(),
            'user_distribution' => [
                'lecturers' => $institution->users()->where('role', 'lecturer')->count(),
                'students' => $institution->users()->where('role', 'student')->count(),
            ],
            'timetable_status' => [
                'published' => Timetable::whereHas('department', function($q) use ($institution) {
                    $q->where('institution_id', $institution->id);
                })->where('status', 'published')->count(),
                'draft' => Timetable::whereHas('department', function($q) use ($institution) {
                    $q->where('institution_id', $institution->id);
                })->where('status', 'draft')->count(),
            ],
            'room_utilization' => Room::whereHas('department', function($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            })->withCount('timetableEntries')->get(),
        ];

        return view('institution-admin.analytics', compact('analytics', 'institution'));
    }

    public function reports()
    {
        $user = Auth::user();
        $institution = $user->institution;

        $reports_data = [
            'summary_stats' => [
                'total_departments' => $institution->departments()->count(),
                'total_courses' => $institution->departments()->withCount('courses')->get()->sum('courses_count'),
                'total_lecturers' => $institution->users()->where('role', 'lecturer')->count(),
                'total_students' => $institution->users()->where('role', 'student')->count(),
                'total_rooms' => Room::whereHas('department', function($q) use ($institution) {
                    $q->where('institution_id', $institution->id);
                })->count(),
            ],
            'departments' => $institution->departments()->withCount(['courses', 'rooms'])->get(),
            'recent_timetables' => Timetable::whereHas('department', function($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            })->with('department')->latest()->take(10)->get(),
        ];

        return view('institution-admin.reports', compact('reports_data', 'institution'));
    }
}