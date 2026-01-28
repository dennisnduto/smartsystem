<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TimetableEntry;

class SelfServiceController extends Controller
{
    public function timetable(Request $request)
    {
        $user = $request->user();
        // Load today's and upcoming entries for this lecturer (only from approved/published timetables)
        $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
            ->where('lecturer_id', $user->lecturer_id)
            ->whereHas('timetable', function($q) {
                $q->whereIn('status', ['approved', 'published']);
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();
        // Load saved availability from lecturers table (JSON)
        $availability = [];
        $lect = DB::table('lecturers')->where('id', $user->lecturer_id)->first();
        if ($lect && !empty($lect->availability)) {
            $decoded = json_decode($lect->availability, true);
            if (is_array($decoded)) { $availability = $decoded; }
        }
        // If no saved availability, prefill a default template (all Free)
        if (empty($availability)) {
            for ($day = 1; $day <= 5; $day++) {
                for ($slot = 1; $slot <= 4; $slot++) {
                    $availability[$day][$slot] = true; // Free by default
                }
            }
        }
        // Auto-mark BUSY where there are scheduled classes (override Free)
        foreach ($entries as $e) {
            $d = (int)($e->day_of_week ?? 0);
            $s = (int)($e->slot ?? 0);
            if ($d >= 1 && $d <= 5 && $s >= 1 && $s <= 4) {
                $availability[$d][$s] = false; // Busy where teaching
            }
        }
        // Group by day for week-at-a-glance cards
        $entriesByDay = $entries->groupBy('day_of_week')->map(function($col) {
            return $col->sortBy('slot')->values();
        });

        return view('lecturer.timetable', compact('entries', 'entriesByDay', 'availability', 'user'));
    }

    public function updateAvailability(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'availability' => 'required|array',
        ]);

        DB::table('lecturers')->where('id', $user->lecturer_id)->update([
            'availability' => json_encode($data['availability']),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Availability updated');
    }

    public function assigned(Request $request)
    {
        $user = $request->user();
        $classes = DB::table('course_unit_year as cuy')
            ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
            ->join('courses as c', 'c.id', '=', 'cuy.course_id')
            ->join('units as u', 'u.id', '=', 'cuy.unit_id')
            ->where('cuyu.user_id', $user->id)
            ->select('c.name as course_name', 'u.code as unit_code', 'u.name as unit_name', 'cuy.academic_year', 'cuy.semester')
            ->orderBy('c.name')->orderBy('u.code')
            ->get();

        $rooms = \App\Models\Room::where('institution_id', $user->institution_id)
            ->orderBy('name')
            ->get();

        return view('lecturer.assigned', compact('classes', 'rooms'));
    }

    public function rooms(Request $request)
    {
        $user = $request->user();
        // Very simple real-time availability: show rooms not used in the current slot
        $now = now();
        $day = max(1, min(5, (int)$now->dayOfWeekIso));
        $slot = $this->timeToSlot($now->format('H:i'));

        $busyRoomIds = TimetableEntry::where('day_of_week', $day)
            ->where('slot', $slot)
            ->whereHas('timetable', function($q) {
                $q->whereIn('status', ['approved', 'published']);
            })
            ->pluck('room_id');

        // Also check room bookings
        $bookingBusyRoomIds = \App\Models\RoomBooking::where('institution_id', $user->institution_id)
            ->where('status', 'active')
            ->where('booking_date', now()->toDateString())
            ->where(function($q) {
                $now = now();
                $q->where('start_time', '<=', $now->format('H:i:s'))
                  ->where('end_time', '>=', $now->format('H:i:s'));
            })
            ->pluck('room_id');

        $allBusyRoomIds = $busyRoomIds->merge($bookingBusyRoomIds)->unique();

        $availableRooms = \App\Models\Room::where('institution_id', $user->institution_id)
            ->whereNotIn('id', $allBusyRoomIds)
            ->orderBy('name')
            ->get();

        return view('lecturer.rooms', compact('availableRooms', 'day', 'slot'));
    }

    public function requestChange(Request $request)
    {
        $data = $request->validate([
            'timetable_entry_id' => 'required|integer|exists:timetable_entries,id',
            'reason' => 'required|string|max:500'
        ]);

        DB::table('schedule_change_requests')->insert([
            'timetable_entry_id' => $data['timetable_entry_id'],
            'requested_by' => $request->user()->id,
            'reason' => $data['reason'],
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Request submitted');
    }

    public function chatbot(Request $request)
    {
        $user = $request->user();
        $q = strtolower((string)$request->input('q'));

        if (str_contains($q, 'next class')) {
            $now = now();
            $day = max(1, min(5, (int)$now->dayOfWeekIso));
            $entry = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
                ->where('lecturer_id', $user->lecturer_id)
                ->whereHas('timetable', function($q) {
                    $q->whereIn('status', ['approved', 'published']);
                })
                ->where(function($qq) use ($day) {
                    $qq->where('day_of_week', '>=', $day);
                })
                ->orderBy('day_of_week')->orderBy('slot')
                ->first();
            if ($entry) {
                return response()->json(['answer' => sprintf('Your next class is %s (%s) in %s on day %d slot %d.', $entry->unit->code, $entry->course->name ?? '—', $entry->room->name ?? 'TBA', $entry->day_of_week, $entry->slot)]);
            }
            return response()->json(['answer' => 'No upcoming classes found.']);
        }

        if (preg_match('/which\s+students\s+attend\s+(\w+)/i', $q, $m)) {
            $unitCode = $m[1] ?? null;
            // Placeholder: depends on student enrollment data
            return response()->json(['answer' => "Student roster for $unitCode is not connected yet."]);
        }

        if (str_contains($q, 'where am i teaching today')) {
            $today = max(1, min(5, (int)now()->dayOfWeekIso));
            $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
                ->where('lecturer_id', $user->lecturer_id)
                ->whereHas('timetable', function($q) {
                    $q->whereIn('status', ['approved', 'published']);
                })
                ->where('day_of_week', $today)
                ->orderBy('slot')->get();
            if ($entries->isEmpty()) {
                return response()->json(['answer' => 'You have no classes today.']);
            }
            $answer = $entries->map(fn($e) => sprintf('%s %s in %s (slot %d)', $e->unit->code, $e->course->name ?? '—', $e->room->name ?? 'TBA', $e->slot))->implode('; ');
            return response()->json(['answer' => $answer]);
        }

        return response()->json(['answer' => 'Sorry, I did not understand. Try: "When is my next class?"']);
    }

    private function timeToSlot(string $time): int
    {
        $t = strtotime($time);
        $h = (int)date('H', $t);
        if ($h < 10) return 1;
        if ($h < 13) return 2;
        if ($h < 16) return 3;
        return 4;
    }
}


