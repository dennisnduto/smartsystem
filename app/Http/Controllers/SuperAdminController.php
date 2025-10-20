<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institution;
use App\Models\Timetable;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        try {
            $stats = [
                'total_institutions' => Institution::count(),
                'total_admins' => User::where('role', 'institution_admin')->count(),
                'total_timetables' => Timetable::where('status', 'published')->count(),
                'total_users' => User::count()
            ];
            
            $recent_institutions = Institution::latest()
                ->limit(5)
                ->get();
            
            $recent_admins = User::where('role', 'institution_admin')
                ->with('institution')
                ->latest()
                ->limit(5)
                ->get();
            
            // Debug logging
            \Log::info('Dashboard data:', [
                'stats' => $stats,
                'institutions_count' => $recent_institutions->count(),
                'admins_count' => $recent_admins->count()
            ]);
                
            return view('super-admin.dashboard', compact('stats', 'recent_institutions', 'recent_admins'));
            
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());
            
            // Fallback data
            $stats = [
                'total_institutions' => 0,
                'total_admins' => 0,
                'total_timetables' => 0,
                'total_users' => 0
            ];
            
            $recent_institutions = collect();
            $recent_admins = collect();
            
            return view('super-admin.dashboard', compact('stats', 'recent_institutions', 'recent_admins'));
        }
    }

    public function manageAdmins()
    {
        $admins = User::where('role', 'institution_admin')
            ->with('institution')
            ->paginate(15);
            
        $institutions = Institution::all();
        
        return view('super-admin.manage-admins', compact('admins', 'institutions'));
    }
    
    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'institution_id' => 'required|exists:institutions,id'
        ]);
        
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'institution_admin',
            'institution_id' => $request->institution_id
        ]);
        
        return redirect()->route('super-admin.manage-admins')
            ->with('success', 'Institution admin created successfully!');
    }
    
    public function updateAdmin(Request $request, User $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($admin->id)],
            'institution_id' => 'required|exists:institutions,id',
            'password' => 'nullable|string|min:8'
        ]);
        
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'institution_id' => $request->institution_id
        ];
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $admin->update($data);
        
        return redirect()->route('super-admin.manage-admins')
            ->with('success', 'Institution admin updated successfully!');
    }
    
    public function deleteAdmin(User $admin)
    {
        if ($admin->role !== 'institution_admin') {
            return redirect()->back()->with('error', 'Invalid admin user!');
        }
        
        $admin->delete();
        
        return redirect()->route('super-admin.manage-admins')
            ->with('success', 'Institution admin deleted successfully!');
    }
    
    public function viewTimetables()
    {
        $timetables = Timetable::where('status', 'published')
            ->with(['institution', 'teachingGroups.course', 'teachingGroups.lecturer', 'teachingGroups.room'])
            ->paginate(20);
            
        return view('super-admin.timetables', compact('timetables'));
    }
    
    public function generateSummaryReport()
    {
        $data = [
            'institutions' => Institution::withCount(['users', 'departments'])->get(),
            'timetable_stats' => [
                'total_published' => Timetable::where('status', 'published')->count(),
                'total_draft' => Timetable::where('status', 'draft')->count(),
                'by_institution' => Timetable::selectRaw('institution_id, status, count(*) as count')
                    ->with('institution:id,name')
                    ->groupBy('institution_id', 'status')
                    ->get()
                    ->groupBy('institution_id')
            ],
            'user_stats' => [
                'by_role' => User::selectRaw('role, count(*) as count')
                    ->groupBy('role')
                    ->get()
                    ->pluck('count', 'role'),
                'by_institution' => User::whereNotNull('institution_id')
                    ->selectRaw('institution_id, role, count(*) as count')
                    ->with('institution:id,name')
                    ->groupBy('institution_id', 'role')
                    ->get()
                    ->groupBy('institution_id')
            ],
            'generated_at' => now()
        ];
        
        $pdf = Pdf::loadView('super-admin.reports.summary', $data);
        
        return $pdf->download('summary-report-' . now()->format('Y-m-d') . '.pdf');
    }
}