<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Models\TimetableEntry;
use App\Models\LecturerAvailability;
use App\Services\AIChatbotService;

class SelfServiceController extends Controller
{
    public function timetable(Request $request)
    {
        $user = $request->user();
        
        // Check if user has lecturer_id
        if (!$user->lecturer_id) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not assigned as a lecturer. Please contact your administrator.');
        }
        
        // Load entries for this lecturer from published timetables only
        $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
            ->where('lecturer_id', $user->lecturer_id)
            ->whereHas('timetable', function($q) {
                $q->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();
        // Load availability from lecturer_availability table
        $availabilityRows = LecturerAvailability::where('lecturer_id', $user->lecturer_id)->get();
        $availability = [];
        foreach ($availabilityRows as $row) {
            $availability[(int) $row->day][(int) $row->slot] = $row->status;
        }

        
        // Count lab sessions for this lecturer (units scheduled in lab rooms)
        try {
            $labCount = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
                ->where('lecturer_id', $user->lecturer_id)
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

        return view('lecturer.timetable', [
            'entries' => $entriesWithYear,
            'entriesByDay' => $entriesByDay,
            'availability' => $availability,
            'labCount' => $labCount,
            'user' => $user
        ]);
    }

    public function toggleAvailability(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'day' => 'required|integer|min:1|max:5',
            'slot' => 'required|integer|min:1|max:4',
        ]);

        $lecturerId = $user->lecturer_id;
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
            $now = now();
            $dayOfWeek = (int)$now->dayOfWeekIso; // 1=Monday, 7=Sunday
            
            // Only check timetable entries on weekdays (Monday-Friday)
            $busyRoomIds = collect();
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) { // Weekdays only
                $day = $dayOfWeek;
                
                // Simple time to slot calculation
                $h = (int)$now->format('H');
                $slot = $h < 10 ? 1 : ($h < 13 ? 2 : ($h < 16 ? 3 : 4));

                $busyRoomIds = TimetableEntry::where('day_of_week', $day)
                    ->where('slot', $slot)
                    ->whereHas('timetable', function($q) {
                        $q->whereIn('status', ['approved', 'published']);
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

    public function timeToSlot(string $time): int
    {
        $t = strtotime($time);
        $h = (int)date('H', $t);
        if ($h < 10) return 1;
        if ($h < 13) return 2;
        if ($h < 16) return 3;
        return 4;
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
            ->where('lecturer_id', $user->lecturer_id)
            ->whereHas('timetable', function($q) {
                $q->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();

        $csvData = [];
        $csvData[] = ['Day', 'Slot', 'Time', 'Unit Code', 'Unit Name', 'Course', 'Year', 'Room'];

        foreach ($entries as $entry) {
            $timeSlots = [1=>'7:00-10:00',2=>'10:00-13:00',3=>'13:00-16:00',4=>'16:00-19:00'];
            $dayNames = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
            
            $csvData[] = [
                $dayNames[$entry->day_of_week] ?? 'Day ' . $entry->day_of_week,
                $entry->slot,
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
            ->where('lecturer_id', $user->lecturer_id)
            ->whereHas('timetable', function($q) {
                $q->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();

        // Group by day for better organization
        $entriesByDay = $entries->groupBy('day_of_week')->map(function($col) {
            return $col->sortBy('slot')->values();
        });

        $filename = 'timetable_' . $user->name . '_' . now()->format('Y-m-d') . '.pdf';
        
        // Generate simple HTML for PDF conversion
        $html = view('lecturer.timetable-pdf', compact('entries', 'entriesByDay', 'user'))->render();

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
}


