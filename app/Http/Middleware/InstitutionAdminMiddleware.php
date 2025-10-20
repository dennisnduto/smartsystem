<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstitutionAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in to access this page.');
        }

        // Check if user is an institution admin
        if ($user->role !== 'institution_admin') {
            \Log::warning('Non-institution admin tried to access institution admin area: ' . $user->email . ' (role: ' . $user->role . ')');
            return redirect()->route('dashboard')->with('error', 'You do not have permission to access this area.');
        }

        // Check if institution admin has an assigned institution
        if (!$user->institution) {
            \Log::error('Institution admin without institution tried to access admin area: ' . $user->email);
            return redirect()->route('dashboard')->with('error', 'No institution assigned to your account. Please contact the system administrator.');
        }

        // Log successful access for monitoring
        \Log::info('Institution admin access granted: ' . $user->email . ' for institution: ' . $user->institution->name);

        return $next($request);
    }
}