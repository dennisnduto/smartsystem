<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
   public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    $user = $request->user();

    switch ($user->role) {
        case 'super_admin':
            return redirect()->route('super-admin.dashboard');

        case 'institution_admin':
            return redirect()->route('institution-admin.dashboard');

        case 'lecturer':
            return redirect()->route('lecturer.dashboard');

        case 'student':
            return redirect()->route('student.dashboard');

        default:
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Unauthorized access. Role not recognized.',
            ]);
    }
}


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
