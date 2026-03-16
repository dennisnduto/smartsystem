<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Institution, Timetable, Department, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class SuperAdminController extends Controller
{
    protected $days = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
    ];

    public function index()
    {
        $stats = [
            'institutions' => Institution::count(),
            'admins' => User::where('role','institution_admin')->count(),
            'lecturers' => User::where('role','lecturer')->count(),
            'students' => User::where('role','student')->count(),
            'timetables' => \App\Models\Timetable::count(),
        ];

        $recentInstitutions = Institution::latest()->take(5)->get();
        $recentTimetables = \App\Models\Timetable::with('department')->latest()->take(5)->get();
        $allInstitutions = Institution::orderBy('name')->paginate(15);

        return view('admin.dashboard', compact('stats','recentInstitutions','recentTimetables','allInstitutions'));
    }

    public function dashboard()
    {
        \Log::info('Dashboard method called');
        
        $stats = [
            'total_institutions' => Institution::count(),
            'total_admins' => User::where('role','institution_admin')->count(),
            'total_users' => User::count(),
            'total_timetables' => \App\Models\Timetable::where('status', 'published')->count(),
        ];

        $recent_institutions = Institution::withCount(['users', 'departments'])->latest()->take(5)->get();
        $recent_admins = User::where('role', 'institution_admin')
            ->with('institution')
            ->latest()
            ->take(5)
            ->get();

        \Log::info('Dashboard data:', compact('stats', 'recent_institutions', 'recent_admins'));
        
        return view('super-admin.dashboard', compact('stats', 'recent_institutions', 'recent_admins'));
    }

    public function manageAdmins()
    {
        $admins = User::where('role', 'institution_admin')
            ->with('institution')
            ->paginate(20);
        
        $institutions = Institution::orderBy('name')->get();
        
        return view('super-admin.manage-admins', compact('admins', 'institutions'));
    }

    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'institution_id' => 'required|exists:institutions,id',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'institution_admin',
            'institution_id' => $request->institution_id,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('super-admin.manage-admins')
            ->with('success', 'Institution admin created successfully.');
    }

    public function updateAdmin(Request $request, User $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'institution_id' => 'required|exists:institutions,id',
            'password' => 'nullable|string|min:8',
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'institution_id' => $request->institution_id,
        ]);

        if ($request->password) {
            $admin->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('super-admin.manage-admins')
            ->with('success', 'Institution admin updated successfully.');
    }

    public function deleteAdmin(User $admin)
    {
        // Keep the route for backward compatibility, but do not hard-delete.
        $admin->update(['is_active' => false]);
        
        return redirect()->route('super-admin.manage-admins')
            ->with('success', 'Institution admin deactivated successfully.');
    }

    public function deactivateAdmin(User $admin)
    {
        $admin->update(['is_active' => false]);

        return redirect()->route('super-admin.manage-admins')
            ->with('success', 'Institution admin deactivated successfully.');
    }

    public function reactivateAdmin(User $admin)
    {
        $admin->update(['is_active' => true]);

        return redirect()->route('super-admin.manage-admins')
            ->with('success', 'Institution admin reactivated successfully.');
    }

    public function viewTimetables()
    {
        // Super admin should see ONLY published timetables from all institutions
        $timetables = \App\Models\Timetable::where('status', 'published')
            ->with(['institution', 'department'])
            ->latest()
            ->paginate(20);

        return view('super-admin.timetables', compact('timetables'));
    }

    public function viewTimetable(\App\Models\Timetable $timetable)
    {
        if (!in_array($timetable->status, ['approved', 'published'])) {
            abort(404);
        }

        $timetable->load(['institution', 'department', 'entries.unit', 'entries.course', 'entries.room', 'entries.lecturer', 'entries.teachingGroup']);
        
        $matrix = [];
        $programs = [];
        $entries = $timetable->entries;

        $entries = $entries->map(function($entry) {
            $cuy = DB::table('course_unit_year')
                ->where('course_id', $entry->course_id)
                ->where('unit_id', $entry->unit_id)
                ->first();
            
            $entry->academic_year_val = $cuy->academic_year ?? $entry->timetable->academic_year ?? 'N/A';
            return $entry;
        });

        foreach ($entries as $entry) {
            $courseCode = !empty($entry->course->code) 
                ? strtoupper($entry->course->code) 
                : $this->abbreviateCourseName($entry->course->name ?? 'Unknown Course');
                
            $academicYear = $entry->academic_year_val;
            $key = $courseCode . ' | ' . $academicYear;
            
            if (!isset($programs[$key])) {
                $programs[$key] = [
                    'course' => $courseCode,
                    'year' => $academicYear,
                ];
            }
            $matrix[$entry->day_of_week][$entry->slot][$key] = $entry;
        }

        uksort($programs, function($a, $b) {
            return strnatcmp($a, $b);
        });

        $programsByCourse = [];
        foreach ($programs as $key => $details) {
            $course = $details['course'];
            $programsByCourse[$course][$key] = $details;
        }

        $programChunks = [];
        foreach ($programsByCourse as $course => $coursePrograms) {
            $chunks = array_chunk($coursePrograms, 7, true);
            foreach ($chunks as $chunk) {
                $programChunks[] = [
                    'course' => $course,
                    'programs' => $chunk
                ];
            }
        }

        $days = $this->days;
        $slots = [1 => '7:00-10:00', 2 => '10:00-13:00', 3 => '13:00-16:00', 4 => '16:00-19:00'];

        return view('super-admin.timetable-view', compact('timetable', 'matrix', 'programChunks', 'days', 'slots'));
    }

    public function downloadTimetable(\App\Models\Timetable $timetable)
    {
        if (!in_array($timetable->status, ['approved', 'published'])) {
            abort(404);
        }

        $timetable->load(['institution', 'department', 'entries.unit', 'entries.course', 'entries.room', 'entries.lecturer', 'entries.teachingGroup']);
        
        $user = auth()->user();
        
        $matrix = [];
        $programs = [];
        $entries = $timetable->entries;

        $entries = $entries->map(function($entry) {
            $cuy = DB::table('course_unit_year')
                ->where('course_id', $entry->course_id)
                ->where('unit_id', $entry->unit_id)
                ->first();
            
            $entry->academic_year_val = $cuy->academic_year ?? $entry->timetable->academic_year ?? 'N/A';
            return $entry;
        });

        foreach ($entries as $entry) {
            $courseCode = !empty($entry->course->code) 
                ? strtoupper($entry->course->code) 
                : $this->abbreviateCourseName($entry->course->name ?? 'Unknown Course');
                
            $academicYear = $entry->academic_year_val;
            $key = $courseCode . ' | ' . $academicYear;
            
            if (!isset($programs[$key])) {
                $programs[$key] = [
                    'course' => $courseCode,
                    'year' => $academicYear,
                ];
            }
            $matrix[$entry->day_of_week][$entry->slot][$key] = $entry;
        }

        uksort($programs, function($a, $b) {
            return strnatcmp($a, $b);
        });

        $programsByCourse = [];
        foreach ($programs as $key => $details) {
            $course = $details['course'];
            $programsByCourse[$course][$key] = $details;
        }

        $programChunks = [];
        foreach ($programsByCourse as $course => $coursePrograms) {
            $chunks = array_chunk($coursePrograms, 7, true);
            foreach ($chunks as $chunk) {
                $programChunks[] = [
                    'course' => $course,
                    'programs' => $chunk
                ];
            }
        }

        $days = $this->days;
        $slots = [1 => '7:00-10:00', 2 => '10:00-13:00', 3 => '13:00-16:00', 4 => '16:00-19:00'];
        $title = 'Official Institutional Timetable';
        $isInstitutional = true;

        $pdf = Pdf::loadView('institution-admin.timetables.pdf', [
            'timetable' => $timetable,
            'matrix' => $matrix,
            'programChunks' => $programChunks,
            'days' => $days,
            'slots' => $slots,
            'user' => $user,
            'title' => $title,
            'isInstitutional' => $isInstitutional
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);
        
        $baseFilename = 'Timetable_' . $timetable->institution->name . '_' . $timetable->name;
        $filename = $baseFilename . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function generateSummaryReport()
    {
        $stats = [
            'total_institutions' => Institution::count(),
            'total_admins' => User::where('role', 'institution_admin')->count(),
            'total_users' => User::count(),
            'total_timetables' => \App\Models\Timetable::count(),
            'published_timetables' => \App\Models\Timetable::where('status', 'published')->count(),
        ];

        $institutions = Institution::withCount(['users', 'departments'])->get();
        $recent_activities = \App\Models\Timetable::with(['institution'])
            ->latest()
            ->take(10)
            ->get();

        return view('super-admin.reports', compact('stats', 'institutions', 'recent_activities'));
    }

    public function manageInstitutions()
    {
        $institutions = Institution::withCount(['users', 'departments'])
            ->latest()
            ->paginate(20);
        
        return view('super-admin.manage-institutions', compact('institutions'));
    }

    public function createInstitution(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:institutions,name',
        ]);

        $institution = Institution::create([
            'name' => $request->name,
        ]);

        // Create a default department
        \App\Models\Department::create([
            'name' => 'General',
            'institution_id' => $institution->id,
        ]);

        return redirect()->route('super-admin.institutions')
            ->with('success', 'Institution created successfully.');
    }

    public function updateInstitution(Request $request, Institution $institution)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:institutions,name,' . $institution->id,
        ]);

        $institution->update([
            'name' => $request->name,
        ]);

        return redirect()->route('super-admin.institutions')
            ->with('success', 'Institution updated successfully.');
    }

    public function deleteInstitution(Institution $institution)
    {
        // Keep the route for backward compatibility, but do not hard-delete.
        $institution->update(['is_active' => false]);
        $institution->users()->update(['is_active' => false]);
        
        return redirect()->route('super-admin.institutions')
            ->with('success', 'Institution deactivated successfully.');
    }

    public function deactivateInstitution(Institution $institution)
    {
        $institution->update(['is_active' => false]);
        $institution->users()->update(['is_active' => false]);

        return redirect()->route('super-admin.institutions')
            ->with('success', 'Institution deactivated successfully.');
    }

    public function reactivateInstitution(Institution $institution)
    {
        $institution->update(['is_active' => true]);
        // NOTE: We do NOT auto-reactivate all institution users; admin can reactivate individually if needed.

        return redirect()->route('super-admin.institutions')
            ->with('success', 'Institution reactivated successfully.');
    }

    public function exportReport($format)
    {
        // Gather the same data as the reports page
        $stats = [
            'total_institutions' => Institution::count(),
            'total_admins' => User::where('role', 'institution_admin')->count(),
            'total_users' => User::count(),
            'total_timetables' => \App\Models\Timetable::count(),
            'published_timetables' => \App\Models\Timetable::where('status', 'published')->count(),
        ];

        $institutions = Institution::withCount(['users', 'departments'])->get();
        $recent_activities = \App\Models\Timetable::with(['institution'])
            ->latest()
            ->take(20)
            ->get();

        switch (strtolower($format)) {
            case 'pdf':
                return $this->exportToPDF($stats, $institutions, $recent_activities);
            case 'csv':
                return $this->exportToCSV($stats, $institutions, $recent_activities);
            case 'excel':
                return $this->exportToExcel($stats, $institutions, $recent_activities);
            default:
                return redirect()->back()->with('error', 'Invalid export format.');
        }
    }

    private function exportToPDF($stats, $institutions, $recent_activities)
    {
        $data = compact('stats', 'institutions', 'recent_activities');
        $pdf = Pdf::loadView('reports.pdf-export', $data);
        
        return $pdf->download('system-report-' . date('Y-m-d') . '.pdf');
    }

    private function exportToCSV($stats, $institutions, $recent_activities)
    {
        $filename = 'system-report-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($stats, $institutions, $recent_activities) {
            $file = fopen('php://output', 'w');
            
            // System Statistics
            fputcsv($file, ['System Statistics']);
            fputcsv($file, ['Metric', 'Value']);
            foreach ($stats as $key => $value) {
                fputcsv($file, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }
            
            fputcsv($file, []); // Empty line
            
            // Institutions
            fputcsv($file, ['Institutions']);
            fputcsv($file, ['Name', 'Users Count', 'Departments Count', 'Created Date']);
            foreach ($institutions as $institution) {
                fputcsv($file, [
                    $institution->name,
                    $institution->users_count ?? 0,
                    $institution->departments_count ?? 0,
                    $institution->created_at->format('Y-m-d')
                ]);
            }
            
            fputcsv($file, []); // Empty line
            
            // Recent Activities
            fputcsv($file, ['Recent Timetable Activities']);
            fputcsv($file, ['Timetable', 'Institution', 'Status', 'Created Date']);
            foreach ($recent_activities as $activity) {
                fputcsv($file, [
                    $activity->name ?? 'Unnamed',
                    $activity->institution->name ?? 'N/A',
                    ucfirst($activity->status ?? 'draft'),
                    $activity->created_at->format('Y-m-d')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($stats, $institutions, $recent_activities)
    {
        $filename = 'system-report-' . date('Y-m-d') . '.xls';
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($stats, $institutions, $recent_activities) {
            $output = fopen('php://output', 'w');
            
            // HTML table format that Excel can read
            echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>";
            echo "<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'></head><body>";
            
            // System Statistics
            echo "<h2>System Statistics</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Metric</th><th>Value</th></tr>";
            foreach ($stats as $key => $value) {
                echo "<tr><td>" . ucfirst(str_replace('_', ' ', $key)) . "</td><td>" . $value . "</td></tr>";
            }
            echo "</table><br><br>";
            
            // Institutions
            echo "<h2>Institution Details</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Name</th><th>Users Count</th><th>Departments Count</th><th>Created Date</th></tr>";
            foreach ($institutions as $institution) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($institution->name) . "</td>";
                echo "<td>" . ($institution->users_count ?? 0) . "</td>";
                echo "<td>" . ($institution->departments_count ?? 0) . "</td>";
                echo "<td>" . $institution->created_at->format('Y-m-d') . "</td>";
                echo "</tr>";
            }
            echo "</table><br><br>";
            
            // Recent Activities
            echo "<h2>Recent Timetable Activities</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Timetable</th><th>Institution</th><th>Status</th><th>Created Date</th></tr>";
            foreach ($recent_activities as $activity) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($activity->name ?? 'Unnamed') . "</td>";
                echo "<td>" . htmlspecialchars($activity->institution->name ?? 'N/A') . "</td>";
                echo "<td>" . ucfirst($activity->status ?? 'draft') . "</td>";
                echo "<td>" . $activity->created_at->format('Y-m-d') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "</body></html>";
            
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Abbreviate long course names to save space in dense views.
     */
    private function abbreviateCourseName($name)
    {
        $replacements = [
            'Bachelor of Science in Software Engineering' => 'BSE',
            'Bachelor of Science Software Engineering' => 'BSE',
            'Software Engineering' => 'BSE',
            
            'Bachelor of Science in Computer Science' => 'BCS',
            'Bachelor of Science Computer Science' => 'BCS',
            'Computer Science' => 'BCS',
            
            'Bachelor of Science in Computer Technology' => 'BST',
            'Bachelor of Science Computer Technology' => 'BST',
            'Computer Technology' => 'BST',
            
            'Bachelor of Business Information Technology' => 'BBIT',
            'Business Information Technology' => 'BBIT',
            
            'Bachelor of Science in Information Technology' => 'BIT',
            'Bachelor of Science Information Technology' => 'BIT',
            'Information Technology' => 'BIT',
        ];

        foreach ($replacements as $long => $short) {
            if (stripos($name, $long) !== false) {
                return $short;
            }
        }

        // Generic abbreviation if no match
        $words = explode(' ', str_replace(['(', ')', '-', '/'], ' ', $name));
        $abbr = '';
        foreach ($words as $word) {
            if (strlen($word) > 2 && !in_array(strtolower($word), ['and', 'for', 'the', 'with'])) {
                $abbr .= strtoupper($word[0]);
            }
        }
        
        return !empty($abbr) ? $abbr : strtoupper(substr($name, 0, 3));
    }
}
