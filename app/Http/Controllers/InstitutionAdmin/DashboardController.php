<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Institution, Department, User, Timetable, Room, Unit};
use App\Services\InstitutionAdminAIService;
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
            'total_units' => Unit::where('institution_id', $institution->id)->count(),
            'total_lecturers' => $institution->users()->where('role', 'lecturer')->count(),
            'total_students' => $institution->users()->where('role', 'student')->count(),
            'total_rooms' => Room::where('institution_id', $institution->id)->count(),
            'active_timetables' => Timetable::where('institution_id', $institution->id)
                ->where('status', 'published')
                ->count(),
            'draft_timetables' => Timetable::where('institution_id', $institution->id)
                ->where('status', 'draft')
                ->count(),
        ];

        // Get recent activities
        $recent_timetables = Timetable::where('institution_id', $institution->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->take(5)
            ->get();

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
        $available_rooms = Room::where('institution_id', $institution->id)
            ->take(5)
            ->get();

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
                'published' => Timetable::where('institution_id', $institution->id)
                    ->where('status', 'published')
                    ->count(),
                'draft' => Timetable::where('institution_id', $institution->id)
                    ->where('status', 'draft')
                    ->count(),
            ],
            'room_utilization' => Room::where('institution_id', $institution->id)
                ->withCount('timetableEntries')
                ->get(),
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
                'total_rooms' => Room::where('institution_id', $institution->id)->count(),
            ],
            'departments' => $institution->departments()->withCount(['courses', 'rooms'])->get(),
            'recent_timetables' => Timetable::where('institution_id', $institution->id)
                ->with('department')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('institution-admin.reports', compact('reports_data', 'institution'));
    }

    /**
     * AI chatbot for institution admin
     */
    public function chatbot(Request $request)
    {
        $user = Auth::user();
        $question = $request->input('q', '');

        if (empty($question)) {
            return response()->json(['answer' => 'Please ask me something about institution management.']);
        }

        $aiService = new InstitutionAdminAIService();
        $response = $aiService->generateResponse($question, $user);

        // Log the interaction
        \App\Models\ChatLog::create([
            'user_id' => $user->id,
            'role' => $user->role,
            'query' => $question,
            'response' => $response,
        ]);

        return response()->json(['answer' => $response]);
    }

    public function clearChat(Request $request)
    {
        $user = Auth::user();
        \App\Models\ChatLog::where('user_id', $user->id)->delete();
        
        return response()->json(['message' => 'Chat history cleared.']);
    }

    /**
     * Export institution reports in various formats
     */
    public function exportReport(Request $request, $format)
    {
        $user = Auth::user();
        $institution = $user->institution;

        if (!$institution) {
            return back()->with('error', 'No institution assigned to your account.');
        }

        // Get comprehensive institution data
        $reportData = $this->getReportData($institution);

        switch (strtolower($format)) {
            case 'pdf':
                return $this->exportPDF($institution, $reportData);
            case 'csv':
                return $this->exportCSV($institution, $reportData);
            case 'excel':
                return $this->exportExcel($institution, $reportData);
            default:
                return back()->with('error', 'Unsupported export format.');
        }
    }

    private function getReportData($institution): array
    {
        // Get all the data needed for the report
        $stats = [
            'total_departments' => $institution->departments()->count(),
            'total_schools' => $institution->schools()->count(),
            'total_courses' => $institution->departments()->withCount('courses')->get()->sum('courses_count'),
            'total_units' => Unit::where('institution_id', $institution->id)->count(),
            'total_lecturers' => $institution->users()->where('role', 'lecturer')->count(),
            'total_students' => $institution->users()->where('role', 'student')->count(),
            'total_rooms' => Room::where('institution_id', $institution->id)->count(),
            'active_timetables' => Timetable::where('institution_id', $institution->id)
                ->where('status', 'published')
                ->count(),
            'draft_timetables' => Timetable::where('institution_id', $institution->id)
                ->where('status', 'draft')
                ->count(),
        ];

        $departments = $institution->departments()
            ->withCount(['courses', 'rooms'])
            ->with(['courses', 'rooms'])
            ->get();

        // Add lecturer counts to departments
        foreach ($departments as $dept) {
            $dept->lecturers_count = User::where('institution_id', $institution->id)
                ->where('role', 'lecturer')
                ->where('department_id', $dept->id)
                ->count();
        }

        $recentTimetables = Timetable::where('institution_id', $institution->id)
            ->with('department')
            ->latest()
            ->take(10)
            ->get();

        $rooms = Room::where('institution_id', $institution->id)
            ->with('department')
            ->get();

        $lecturers = $institution->users()
            ->where('role', 'lecturer')
            ->withCount(['courseUnitYears'])
            ->with('department')
            ->get();

        return [
            'institution' => $institution,
            'stats' => $stats,
            'departments' => $departments,
            'timetables' => $recentTimetables,
            'rooms' => $rooms,
            'lecturers' => $lecturers,
            'generated_at' => now()
        ];
    }

    private function exportPDF($institution, $data)
    {
        $filename = "institution_report_{$institution->id}_" . date('Y-m-d') . ".pdf";
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('institution-admin.reports-pdf', $data);
        
        return $pdf->download($filename);
    }


    private function exportCSV($institution, $data)
    {
        $filename = "institution_report_{$institution->id}_" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding
            fwrite($file, "\xEF\xBB\xBF");
            
            // Institution Overview
            fputcsv($file, ['Institution Report']);
            fputcsv($file, ['Generated:', $data['generated_at']->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Statistics
            fputcsv($file, ['Institution Statistics']);
            fputcsv($file, ['Metric', 'Value']);
            foreach ($data['stats'] as $key => $value) {
                fputcsv($file, [ucwords(str_replace('_', ' ', $key)), $value]);
            }
            fputcsv($file, []);

            // Departments
            fputcsv($file, ['Departments']);
            fputcsv($file, ['Name', 'Courses', 'Rooms', 'Lecturers']);
            foreach ($data['departments'] as $dept) {
                fputcsv($file, [
                    $dept->name,
                    $dept->courses_count,
                    $dept->rooms_count,
                    $dept->users_count
                ]);
            }
            fputcsv($file, []);

            // Rooms
            fputcsv($file, ['Rooms']);
            fputcsv($file, ['Name', 'Department', 'Capacity', 'Type']);
            foreach ($data['rooms'] as $room) {
                fputcsv($file, [
                    $room->name,
                    $room->department->name ?? 'N/A',
                    $room->capacity ?? 'N/A',
                    $room->room_type ?? 'Standard'
                ]);
            }
            fputcsv($file, []);

            // Lecturers
            fputcsv($file, ['Lecturers']);
            fputcsv($file, ['Name', 'Email', 'Department', 'Total Classes']);
            foreach ($data['lecturers'] as $lecturer) {
                fputcsv($file, [
                    $lecturer->name,
                    $lecturer->email,
                    $lecturer->department->name ?? 'N/A',
                    $lecturer->course_unit_years_count
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportExcel($institution, $data)
    {
        $filename = "institution_report_{$institution->id}_" . date('Y-m-d') . ".xlsx";
        
        // For now, create a CSV that can be opened in Excel
        // In a production environment, you'd use Laravel Excel or similar
        
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding
            fwrite($file, "\xEF\xBB\xBF");
            
            // Institution Overview
            fputcsv($file, ['Institution Report']);
            fputcsv($file, ['Generated:', $data['generated_at']->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Statistics
            fputcsv($file, ['Institution Statistics']);
            fputcsv($file, ['Metric', 'Value']);
            foreach ($data['stats'] as $key => $value) {
                fputcsv($file, [ucwords(str_replace('_', ' ', $key)), $value]);
            }
            fputcsv($file, []);

            // Departments
            fputcsv($file, ['Departments']);
            fputcsv($file, ['Name', 'Courses', 'Rooms', 'Lecturers']);
            foreach ($data['departments'] as $dept) {
                fputcsv($file, [
                    $dept->name,
                    $dept->courses_count,
                    $dept->rooms_count,
                    $dept->lecturers_count ?? 0
                ]);
            }
            fputcsv($file, []);

            // Rooms
            fputcsv($file, ['Rooms']);
            fputcsv($file, ['Name', 'Department', 'Capacity', 'Type']);
            foreach ($data['rooms'] as $room) {
                fputcsv($file, [
                    $room->name,
                    $room->department->name ?? 'N/A',
                    $room->capacity ?? 'N/A',
                    $room->room_type ?? 'Standard'
                ]);
            }
            fputcsv($file, []);

            // Lecturers
            fputcsv($file, ['Lecturers']);
            fputcsv($file, ['Name', 'Email', 'Department', 'Total Classes']);
            foreach ($data['lecturers'] as $lecturer) {
                fputcsv($file, [
                    $lecturer->name,
                    $lecturer->email,
                    $lecturer->department->name ?? 'N/A',
                    $lecturer->course_unit_years_count
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}