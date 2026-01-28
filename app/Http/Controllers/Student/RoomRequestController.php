<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{StudentRoomRequest, Room};
use Illuminate\Http\Request;

class RoomRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_approved) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Your account is pending approval.');
        }

        $requests = StudentRoomRequest::where('student_id', $user->id)
            ->with(['room', 'course', 'unit'])
            ->latest()
            ->paginate(15);

        return view('student.room-requests.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_approved) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Your account is pending approval.');
        }

        $rooms = Room::where('institution_id', $user->institution_id)
            ->orderBy('name')
            ->get();

        $courses = $user->courses;

        return view('student.room-requests.create', compact('rooms', 'courses'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'course_id' => 'nullable|exists:courses,id',
            'unit_id' => 'nullable|exists:units,id',
            'requested_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:255',
            'reason' => 'nullable|string|max:1000',
        ]);

        // Verify room belongs to student's institution
        $room = Room::findOrFail($validated['room_id']);
        if ($room->institution_id !== $user->institution_id) {
            return back()->withErrors(['room_id' => 'Room does not belong to your institution.'])->withInput();
        }

        // Verify course belongs to student if specified
        if ($validated['course_id'] && !$user->courses->contains($validated['course_id'])) {
            return back()->withErrors(['course_id' => 'You are not enrolled in this course.'])->withInput();
        }

        StudentRoomRequest::create([
            'student_id' => $user->id,
            'room_id' => $validated['room_id'],
            'course_id' => $validated['course_id'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'institution_id' => $user->institution_id,
            'requested_date' => $validated['requested_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'purpose' => $validated['purpose'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('student.room-requests.index')
            ->with('success', 'Room booking request submitted. Waiting for admin approval.');
    }
}
