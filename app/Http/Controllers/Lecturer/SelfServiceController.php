<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Models\TimetableEntry;
use App\Models\LecturerAvailability;
use App\Services\AIChatbotService;

class SelfServiceController extends Controller
{
    public function dashboard(Request $request)
    {
        return $this->renderLecturerView($request, 'lecturer.dashboard');
    }

    public function timetable(Request $request)
    {
        return $this->renderLecturerView($request, 'lecturer.timetable');
    }

    private function renderLecturerView(Request $request, string $viewName)
    {
        $user = $request->user();
        
        // Check if user has lecturer_id
        if (!$user->lecturer_id) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not assigned as a lecturer. Please contact your administrator.');
        }

        try {
        
        // Load entries for this lecturer from published timetables only
        $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
            ->where('lecturer_id', $user->id)
            ->whereHas('timetable', function($q) {
                $q->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();
        // Load availability from lecturer_availability table
        $availabilityRows = LecturerAvailability::where('lecturer_id', $user->id)->get();
        $availability = [];
        foreach ($availabilityRows as $row) {
            $availability[(int) $row->day][(int) $row->slot] = $row->status;
        }

        
        // Count lab sessions for this lecturer (units scheduled in lab rooms)
        try {
            $labCount = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
                ->where('lecturer_id', $user->id)
                ->whereHas('timetable', function($q) {
                    $q->where('status', 'published');
                })
                ->whereHas('room', function($q) {
                    $q->where('room_type', 'LIKE', '%lab%')
                      ->orWhere('room_type', 'LIKE', '%Laboratory%')
                      ->orWhere('room_type', 'LIKE', '%LAB%');
                })
                ->distinct('unit_id')
                ->count('unit_id');
        } catch (\Exception $e) {
            $labCount = 0;
        }

        // Add year of study to each entry
        $entriesWithYear = $entries->map(function($entry) {
            $yearOfStudy = null;
            if ($entry->unit_id && $entry->course_id) {
                $courseUnitYear = DB::table('course_unit_year')
                    ->where('course_id', $entry->course_id)
                    ->where('unit_id', $entry->unit_id)
                    ->first();
                $yearOfStudy = $courseUnitYear ? $courseUnitYear->academic_year : null;
            }
            $entry->year_of_study = $yearOfStudy;
            return $entry;
        });

        // Regroup entries with year data
        $entriesByDay = $entriesWithYear->groupBy('day_of_week')->map(function($col) {
            return $col->sortBy('slot')->values();
        });

        return view($viewName, [
            'entries' => $entriesWithYear,
            'entriesByDay' => $entriesByDay,
            'availability' => $availability,
            'labCount' => $labCount,
            'user' => $user
        ]);

        } catch (\Throwable $e) {
            Log::error('Lecturer dashboard failed to render', [
                'user_id' => $user->id ?? null,
                'lecturer_id' => $user->lecturer_id ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return view($viewName, [
                'entries' => collect([]),
                'entriesByDay' => collect([]),
                'availability' => [],
                'labCount' => 0,
                'user' => $user,
                'renderError' => app()->hasDebugModeEnabled() ? $e->getMessage() : 'Unable to load dashboard data.'
            ]);
        }
    }

    public function toggleAvailability(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'day' => 'required|integer|min:1|max:5',
            'slot' => 'required|integer|min:1|max:4',
        ]);

        $lecturerId = $user->id;
        $day = (int) $data['day'];
        $slot = (int) $data['slot'];

        $record = LecturerAvailability::where('lecturer_id', $lecturerId)
            ->where('day', $day)
            ->where('slot', $slot)
            ->first();

        // auto_busy slots are locked (scheduled classes)
        if ($record && $record->status === 'auto_busy') {
            return response()->json([
                'status' => $record->status,
                'locked' => true,
            ]);
        }

        if (!$record) {
            // No row -> currently unavailable; first toggle makes it explicitly available
            $record = LecturerAvailability::create([
                'lecturer_id' => $lecturerId,
                'day' => $day,
                'slot' => $slot,
                'status' => 'available',
            ]);
        } else {
            // Toggle between available and busy
            $record->status = $record->status === 'available' ? 'busy' : 'available';
            $record->save();
        }

        return response()->json([
            'status' => $record->status,
            'locked' => $record->status === 'auto_busy',
        ]);
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
        try {
            $user = $request->user();
            
            // Very simple real-time availability: show rooms not used in the current slot
            $now = now()->setTimezone('Africa/Nairobi'); 
            $dayOfWeekIso = (int)$now->dayOfWeekIso; 
            $currentTime = $now->format('H:i');
            $currentDate = $now->toDateString();
            $activeSlot = $this->timeToSlot($currentTime);
            
            // Only check timetable entries if we're in an active time slot on weekdays
            $busyRoomIds = collect();
            if ($dayOfWeekIso >= 1 && $dayOfWeekIso <= 5 && $activeSlot >= 1 && $activeSlot <= 4) {
                $busyRoomIds = TimetableEntry::where('day_of_week', $dayOfWeekIso)
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
            
            // Get user's bookings
            $myBookings = \App\Models\RoomBooking::where('lecturer_id', $user->id)
                ->where('status', 'active')
                ->where('booking_date', '>=', now()->toDateString())
                ->with('room')
                ->orderBy('booking_date')
                ->orderBy('start_time')
                ->get();

            return view('lecturer.rooms-simple', compact('availableRooms', 'allRooms', 'myBookings'));
            
        } catch (\Exception $e) {
            // Fallback: show all rooms if there's an error
            $user = $request->user();
            $allRooms = \App\Models\Room::where('institution_id', $user->institution_id)
                ->orderBy('name')
                ->get();
            
            return view('lecturer.rooms-simple', [
                'availableRooms' => $allRooms,
                'allRooms' => $allRooms,
                'myBookings' => collect([])
            ]);
        }
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
        $question = $request->input('q', '');

        if (empty($question)) {
            return response()->json(['answer' => 'Please ask me something about your schedule.']);
        }

        $aiService = new AIChatbotService();
        $response = $aiService->generateResponse($question, $user);

        return response()->json(['answer' => $response]);
    }

    public function clearChat(Request $request)
    {
        $user = $request->user();
        \App\Models\ChatLog::where('user_id', $user->id)->delete();
        
        return response()->json(['message' => 'Chat history cleared.']);
    }

    public function timeToSlot(string $time): int
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

    public function getYearOfStudy($entry): ?string
    {
        if (!$entry->unit_id || !$entry->course_id) {
            return null;
        }

        $courseUnitYear = DB::table('course_unit_year')
            ->where('course_id', $entry->course_id)
            ->where('unit_id', $entry->unit_id)
            ->first();

        return $courseUnitYear ? $courseUnitYear->academic_year : null;
    }

    public function exportCSV(Request $request)
    {
        $user = $request->user();
        
        $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
            ->where('lecturer_id', $user->id)
            ->whereHas('timetable', function($q) {
                $q->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();

        $csvData = [];
        $csvData[] = ['Day', 'Time Slot', 'Unit Code', 'Unit Name', 'Course / Program', 'Year', 'Room'];

        foreach ($entries as $entry) {
            $timeSlots = [1=>'7:00 AM - 10:00 AM', 2=>'10:00 AM - 1:00 PM', 3=>'1:00 PM - 4:00 PM', 4=>'4:00 PM - 7:00 PM'];
            $dayNames = [1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday'];
            
            $csvData[] = [
                $dayNames[$entry->day_of_week] ?? 'Day ' . $entry->day_of_week,
                $timeSlots[$entry->slot] ?? 'Unknown',
                $entry->unit->code ?? '—',
                $entry->unit->name ?? '—',
                $entry->course->name ?? '—',
                $this->getYearOfStudy($entry) ?? '—',
                $entry->room->name ?? 'TBA'
            ];
        }

        $filename = 'timetable_' . $user->name . '_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPDF(Request $request)
    {
        $user = $request->user();
        
        $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
            ->where('lecturer_id', $user->id)
            ->whereHas('timetable', function($q) {
                $q->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();

        // Group simply by day
        $entriesByDay = $entries->groupBy('day_of_week');

        $timeSlots = [1=>'7:00-10:00', 2=>'10:00-13:00', 3=>'13:00-16:00', 4=>'16:00-19:00'];
        $dayNames = [1=>'Mon', 2=>'Tue', 3=>'Wed', 4=>'Thu', 5=>'Fri'];

        $filename = 'timetable_' . $user->name . '_' . now()->format('Y-m-d') . '.pdf';
        
        // Generate simple HTML for PDF conversion
        $html = view('lecturer.timetable-pdf', compact('entries', 'entriesByDay', 'user', 'timeSlots', 'dayNames'))->render();

        // Use DomPDF if available, otherwise fallback to simple download
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } else {
            // Fallback: return HTML as downloadable file
            return response($html, 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'attachment; filename="' . str_replace('.pdf', '.html', $filename) . '"',
            ]);
        }
    }
    /**
     * View full institution timetable (all courses, published only)
     */
    public function fullTimetable(Request $request)
    {
        $user = $request->user();

        // All published entries for this institution
        $entries = TimetableEntry::with(['unit', 'course', 'room', 'lecturer', 'timetable'])
            ->whereHas('timetable', function ($q) use ($user) {
                $q->where('institution_id', $user->institution_id)
                  ->where('status', 'published');
            })
            ->get();

        // Enforce academic year retrieval with fallback to Timetable level
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
        $slots = [1 => '7:00-10:00', 2 => '10:00-13:00', 3 => '13:00-16:00', 4 => '16:00-19:00'];

        return view('lecturer.timetable-full', [
            'programChunks' => $programChunks,
            'matrix' => $matrix,
            'days' => $days,
            'slots' => $slots,
            'user' => $user
        ]);
    }

    /**
     * Export the full institutional timetable
     */
    public function exportFullTimetable(Request $request, $format)
    {
        $user = $request->user();
        
        $entries = TimetableEntry::with(['unit', 'course', 'room', 'lecturer', 'timetable'])
            ->whereHas('timetable', function ($q) use ($user) {
                $q->where('institution_id', $user->institution_id)
                  ->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();

        // Enforce academic year retrieval with fallback to Timetable level
        $entries = $entries->map(function($entry) {
            $cuy = \Illuminate\Support\Facades\DB::table('course_unit_year')
                ->where('course_id', $entry->course_id)
                ->where('unit_id', $entry->unit_id)
                ->first();
            
            // Priority: course_unit_year pivot > Timetable model > "N/A"
            $entry->academic_year = $cuy->academic_year ?? $entry->timetable->academic_year ?? 'N/A';
            return $entry;
        });

        if ($format === 'csv') {
            $csvData = [];
            $csvData[] = ['Program / Course', 'Academic Year', 'Day', 'Time Slot', 'Unit Code', 'Unit Name', 'Lecturer', 'Room'];

            // Sort entries for CSV: Program -> Year -> Day -> Slot
            $entries = $entries->sortBy([
                ['course.name', 'asc'],
                ['academic_year', 'asc'],
                ['day_of_week', 'asc'],
                ['slot', 'asc']
            ]);

            foreach ($entries as $entry) {
                $dayNames = [1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday'];
                $timeSlots = [1=>'7:00 AM - 10:00 AM', 2=>'10:00 AM - 1:00 PM', 3=>'1:00 PM - 4:00 PM', 4=>'4:00 PM - 7:00 PM'];
                
                $csvData[] = [
                    $entry->course->name ?? '—',
                    $entry->academic_year ?? '—',
                    $dayNames[$entry->day_of_week] ?? 'Day ' . $entry->day_of_week,
                    $timeSlots[$entry->slot] ?? 'Unknown',
                    $entry->unit->code ?? '—',
                    $entry->unit->name ?? '—',
                    $entry->lecturer->name ?? '—',
                    $entry->room->name ?? 'TBA'
                ];
            }

            $filename = 'institution_wide_timetable_' . now()->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return response()->stream(function() use ($csvData) {
                $file = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            }, 200, $headers);
        }

        // PDF Matrix handle (Day/Slot rows, Program columns)
        $matrix = [];
        $programs = [];
        foreach ($entries as $entry) {
            // Prefer the official course code if available, fallback to manual abbreviation
            $courseName = !empty($entry->course->code) 
                ? strtoupper($entry->course->code) 
                : $this->abbreviateCourseName($entry->course->name ?? 'Unknown Course');
                
            $academicYear = $entry->academic_year ?? $entry->timetable->academic_year ?? 'N/A';
            
            // Key by Course first for PDF grouping
            $key = $courseName . ' | ' . $academicYear;
            
            if (!isset($programs[$key])) {
                $programs[$key] = [
                    'course' => $courseName,
                    'year' => $academicYear,
                ];
            }
            $matrix[$entry->day_of_week][$entry->slot][$key] = $entry;
        }
        // Sort programs naturally by Course then Year for PDF
        uksort($programs, function($a, $b) {
            return strnatcmp($a, $b);
        });

        // Group programs by course before chunking for PDF
        $programsByCourse = [];
        foreach ($programs as $key => $details) {
            $course = $details['course'];
            $programsByCourse[$course][$key] = $details;
        }

        // Chunk within each course to maintain separation in PDF
        $programChunks = [];
        foreach ($programsByCourse as $course => $coursePrograms) {
            $chunks = array_chunk($coursePrograms, 7, true);
            foreach ($chunks as $chunk) {
                // Attach course info for page titles
                $programChunks[] = [
                    'course' => $course,
                    'programs' => $chunk
                ];
            }
        }

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
        $slots = [1 => '7:00-10:00', 2 => '10:00-13:00', 3 => '13:00-16:00', 4 => '16:00-19:00'];

        $filename = 'institution_wide_timetable_' . now()->format('Y-m-d') . '.pdf';

        $html = view('lecturer.timetable-pdf', [
            'programChunks' => $programChunks,
            'matrix' => $matrix,
            'days' => $days,
            'slots' => $slots,
            'user' => $user,
            'title' => 'Institution Wide Timetable',
            'isInstitutional' => true
        ])->render();

        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . str_replace('.pdf', '.html', $filename) . '"',
        ]);
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


