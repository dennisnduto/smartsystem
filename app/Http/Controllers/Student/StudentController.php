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
        $courses = $user->courses()->with('department')->get() ?? collect([]);
        
        // Get today's classes
        $today = max(1, min(5, (int)now()->dayOfWeekIso));
        $todayEntries = $this->getStudentTimetableEntries($user, $today) ?? collect([]);
        
        // Get all timetable entries for the week
        $allEntries = $this->getStudentTimetableEntries($user) ?? collect([]);
        $entriesByDay = $allEntries->groupBy('day_of_week')->map(function($col) {
            return $col->sortBy('slot')->values();
        }) ?? collect([]);
        
        // Get next lecture
        $nextLecture = $this->getNextLecture($user);

        return view('student.dashboard', compact('user', 'courses', 'todayEntries', 'allEntries', 'entriesByDay', 'nextLecture', 'today'));
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

        // Get published timetables for student's institution
        $timetables = Timetable::where('institution_id', $user->institution_id)
            ->where('status', 'published')
            ->latest()
            ->get();

        return view('student.timetable', compact('entries', 'entriesByDay', 'timetables', 'user'));
    }

    /**
     * View full institution timetable (all courses, published only)
     */
    public function fullTimetable(Request $request)
    {
        $user = $request->user();

        if (!$user->is_approved) {
            return redirect()->route('student.dashboard')->with('error', 'Your account is pending approval.');
        }

        // All published entries for this institution
        $entries = TimetableEntry::with(['unit', 'course', 'room', 'lecturer', 'timetable'])
            ->whereHas('timetable', function ($q) use ($user) {
                $q->where('institution_id', $user->institution_id)
                  ->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();

        $entriesByDay = $entries->groupBy('day_of_week')->map(function ($col) {
            return $col->sortBy('slot')->values();
        });

        $timetables = Timetable::where('institution_id', $user->institution_id)
            ->where('status', 'published')
            ->latest()
            ->get();

        return view('student.timetable-full', compact('entries', 'entriesByDay', 'timetables', 'user'));
    }

    /**
     * Check real-time room availability
     */
    public function rooms(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->is_approved) {
                return redirect()->route('student.dashboard')->with('error', 'Your account is pending approval.');
            }

            // Very simple real-time availability: show rooms not used in the current slot
            $now = now()->setTimezone('Africa/Nairobi'); // Set to East Africa Time (UTC+3)
            $dayOfWeek = (int)$now->format('w'); // 0=Sunday, 1=Monday, ..., 6=Saturday
            $hour = (int)$now->format('H');
            
            // Logical time slot calculation
            $day = $dayOfWeek;
            $slot = 1; // Default
            
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) { // Weekdays only (1=Monday to 5=Friday)
                if ($hour >= 7 && $hour < 10) {
                    $slot = 1; // 7:00am-10:00am
                } elseif ($hour >= 10 && $hour < 13) {
                    $slot = 2; // 10:00am-1:00pm
                } elseif ($hour >= 13 && $hour < 16) {
                    $slot = 3; // 1:00pm-4:00pm
                } elseif ($hour >= 16 && $hour < 19) {
                    $slot = 4; // 4:00pm-7:00pm
                } else {
                    // Outside class hours (before 7am or after 7pm)
                    $slot = 0; // No active slot
                }
            } else {
                // Weekend - no scheduled classes
                $slot = 0; // No active slot
            }
            
            // Only check timetable entries if we're in an active time slot on weekdays
            $busyRoomIds = collect();
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5 && $slot > 0) {
                $busyRoomIds = TimetableEntry::where('day_of_week', $day)
                    ->where('slot', $slot)
                    ->whereHas('timetable', function($q) use ($user) {
                        $q->where('institution_id', $user->institution_id)
                          ->whereIn('status', ['approved', 'published']);
                    })
                    ->pluck('room_id');
            }

            // Also check room bookings with precise current timestamp - only currently active bookings
            $currentDateTime = now();
            $currentTime = $currentDateTime->format('H:i:s');
            $currentDate = $currentDateTime->toDateString();
            
            $bookingBusyRoomIds = \App\Models\RoomBooking::where('institution_id', $user->institution_id)
                ->where('status', 'active')
                ->where('booking_date', $currentDate)
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>', $currentTime) // Only currently active bookings
                ->pluck('room_id');

            $allBusyRoomIds = $busyRoomIds->merge($bookingBusyRoomIds)->unique();

            $availableRooms = \App\Models\Room::where('institution_id', $user->institution_id)
                ->whereNotIn('id', $allBusyRoomIds)
                ->orderBy('name')
                ->get();

            // Get all rooms for the complete list
            $allRooms = \App\Models\Room::where('institution_id', $user->institution_id)
                ->orderBy('name')
                ->get();

            // Handle AJAX requests for real-time updates
            if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'available_rooms' => $availableRooms,
                    'total_rooms' => $allRooms->count(),
                    'available_count' => $availableRooms->count(),
                    'timestamp' => $now->format('H:i:s'),
                    'day' => $day,
                    'slot' => $slot,
                    'is_active_slot' => $slot > 0
                ]);
            }

            return view('student.rooms', compact('user', 'availableRooms', 'allRooms', 'day', 'slot', 'now'));
            
        } catch (\Exception $e) {
            // Fallback: show all rooms if there's an error
            $user = $request->user();
            $allRooms = \App\Models\Room::where('institution_id', $user->institution_id)
                ->orderBy('name')
                ->get();
            
            if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'error' => 'Error loading room data',
                    'available_rooms' => $allRooms,
                    'total_rooms' => $allRooms->count(),
                    'available_count' => $allRooms->count(),
                    'timestamp' => now()->format('H:i:s'),
                    'slot' => 0,
                    'is_active_slot' => false
                ], 500);
            }
            
            return view('student.rooms', [
                'user' => $user,
                'availableRooms' => $allRooms,
                'allRooms' => $allRooms,
                'day' => now()->dayOfWeekIso,
                'slot' => 0,
                'now' => now()
            ]);
        }
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
                          ->where('status', 'published');
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
     * Update student profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_approved) {
            return redirect()->route('student.dashboard')->with('error', 'Your account is pending approval.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'year_of_study' => ['required', 'string', 'in:Y1,Y2,Y3,Y4,Y5'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'year_of_study' => $request->year_of_study,
        ]);

        return redirect()->route('student.dashboard')
            ->with('success', 'Profile updated successfully! Your timetable has been refreshed for your new year of study.');
    }

    /**
     * Get student's timetable entries (with shared unit sessions, no duplication)
     */
    private function getStudentTimetableEntries($user, $day = null)
    {
        // Get student's year of study
        $studentYear = $user->year_of_study;
        
        // Get courses the student is enrolled in
        $courseIds = $user->courses()->pluck('courses.id');
        
        // Return empty collection if no courses or no institution
        if ($courseIds->isEmpty() || !$user->institution_id) {
            return collect([]);
        }

        // Get timetable entries for student's courses/units
        // Filter by published timetables only and student's year of study
        $query = TimetableEntry::with(['unit', 'course', 'room', 'lecturer', 'timetable'])
            ->whereHas('timetable', function($q) use ($user) {
                $q->where('institution_id', $user->institution_id)
                  ->where('status', 'published');
            })
            ->where(function($q) use ($courseIds, $studentYear) {
                // Match by course_id first (simplified for debugging)
                $q->whereIn('course_id', $courseIds);
                
                // Only add year filtering if student has a year set
                if ($studentYear) {
                    $q->whereHas('unit', function($unitQuery) use ($studentYear) {
                        $unitQuery->whereHas('courseUnitYears', function($cuyQuery) use ($studentYear) {
                            $cuyQuery->where('academic_year', $studentYear);
                        });
                    });
                }
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
