<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User, Course};
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'institution_id' => ['required', 'exists:institutions,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'year_of_study' => ['required', 'string', 'in:Y1,Y2,Y3,Y4,Y5'],
            'school_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // 5MB max
        ]);

        // Verify course belongs to the selected institution
        $course = Course::with('department.institution')->findOrFail($request->course_id);
        if ($course->department->institution_id != $request->institution_id) {
            return back()
                ->withInput()
                ->withErrors(['course_id' => 'The selected course does not belong to the selected institution.']);
        }

        // Store the school ID file
        $schoolIdPath = $request->file('school_id')->store('school-ids', 'public');

        // Create user (student)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'institution_id' => $request->institution_id,
            'is_approved' => false, // Students require approval
            'school_id_path' => $schoolIdPath,
            'year_of_study' => $request->year_of_study,
        ]);

        // Assign course to student
        $user->courses()->attach($request->course_id);

        event(new Registered($user));

        // Don't auto-login students - they need admin approval
        return redirect()->route('login')
            ->with('success', 'Registration successful! Your account is pending admin approval. You will be notified once approved.');
    }
}
