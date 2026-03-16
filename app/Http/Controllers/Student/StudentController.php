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
        
        // Get today's classes (Africa/Nairobi)
        $now = now()->setTimezone('Africa/Nairobi');
        $today = (int)$now->dayOfWeekIso;
        $todayEntries = ($today >= 1 && $today <= 5) ? $this->getStudentTimetableEntries($user, $today) : collect([]);
        
        // Get all timetable entries for the week
        $allEntries = $this->getStudentTimetableEntries($user) ?? collect([]);
        $entriesByDay = $allEntries->groupBy('day_of_week')->map(function($col) {
            return $col->sortBy('slot')->values();
        }) ?? collect([]);
        
        // Get next lecture
        $nextLecture = $this->getNextLecture($user);

        // Get available rooms count for initial load
        $availableRoomsCount = $this->getAvailableRoomsCount($user, $now);

        return view('student.dashboard', compact('user', 'courses', 'todayEntries', 'allEntries', 'entriesByDay', 'nextLecture', 'today', 'availableRoomsCount'));
    }

    /**
     * Get dashboard stats in JSON for real-time updates
     */
    public function dashboardStats(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_approved) {
            return response()->json(['error' => 'Unapproved'], 403);
        }

        $now = now()->setTimezone('Africa/Nairobi');
        $today = (int)$now->dayOfWeekIso;
        $currentTime = $now->format('H:i');
        $currentDate = $now->toDateString();
        $currentSlot = $this->timeToSlot($currentTime);
        
        $todayEntries = $this->getStudentTimetableEntries($user, $today);
        $nextLecture = $this->getNextLecture($user);
        
        $timeSlots = [1=>'7:00am-10:00am', 2=>'10:00am-1:00pm', 3=>'1:00pm-4:00pm', 4=>'4:00pm-7:00pm'];

        $formattedToday = $todayEntries->map(function($entry) use ($currentSlot, $timeSlots) {
            $status = 'UPCOMING';
            if ($entry->slot == $currentSlot) $status = 'NOW';
            elseif ($entry->slot < $currentSlot) $status = 'COMPLETED';
            
            return [
                'unit_code' => $entry->unit->code ?? 'Unknown',
                'unit_name' => $entry->unit->name ?? 'Unknown',
                'time' => $timeSlots[$entry->slot] ?? 'Unknown',
                'slot' => $entry->slot,
                'room' => $entry->room->name ?? 'TBA',
                'lecturer' => $entry->lecturer->name ?? null,
                'status' => $status
            ];
        });

        $availableRoomsCount = $this->getAvailableRoomsCount($user, $now);

        return response()->json([
            'live_time' => $now->format('H:i:s'),
            'today_classes_count' => $todayEntries->count(),
            'available_rooms_count' => $availableRoomsCount,
            'next_lecture' => $nextLecture ? [
                'unit_code' => $nextLecture->unit->code ?? 'Unknown',
                'unit_name' => $nextLecture->unit->name ?? 'Unknown',
                'lecturer' => $nextLecture->lecturer->name ?? 'TBA',
                'day' => $this->days[$nextLecture->day_of_week] ?? 'Unknown',
                'time' => $timeSlots[$nextLecture->slot] ?? 'Unknown',
                'room' => $nextLecture->room->name ?? 'TBA'
            ] : null,
            'today_sessions' => $formattedToday,
            'is_weekend' => $today >= 6
        ]);
    }

    /**
     * Get available rooms count based on currently occupied rooms (timetable + bookings)
     */
    private function getAvailableRoomsCount($user, $now)
    {
        $today = (int)$now->dayOfWeekIso;
        $currentTime = $now->format('H:i');
        $currentDate = $now->toDateString();
        $currentSlot = $this->timeToSlot($currentTime);

        $busyRoomIds = TimetableEntry::where('day_of_week', $today)
            ->where('slot', $currentSlot)
            ->whereHas('timetable', function($q) use ($user) {
                $q->where('institution_id', $user->institution_id)
                  ->where('status', 'published');
            })
            ->pluck('room_id');

        $bookingBusyRoomIds = \App\Models\RoomBooking::where('institution_id', $user->institution_id)
            ->where('status', 'active')
            ->where('booking_date', $currentDate)
            ->where('start_time', '<=', $currentTime . ':59')
            ->where('end_time', '>', $currentTime)
            ->pluck('room_id');

        $allBusyRoomIds = $busyRoomIds->merge($bookingBusyRoomIds)->unique();

        return \App\Models\Room::where('institution_id', $user->institution_id)
            ->whereNotIn('id', $allBusyRoomIds)
            ->count();
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
            ->get();

        // Enforce academic year retrieval with fallback
        $entries = $entries->map(function($entry) {
            $cuy = \Illuminate\Support\Facades\DB::table('course_unit_year')
                ->where('course_id', $entry->course_id)
                ->where('unit_id', $entry->unit_id)
                ->first();
            
            // Priority: course_unit_year pivot > Timetable model > "N/A"
            $entry->academic_year = $cuy->academic_year ?? $entry->timetable->academic_year ?? 'N/A';
            return $entry;
        });

        // Pivot into Matrix: matrix[day][slot][programKey]
        $matrix = [];
        $programs = [];
        
        foreach ($entries as $entry) {
            // Prefer the official course code if available, fallback to manual abbreviation
            $courseName = !empty($entry->course->code) 
                ? strtoupper($entry->course->code) 
                : $this->abbreviateCourseName($entry->course->name ?? 'Unknown Course');
                
            $academicYear = $entry->academic_year ?? $entry->timetable->academic_year ?? 'N/A';
            
            // Key by Course first, then year for program-major sorting
            $key = $courseName . ' | ' . $academicYear;
            
            if (!isset($programs[$key])) {
                $programs[$key] = [
                    'course' => $courseName,
                    'year' => $academicYear,
                ];
            }
            
            $matrix[$entry->day_of_week][$entry->slot][$key] = $entry;
        }

        // Sort programs naturally by Course then Year
        uksort($programs, function($a, $b) {
            return strnatcmp($a, $b);
        });

        // Group programs by course before chunking
        $programsByCourse = [];
        foreach ($programs as $key => $details) {
            $course = $details['course'];
            $programsByCourse[$course][$key] = $details;
        }

        // Chunk within each course to maintain separation
        $programChunks = [];
        foreach ($programsByCourse as $course => $coursePrograms) {
            $chunks = array_chunk($coursePrograms, 8, true);
            foreach ($chunks as $chunk) {
                // Attach course info for Web headers
                $programChunks[] = [
                    'course' => $course,
                    'programs' => $chunk
                ];
            }
        }

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
        $slots = [
            1 => '7:00-10:00', 
            2 => '10:00-13:00', 
            3 => '13:00-16:00', 
            4 => '16:00-19:00'
        ];

        return view('student.timetable-full', [
            'programChunks' => $programChunks,
            'matrix' => $matrix,
            'days' => $days,
            'slots' => $slots,
            'user' => $user
        ]);
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
            $dayOfWeekIso = (int)$now->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
            $hour = (int)$now->format('H');
            
            // Logical time slot calculation
            $day = $dayOfWeekIso;
            if ($dayOfWeekIso >= 1 && $dayOfWeekIso <= 5) { // Weekdays only
                if ($hour >= 7 && $hour < 10) {
                    $activeSlot = 1;
                } elseif ($hour >= 10 && $hour < 13) {
                    $activeSlot = 2;
                } elseif ($hour >= 13 && $hour < 16) {
                    $activeSlot = 3;
                } elseif ($hour >= 16 && $hour < 19) {
                    $activeSlot = 4;
                }
            }
            
            // Only check timetable entries if we're in an active time slot on weekdays
            $busyRoomIds = collect();
            if ($dayOfWeekIso >= 1 && $dayOfWeekIso <= 5 && $activeSlot >= 1 && $activeSlot <= 4) {
                $busyRoomIds = TimetableEntry::where('day_of_week', $day)
                    ->where('slot', $activeSlot)
                    ->whereHas('timetable', function($q) use ($user) {
                        $q->where('institution_id', $user->institution_id)
                          ->where('status', 'published');
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
                ->where('start_time', '<=', $currentTime . ':59')
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

            // Returning full view as the JS expects HTML to extract the container
            return view('student.rooms', compact('user', 'availableRooms', 'allRooms', 'day', 'activeSlot', 'now'));
            
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
                'activeSlot' => 0,
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
        $query = $request->input('q', '');

        if (empty($query)) {
            return response()->json(['answer' => 'Please ask me something about your schedule, classes, or available rooms.']);
        }

        $aiService = new \App\Services\StudentAIService();
        $answer = $aiService->generateResponse($query, $user);

        // Log the query and response
        \App\Models\ChatLog::create([
            'user_id' => $user->id,
            'role' => $user->role,
            'query' => $query,
            'response' => $answer,
        ]);

        return response()->json(['answer' => $answer]);
    }

    /**
     * Clear chatbot history
     */
    public function clearChat(Request $request)
    {
        $user = $request->user();
        \App\Models\ChatLog::where('user_id', $user->id)->delete();
        
        return response()->json(['message' => 'Chat history cleared.']);
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
        $now = now()->setTimezone('Africa/Nairobi');
        $currentDay = (int)$now->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
        $currentTime = $now->format('H:i');

        // If it's a weekend, Monday's first class is the next lecture
        if ($currentDay >= 6) {
            return $this->getStudentTimetableEntries($user)->sortBy(['day_of_week', 'slot'])->first();
        }

        $currentSlot = $this->timeToSlot($currentTime);
        $entries = $this->getStudentTimetableEntries($user);

        // Find next entry:
        // 1. Same day, later slot
        // 2. Later day this week
        // 3. Next week (if no entries left this week)
        
        $nextEntry = $entries->first(function($entry) use ($currentDay, $currentSlot, $currentTime) {
            // Same day, strictly later slot
            if ($entry->day_of_week == $currentDay) {
                // If we are currently IN a slot, the next one MUST have a higher slot number
                return $entry->slot > $currentSlot;
            }
            // Later day this week
            return $entry->day_of_week > $currentDay;
        });

        // Special case: If it's before any class has started today, the first class today is the next one
        if (!$nextEntry && $currentDay <= 5 && $currentTime < '07:00') {
            $nextEntry = $entries->where('day_of_week', $currentDay)->sortBy('slot')->first();
        }

        // Final fallback: First class of the week (wrap around)
        if (!$nextEntry) {
            $nextEntry = $entries->sortBy(['day_of_week', 'slot'])->first();
        }

        return $nextEntry;
    }

    /**
     * Convert time to slot number with precision
     */
    private function timeToSlot(string $time): int
    {
        // 1: 07:00 - 09:59
        // 2: 10:00 - 12:59
        // 3: 13:00 - 15:59
        // 4: 16:00 - 18:59
        // Beyond 19:00, we're past all slots (return 5 to ensure slot > currentSlot logic works)
        
        if ($time < '07:00') return 0;
        if ($time < '10:00') return 1;
        if ($time < '13:00') return 2;
        if ($time < '16:00') return 3;
        if ($time < '19:00') return 4;
        return 5; 
    }

    /**
     * Abbreviate long course names to save space in dense views.
     */
    private function abbreviateCourseName($name)
    {
        $replacements = [
            'Bachelor of Science in Software Engineering' => 'BSE',
            'Bachelor of Science Software Engineering' => 'BSE',
            'Software Engineering' => 'BSE',
            
            'Bachelor of Science in Computer Science' => 'BCS',
            'Bachelor of Science Computer Science' => 'BCS',
            'Computer Science' => 'BCS',
            
            'Bachelor of Science in Computer Technology' => 'BST',
            'Bachelor of Science Computer Technology' => 'BST',
            'Computer Technology' => 'BST',
            
            'Bachelor of Business Information Technology' => 'BBIT',
            'Business Information Technology' => 'BBIT',
            
            'Bachelor of Science in Information Technology' => 'BIT',
            'Bachelor of Science Information Technology' => 'BIT',
            'Information Technology' => 'BIT',
            
            'Bachelor of Commerce ' => 'BCOM ',
            'Bachelor of Business Administration' => 'BBA',
            'Bachelor of Arts in ' => 'BA ',
            'Analytical Chemistry' => 'AC',
            'Industrial Chemistry' => 'IC',
        ];

        $abbr = str_ireplace(array_keys($replacements), array_values($replacements), $name);
        return trim($abbr);
    }
}
