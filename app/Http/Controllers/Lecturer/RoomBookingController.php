<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\{RoomBooking, Room, Course, Unit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RoomBookingNotification;

class RoomBookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $bookings = RoomBooking::where('lecturer_id', $user->id)
            ->with(['room', 'course', 'unit'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->paginate(15);

        return view('lecturer.room-bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        
        $rooms = Room::where('institution_id', $user->institution_id)
            ->orderBy('name')
            ->get();

        $courses = Course::whereHas('department', function($q) use ($user) {
            $q->where('institution_id', $user->institution_id);
        })->orderBy('name')->get();

        $units = Unit::whereHas('timetableEntries.timetable', function($q) use ($user) {
            $q->where('institution_id', $user->institution_id)
              ->whereIn('status', ['approved', 'published']);
        })->orderBy('code')->get();

        return view('lecturer.room-bookings.create', compact('rooms', 'courses', 'units'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'course_id' => 'nullable|exists:courses,id',
            'unit_id' => 'nullable|exists:units,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify room belongs to lecturer's institution
        $room = Room::findOrFail($validated['room_id']);
        if ($room->institution_id !== $user->institution_id) {
            return back()->withErrors(['room_id' => 'Room does not belong to your institution.'])->withInput();
        }

        // Check for double-booking
        $overlapping = RoomBooking::where('room_id', $validated['room_id'])
            ->where('status', 'active')
            ->where('booking_date', $validated['booking_date'])
            ->get()
            ->filter(function($booking) use ($validated) {
                return $booking->isOverlapping(
                    \Carbon\Carbon::parse($validated['booking_date']),
                    $validated['start_time'],
                    $validated['end_time']
                );
            });

        if ($overlapping->isNotEmpty()) {
            return back()->withErrors(['start_time' => 'Room is already booked during this time.'])->withInput();
        }

        // Check for timetable entry conflicts
        $conflict = DB::table('timetable_entries')
            ->join('timetables', 'timetables.id', '=', 'timetable_entries.timetable_id')
            ->where('timetable_entries.room_id', $validated['room_id'])
            ->where('timetables.institution_id', $user->institution_id)
            ->whereIn('timetables.status', ['approved', 'published'])
            ->where('timetable_entries.day_of_week', \Carbon\Carbon::parse($validated['booking_date'])->dayOfWeekIso)
            ->where(function($q) use ($validated) {
                $slot = $this->timeToSlot($validated['start_time']);
                $q->where('timetable_entries.slot', $slot);
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'Room is scheduled in the timetable during this time.'])->withInput();
        }

        $booking = RoomBooking::create([
            'room_id' => $validated['room_id'],
            'lecturer_id' => $user->id,
            'course_id' => $validated['course_id'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'institution_id' => $user->institution_id,
            'booking_date' => $validated['booking_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'purpose' => $validated['purpose'] ?? 'Special Session',
            'notes' => $validated['notes'] ?? null,
            'status' => 'active',
        ]);

        // Notify students if course/unit is specified
        if ($booking->course_id || $booking->unit_id) {
            $this->notifyStudents($booking);
        }

        return redirect()->route('lecturer.room-bookings.index')
            ->with('success', 'Room booked successfully. Students have been notified.');
    }

    public function destroy(RoomBooking $booking)
    {
        if ($booking->lecturer_id !== auth()->id()) {
            abort(403);
        }

        $booking->update(['status' => 'cancelled']);
        
        return redirect()->route('lecturer.room-bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }

    private function notifyStudents(RoomBooking $booking)
    {
        $students = \App\Models\User::where('role', 'student')
            ->where('institution_id', $booking->institution_id)
            ->where('is_approved', true);

        if ($booking->course_id) {
            $students->whereHas('courses', function($q) use ($booking) {
                $q->where('courses.id', $booking->course_id);
            });
        }

        if ($booking->unit_id) {
            $students->whereHas('courseUnitYears', function($q) use ($booking) {
                $q->where('course_unit_year.unit_id', $booking->unit_id);
            });
        }

        $students = $students->get();

        foreach ($students as $student) {
            $student->notify(new RoomBookingNotification($booking));
        }
    }

    private function timeToSlot(string $time): int
    {
        $h = (int)date('H', strtotime($time));
        if ($h < 10) return 1;
        if ($h < 13) return 2;
        if ($h < 16) return 3;
        return 4;
    }
}
