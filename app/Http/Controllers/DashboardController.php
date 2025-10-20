<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Debug: Log the user role
        \Log::info('Dashboard accessed by user with role: ' . $user->role);
        
        // Redirect based on user role
        switch ($user->role) {
            case 'super_admin':
                \Log::info('Redirecting to super admin dashboard');
                return redirect()->route('super-admin.dashboard');
                
            case 'institution_admin':
                \Log::info('Redirecting institution admin to their dashboard: ' . ($user->institution->name ?? 'No institution'));
                
                // Check if user has an assigned institution
                if (!$user->institution) {
                    \Log::error('Institution admin without institution: ' . $user->email);
                    return view('dashboard')->with('error', 'No institution assigned to your account. Please contact the system administrator.');
                }
                
                return redirect()->route('institution-admin.dashboard');
                
            case 'lecturer':
                // TODO: Create lecturer dashboard
                return view('dashboard'); // Default dashboard for now
                
            case 'student':
            default:
                // Default dashboard for students and others
                return view('dashboard');
        }
    }
}
