<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of students
     */
    public function index()
    {
        $institution = auth()->user()->institution;
        
        $students = $institution->users()
            ->where('role', 'student')
            ->with('courses')
            ->orderBy('is_approved')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $pendingCount = $institution->users()
            ->where('role', 'student')
            ->where('is_approved', false)
            ->count();
        
        return view('institution-admin.students.index', compact('students', 'pendingCount', 'institution'));
    }

    /**
     * Approve a student
     */
    public function approve(User $student)
    {
        $institution = auth()->user()->institution;
        
        // Verify student belongs to admin's institution
        if ($student->institution_id !== $institution->id || $student->role !== 'student') {
            return redirect()->route('institution-admin.students.index')
                ->with('error', 'Student not found or access denied.');
        }
        
        $student->update(['is_approved' => true]);
        
        return redirect()->route('institution-admin.students.index')
            ->with('success', 'Student approved successfully.');
    }

    /**
     * Reject/Unapprove a student
     */
    public function reject(User $student)
    {
        $institution = auth()->user()->institution;
        
        // Verify student belongs to admin's institution
        if ($student->institution_id !== $institution->id || $student->role !== 'student') {
            return redirect()->route('institution-admin.students.index')
                ->with('error', 'Student not found or access denied.');
        }
        
        $student->update(['is_approved' => false]);
        
        return redirect()->route('institution-admin.students.index')
            ->with('success', 'Student approval revoked.');
    }

    /**
     * Delete a student
     */
    public function destroy(User $student)
    {
        $institution = auth()->user()->institution;
        
        // Verify student belongs to admin's institution
        if ($student->institution_id !== $institution->id || $student->role !== 'student') {
            return redirect()->route('institution-admin.students.index')
                ->with('error', 'Student not found or access denied.');
        }
        
        $student->delete();
        
        return redirect()->route('institution-admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
