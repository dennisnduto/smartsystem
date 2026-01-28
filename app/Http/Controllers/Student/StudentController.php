<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{TimetableEntry, Room, Unit, Course, Timetable};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;

class StudentController extends Controller
{
    private $timeSlots = [
        1 => '7:00am-10:00am',
        2 => '10:00am-1:00pm', 
        3 => '1:00pm-4:00pm',
        4 => '4:00pm-7:00pm'
    ];

    private $days = [
        1 => 'Monday',
        2 => 'Tuesday', 
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday'
    ];

    /**
     * Student Dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_approved) {
            return view('student.pending-approval', compact('user'));
        }

        // Get student's courses
        $courses = $user->courses()->with('department')->get();
        
        // Get today's classes
        $today = max(1, min(5, (int)now()->dayOfWeekIso));
        $todayEntries = $this->getStudentTimetableEntries($user, $today);
        
        // Get next lecture
        $nextLecture = $this->getNextLecture($user);

        return view('student.dashboard', compact('user', 'courses', 'todayEntries', 'nextLecture', 'today'));
    }

    /**
     * View timetable for student's course
     */
    public function timetable(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_approved) {
            return redirect()->route('student.dashboard')->with('error', 'Your account is pending approval.');
        }

        // Get all timetable entries for student's courses (with shared unit sessions, no duplication)
        $entries = $this->getStudentTimetableEntries($user);
        
        // Group by day for display
        $entriesByDay = $entries->groupBy('day_of_week')->map(function($col) {
            return $col->sortBy('slot')->values();
        });

        // Get active timetables (approved or published)
        $timetables = Timetable::where('institution_id', $user->institution_id)
            ->whereIn('status', ['approved', 'published'])
            ->latest()
            ->get();

        return view('student.timetable', compact('entries', 'entriesByDay', 'timetables', 'user'));
    }

    /**
     * Check real-time room availability
     */
    public function rooms(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_approved) {
            return redirect()->route('student.dashboard')->with('error', 'Your account is pending approval.');
        }

        $now = now();
        $day = max(1, min(5, (int)$now->dayOfWeekIso));
        $slot = $this->timeToSlot($now->format('H:i'));

        // Get rooms in use at current time
        $busyRoomIds = TimetableEntry::where('day_of_week', $day)
            ->where('slot', $slot)
            ->whereHas('timetable', function($q) use ($user) {
                $q->where('institution_id', $user->institution_id)
                  ->where('status', 'published');
            })
            ->pluck('room_id');

        // Also check room bookings
        $bookingBusyRoomIds = \App\Models\RoomBooking::where('institution_id', $user->institution_id)
            ->where('status', 'active')
            ->where('booking_date', $now->toDateString())
            ->where(function($q) use ($now) {
                $q->where('start_time', '<=', $now->format('H:i:s'))
                  ->where('end_time', '>=', $now->format('H:i:s'));
            })
            ->pluck('room_id');

        $allBusyRoomIds = $busyRoomIds->merge($bookingBusyRoomIds)->unique();

        // Get available rooms in student's institution
        $availableRooms = Room::where('institution_id', $user->institution_id)
            ->whereNotIn('id', $allBusyRoomIds)
            ->orderBy('name')
            ->get();

        return view('student.rooms', compact('availableRooms', 'day', 'slot', 'now'));
    }

    /**
     * Chatbot answers for students
     */
    public function chatbot(Request $request)
    {
        $user = $request->user();
        $query = strtolower((string)$request->input('q', ''));

        // Log the query
        \App\Models\ChatLog::create([
            'user_id' => $user->id,
            'role' => $user->role,
            'query' => $query,
            'response' => '', // Will be filled below
        ]);

        // "When is my next lecture?"
        if (str_contains($query, 'next lecture') || str_contains($query, 'next class')) {
            $nextLecture = $this->getNextLecture($user);
            if ($nextLecture) {
                $dayName = $this->days[$nextLecture->day_of_week] ?? 'Unknown';
                $timeSlot = $this->timeSlots[$nextLecture->slot] ?? 'Unknown';
                $answer = sprintf(
                    'Your next lecture is %s (%s) in %s on %s, %s.',
                    $nextLecture->unit->code ?? 'Unknown',
                    $nextLecture->unit->name ?? 'Unknown',
                    $nextLecture->room->name ?? 'TBA',
                    $dayName,
                    $timeSlot
                );
            } else {
                $answer = 'You have no upcoming lectures.';
            }
            
            // Log the response
            \App\Models\ChatLog::create([
                'user_id' => $user->id,
                'role' => $user->role,
                'query' => $query,
                'response' => $answer,
            ]);
            
            return response()->json(['answer' => $answer]);
        }

        // "Which room is free now?"
        if (str_contains($query, 'room') && (str_contains($query, 'free') || str_contains($query, 'available'))) {
            $now = now();
            $day = max(1, min(5, (int)$now->dayOfWeekIso));
            $slot = $this->timeToSlot($now->format('H:i'));

            $busyRoomIds = TimetableEntry::where('day_of_week', $day)
                ->where('slot', $slot)
                ->whereHas('timetable', function($q) use ($user) {
                    $q->where('institution_id', $user->institution_id)
                      ->whereIn('status', ['approved', 'published']);
                })
                ->pluck('room_id');

            // Also check room bookings
            $bookingBusyRoomIds = \App\Models\RoomBooking::where('institution_id', $user->institution_id)
                ->where('status', 'active')
                ->where('booking_date', $now->toDateString())
                ->where(function($q) use ($now) {
                    $q->where('start_time', '<=', $now->format('H:i:s'))
                      ->where('end_time', '>=', $now->format('H:i:s'));
                })
                ->pluck('room_id');

            $allBusyRoomIds = $busyRoomIds->merge($bookingBusyRoomIds)->unique();

            $availableRooms = Room::where('institution_id', $user->institution_id)
                ->whereNotIn('id', $allBusyRoomIds)
                ->orderBy('name')
                ->limit(10)
                ->pluck('name');

            if ($availableRooms->isNotEmpty()) {
                $answer = 'Available rooms right now: ' . $availableRooms->implode(', ');
            } else {
                $answer = 'No rooms are currently available.';
            }
            
            // Log the response
            \App\Models\ChatLog::create([
                'user_id' => $user->id,
                'role' => $user->role,
                'query' => $query,
                'response' => $answer,
            ]);
            
            return response()->json(['answer' => $answer]);
        }

        // "Who teaches SIT401?" or similar unit code queries
        if (preg_match('/who\s+teaches\s+([A-Z0-9]+)/i', $query, $matches)) {
            $unitCode = strtoupper($matches[1] ?? '');
            $unit = Unit::where('code', $unitCode)
                ->whereHas('timetableEntries.timetable', function($q) use ($user) {
                    $q->where('institution_id', $user->institution_id)
                      ->where('status', 'published');
                })
                ->first();

            if ($unit) {
                // Get lecturers teaching this unit
                $lecturers = TimetableEntry::where('unit_id', $unit->id)
                    ->whereHas('timetable', function($q) use ($user) {
                        $q->where('institution_id', $user->institution_id)
                          ->whereIn('status', ['approved', 'published']);
                    })
                    ->with('lecturer')
                    ->get()
                    ->pluck('lecturer.name')
                    ->filter()
                    ->unique()
                    ->values();

                if ($lecturers->isNotEmpty()) {
                    $answer = sprintf('%s is taught by: %s', $unitCode, $lecturers->implode(', '));
                } else {
                    $answer = sprintf('%s is in the system but no lecturer is currently assigned.', $unitCode);
                }
            } else {
                $answer = sprintf('Unit %s not found in your institution.', $unitCode);
            }
            return response()->json(['answer' => $answer]);
        }

        return response()->json([
            'answer' => 'Sorry, I did not understand. Try: "When is my next lecture?", "Which room is free now?", or "Who teaches SIT401?"'
        ]);
    }

    /**
     * Print timetable report
     */
    public function printTimetable(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_approved) {
            return redirect()->route('student.dashboard')->with('error', 'Your account is pending approval.');
        }

        $entries = $this->getStudentTimetableEntries($user);
        $entriesByDay = $entries->groupBy('day_of_week')->map(function($col) {
            return $col->sortBy('slot')->values();
        });

        $pdf = PdfFacade::loadView('student.timetable-pdf', [
            'user' => $user,
            'entries' => $entries,
            'entriesByDay' => $entriesByDay,
            'days' => $this->days,
            'timeSlots' => $this->timeSlots,
        ]);

        return $pdf->download('timetable-' . $user->name . '.pdf');
    }

    /**
     * Get student's timetable entries (with shared unit sessions, no duplication)
     */
    private function getStudentTimetableEntries($user, $day = null)
    {
        // Get student's course_unit_year IDs
        $courseUnitYearIds = $user->courseUnitYears()->pluck('course_unit_year.id');
        
        // Get courses the student is enrolled in
        $courseIds = $user->courses()->pluck('courses.id');

        // Get timetable entries for student's courses/units
        // Filter by approved/published timetables only
        $query = TimetableEntry::with(['unit', 'course', 'room', 'lecturer', 'timetable'])
            ->whereHas('timetable', function($q) use ($user) {
                $q->where('institution_id', $user->institution_id)
                  ->whereIn('status', ['approved', 'published']);
            })
            ->where(function($q) use ($courseUnitYearIds, $courseIds) {
                // Match by course_unit_year (through teaching groups or direct course/unit match)
                $q->whereIn('course_id', $courseIds)
                  ->orWhereIn('unit_id', function($subQuery) use ($courseUnitYearIds) {
                      $subQuery->select('unit_id')
                          ->from('course_unit_year')
                          ->whereIn('id', $courseUnitYearIds);
                  });
            });

        if ($day) {
            $query->where('day_of_week', $day);
        }

        $entries = $query->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();

        // Remove duplicates based on unit_id, day_of_week, and slot (shared unit sessions)
        $seen = [];
        return $entries->filter(function($entry) use (&$seen) {
            $key = $entry->unit_id . '-' . $entry->day_of_week . '-' . $entry->slot;
            if (isset($seen[$key])) {
                return false;
            }
            $seen[$key] = true;
            return true;
        })->values();
    }

    /**
     * Get next lecture for student
     */
    private function getNextLecture($user)
    {
        $now = now();
        $currentDay = max(1, min(5, (int)$now->dayOfWeekIso));
        $currentSlot = $this->timeToSlot($now->format('H:i'));

        $entries = $this->getStudentTimetableEntries($user);

        // Find next entry (same day later slot, or later day)
        $nextEntry = $entries->first(function($entry) use ($currentDay, $currentSlot) {
            if ($entry->day_of_week > $currentDay) {
                return true;
            }
            if ($entry->day_of_week == $currentDay && $entry->slot > $currentSlot) {
                return true;
            }
            return false;
        });

        return $nextEntry;
    }

    /**
     * Convert time to slot number
     */
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
