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

        $timetable->load(['institution', 'entries.unit', 'entries.course', 'entries.room', 'entries.lecturer']);
        
        return view('super-admin.timetable-view', compact('timetable'));
    }

    public function downloadTimetable(\App\Models\Timetable $timetable)
    {
        if (!in_array($timetable->status, ['approved', 'published'])) {
            abort(404);
        }

        $timetable->load(['institution', 'entries.unit', 'entries.course', 'entries.room', 'entries.lecturer']);
        
        $pdf = Pdf::loadView('institution-admin.timetables.pdf', compact('timetable'));
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'Timetable_' . $timetable->institution->name . '_' . $timetable->name . '_' . now()->format('Y-m-d') . '.pdf';
        
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
}
