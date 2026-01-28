<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\{StudentRoomRequest, RoomBooking};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentRoomRequestController extends Controller
{
    public function index()
    {
        $institution = auth()->user()->institution;
        
        $pendingRequests = StudentRoomRequest::where('institution_id', $institution->id)
            ->where('status', 'pending')
            ->with(['student', 'room', 'course', 'unit'])
            ->latest()
            ->get();

        $allRequests = StudentRoomRequest::where('institution_id', $institution->id)
            ->with(['student', 'room', 'course', 'unit', 'reviewedBy'])
            ->latest()
            ->paginate(15);

        return view('institution-admin.student-room-requests.index', compact('pendingRequests', 'allRequests', 'institution'));
    }

    public function approve(StudentRoomRequest $request)
    {
        $institution = auth()->user()->institution;
        
        if ($request->institution_id !== $institution->id) {
            abort(403);
        }

        // Check for conflicts
        $conflicts = $this->checkConflicts($request);
        if ($conflicts['has_conflict']) {
            return back()->with('error', $conflicts['message']);
        }

        $request->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('institution-admin.student-room-requests.index')
            ->with('success', 'Room request approved successfully.');
    }

    public function reject(Request $httpRequest, StudentRoomRequest $request)
    {
        $institution = auth()->user()->institution;
        
        if ($request->institution_id !== $institution->id) {
            abort(403);
        }

        $validated = $httpRequest->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        return redirect()->route('institution-admin.student-room-requests.index')
            ->with('success', 'Room request rejected.');
    }

    private function checkConflicts(StudentRoomRequest $request): array
    {
        // Check room bookings
        $bookingConflict = RoomBooking::where('room_id', $request->room_id)
            ->where('status', 'active')
            ->where('booking_date', $request->requested_date)
            ->get()
            ->filter(function($booking) use ($request) {
                return $booking->isOverlapping(
                    $request->requested_date,
                    $request->start_time->format('H:i:s'),
                    $request->end_time->format('H:i:s')
                );
            });

        if ($bookingConflict->isNotEmpty()) {
            return [
                'has_conflict' => true,
                'message' => 'Room is already booked during this time.'
            ];
        }

        // Check timetable entries
        $timetableConflict = DB::table('timetable_entries')
            ->join('timetables', 'timetables.id', '=', 'timetable_entries.timetable_id')
            ->where('timetable_entries.room_id', $request->room_id)
            ->where('timetables.institution_id', $request->institution_id)
            ->whereIn('timetables.status', ['approved', 'published'])
            ->where('timetable_entries.day_of_week', $request->requested_date->dayOfWeekIso)
            ->where(function($q) use ($request) {
                $slot = $this->timeToSlot($request->start_time->format('H:i'));
                $q->where('timetable_entries.slot', $slot);
            })
            ->exists();

        if ($timetableConflict) {
            return [
                'has_conflict' => true,
                'message' => 'Room is scheduled in the timetable during this time.'
            ];
        }

        return ['has_conflict' => false];
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
