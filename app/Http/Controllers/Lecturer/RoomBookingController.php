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
            ->get();

        if ($request->expectsJson()) {
            return response()->json($bookings->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'room' => $booking->room,
                    'purpose' => $booking->purpose,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'status' => $booking->status,
                    'can_cancel' => $booking->status === 'active' && $booking->booking_date->isFuture() || 
                                   ($booking->booking_date->isToday() && now()->format('H:i:s') < $booking->end_time->format('H:i:s')),
                ];
            }));
        }

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

        // Handle quick booking with duration
        if ($request->has('duration')) {
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'duration' => 'required|integer|min:1|max:3',
                'purpose' => 'required|string|max:255',
            ]);

            $now = now();
            $startTime = $now->format('H:i');
            $endTime = $now->copy()->addHours((int)$validated['duration'])->format('H:i');
            $bookingDate = $now->toDateString();

            // Check if end time goes beyond 19:00 (end of day)
            if (strtotime($endTime) > strtotime('19:00')) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Booking would extend beyond 19:00. Please choose a shorter duration.'], 422);
                }
                return back()->withErrors(['duration' => 'Booking would extend beyond 19:00. Please choose a shorter duration.'])->withInput();
            }
        } else {
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

            $startTime = $validated['start_time'];
            $endTime = $validated['end_time'];
            $bookingDate = $validated['booking_date'];
        }

        // Verify room belongs to lecturer's institution
        $room = Room::findOrFail($validated['room_id']);
        if ($room->institution_id !== $user->institution_id) {
            $message = 'Room does not belong to your institution.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->withErrors(['room_id' => $message])->withInput();
        }

        // Check for double-booking
        $overlapping = RoomBooking::where('room_id', $validated['room_id'])
            ->where('status', 'active')
            ->where('booking_date', $bookingDate)
            ->get()
            ->filter(function($booking) use ($bookingDate, $startTime, $endTime) {
                return $booking->isOverlapping(
                    \Carbon\Carbon::parse($bookingDate),
                    $startTime,
                    $endTime
                );
            });

        if ($overlapping->isNotEmpty()) {
            $message = 'Room is already booked during this time.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->withErrors(['start_time' => $message])->withInput();
        }

        // Check for timetable entry conflicts
        $conflict = DB::table('timetable_entries')
            ->join('timetables', 'timetables.id', '=', 'timetable_entries.timetable_id')
            ->where('timetable_entries.room_id', $validated['room_id'])
            ->where('timetables.institution_id', $user->institution_id)
            ->whereIn('timetables.status', ['approved', 'published'])
            ->where('timetable_entries.day_of_week', \Carbon\Carbon::parse($bookingDate)->dayOfWeekIso)
            ->where(function($q) use ($startTime) {
                $slot = $this->timeToSlot($startTime);
                $q->where('timetable_entries.slot', $slot);
            })
            ->exists();

        if ($conflict) {
            $message = 'Room is scheduled in the timetable during this time.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->withErrors(['start_time' => $message])->withInput();
        }

        $booking = RoomBooking::create([
            'room_id' => $validated['room_id'],
            'lecturer_id' => $user->id,
            'course_id' => $validated['course_id'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'institution_id' => $user->institution_id,
            'booking_date' => $bookingDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'purpose' => $validated['purpose'] ?? 'Special Session',
            'notes' => $validated['notes'] ?? null,
            'status' => 'active',
        ]);

        // Set auto-release time for quick bookings
        if ($request->has('duration')) {
            $booking->update([
                'auto_released_at' => \Carbon\Carbon::parse($bookingDate . ' ' . $endTime)
            ]);
        }

        // Notify students if course/unit is specified
        if ($booking->course_id || $booking->unit_id) {
            $this->notifyStudents($booking);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Room booked successfully!',
                'booking' => $booking->load('room')
            ]);
        }

        return redirect()->route('lecturer.room-bookings.index')
            ->with('success', 'Room booked successfully. Students have been notified.');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        // Find the booking manually to avoid route model binding issues
        $booking = RoomBooking::find($id);
        
        if (!$booking) {
            \Log::warning('Booking not found for cancellation', ['booking_id' => $id]);
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Booking not found'], 404);
            }
            abort(404, 'Booking not found');
        }
        
        // Debug: Log the IDs for troubleshooting
        \Log::info('Booking cancellation attempt', [
            'booking_id' => $booking->id,
            'booking_lecturer_id' => $booking->lecturer_id,
            'current_user_id' => $user->id,
            'user_email' => $user->email
        ]);
        
        if ($booking->lecturer_id !== $user->id) {
            \Log::warning('Unauthorized booking cancellation attempt', [
                'booking_id' => $booking->id,
                'booking_lecturer_id' => $booking->lecturer_id,
                'current_user_id' => $user->id
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized - You can only cancel your own bookings'], 403);
            }
            abort(403, 'You can only cancel your own bookings');
        }

        $booking->update(['status' => 'cancelled']);
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Booking cancelled successfully.']);
        }
        
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
