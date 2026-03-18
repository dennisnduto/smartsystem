<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Models\LecturerAvailability;
use Illuminate\Support\Facades\DB;

class TimetableGenerator
{
    /**
     * Generate timetable entries for a specific timetable instance
     */
    public function generateForTimetable(Timetable $timetable)
    {
        // Convert timetable format to course_unit_year format
        $convertedAcademicYear = $this->convertAcademicYear($timetable->academic_year);
        $convertedSemester = $this->convertSemester($timetable->semester);

        // Load lecturer-unit-year assignments across the institution
        // Start with just institution filter - be lenient with year/semester matching
        $query = DB::table('course_unit_year as cuy')
            ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
            ->join('users as u', 'u.id', '=', 'cuyu.user_id')
            ->join('lecturers as L', 'L.id', '=', 'u.lecturer_id')
            ->join('courses as c', 'c.id', '=', 'cuy.course_id')
            ->join('departments as d', 'd.id', '=', 'c.department_id')
            ->where('u.institution_id', $timetable->institution_id);
        
        // Try to filter by academic year if conversion succeeded, but if no results, try without filter
        $assignments = null;
        if ($convertedAcademicYear) {
            $filteredQuery = clone $query;
            $filteredQuery->where('cuy.academic_year', $convertedAcademicYear);
            
            // For semester: match converted value OR null
            if ($convertedSemester) {
                $filteredQuery->where(function($q) use ($convertedSemester) {
                    $q->where('cuy.semester', $convertedSemester)
                      ->orWhereNull('cuy.semester');
                });
            }
            
            $assignments = $filteredQuery->select(
                    'cuy.id as cuy_id',
                    'cuy.unit_id',
                    'cuy.course_id',
                    'cuy.academic_year',
                    'cuy.semester',
                    'u.id as lecturer_id',
                    'cuyu.is_lab_only'
                )
                ->get();
        }
        
        // If no assignments found with filters, or conversion failed, get all assignments for institution
        if (!$assignments || $assignments->isEmpty()) {
            \Log::info('TimetableGenerator: No assignments with filters, using all assignments for institution');
            $assignments = $query->select(
                    'cuy.id as cuy_id',
                    'cuy.unit_id',
                    'cuy.course_id',
                    'cuy.academic_year',
                    'cuy.semester',
                    'u.id as lecturer_id',
                    'cuyu.is_lab_only'
                )
                ->get();
        }

        // Log for debugging
        \Log::info('TimetableGenerator assignments', [
            'timetable_id' => $timetable->id,
            'timetable_year' => $timetable->academic_year,
            'timetable_semester' => $timetable->semester,
            'converted_year' => $convertedAcademicYear,
            'converted_semester' => $convertedSemester,
            'assignments_count' => $assignments->count(),
            'sample_academic_years' => $assignments->pluck('academic_year')->unique()->values()->all(),
            'sample_semesters' => $assignments->pluck('semester')->unique()->values()->all()
        ]);

        if ($assignments->isEmpty()) {
            \Log::warning('TimetableGenerator: No assignments found', [
                'timetable_id' => $timetable->id,
                'institution_id' => $timetable->institution_id,
                'converted_year' => $convertedAcademicYear,
                'converted_semester' => $convertedSemester
            ]);
            return $timetable; // nothing to schedule
        }

        // Clear existing entries for this timetable to avoid duplicates (release slots first to avoid orphaned auto_busy)
        $timetable->releaseSlots();
        TimetableEntry::where('timetable_id', $timetable->id)->delete();

        $scheduledEntries = $this->generateSmartTimetable($timetable, $assignments, $timetable->institution_id);
        
        $entriesCreated = count($scheduledEntries);
        \Log::info('TimetableGenerator: Finished generation', [
            'timetable_id' => $timetable->id,
            'assignments_count' => $assignments->count(),
            'entries_created' => $entriesCreated
        ]);
        
        if ($entriesCreated === 0 && $assignments->count() > 0) {
            \Log::warning('TimetableGenerator: No entries created despite having assignments', [
                'timetable_id' => $timetable->id,
                'assignments_count' => $assignments->count()
            ]);
        }

        return $timetable;
    }

    /**
     * Legacy method - for backward compatibility
     */
    public function generate(string $name, ?int $institutionId = null, ?string $academicYear = null, ?string $semester = null)
    {
        // Find or create a timetable record by name/institution/year/semester to avoid duplicates
        $timetable = Timetable::firstOrCreate([
            'name' => $name,
            'institution_id' => $institutionId,
            'academic_year' => $academicYear,
            'semester' => $semester,
        ]);

        return $this->generateForTimetable($timetable);
    }

    /**
     * Generate a smart timetable with better scheduling logic
     */
    private function generateSmartTimetable($timetable, $assignments, $institutionId)
    {
        $days = range(1, 5); // Monday to Friday
        $slots = range(1, 4); // 4 slots per day
        
        // Load all available rooms for the institution
        $rooms = DB::table('rooms')
            ->where('institution_id', $institutionId)
            ->select('id', 'name', 'capacity', 'room_type', 'department_id')
            ->get()
            ->keyBy('id');

        if ($rooms->isEmpty()) {
            \Log::error('TimetableGenerator: No rooms found for institution', ['institution_id' => $institutionId]);
            throw new \RuntimeException('No rooms found for this institution. Please create rooms first.');
        }

        // Preload lecturer availability matrix from lecturer_availability table
        $lecturerIds = $assignments->pluck('lecturer_id')->filter()->unique();
        $availabilityRows = LecturerAvailability::whereIn('lecturer_id', $lecturerIds)->get();

        $availabilityMatrix = [];
        foreach ($availabilityRows as $row) {
            $availabilityMatrix[(int) $row->lecturer_id][(int) $row->day][(int) $row->slot] = $row->status;
        }
        
        // Strictly Exclusive Availability: 
        // We do NOT assume availability. If no record exists for a slot, it remains null/false.
        
        \Log::info('TimetableGenerator: Starting generation', [
            'assignments_count' => $assignments->count(),
            'rooms_count' => $rooms->count(),
            'lecturers_count' => $lecturerIds->count()
        ]);

        // Track the scheduling in-memory for this run
        $lecturerSchedule = []; // Track lecturer usage in this generation
        $roomSchedule = []; // Track room usage in this generation
        $groupSchedule = []; // Track student group (Course + Year) usage in this generation
        $timeSlotUsage = []; // Track which time slots are used overall

        // Preload external occupancy from other published/approved timetables across the institution
        $externalEntries = TimetableEntry::whereHas('timetable', function($q) use ($institutionId, $timetable) {
                $q->where('institution_id', $institutionId)
                  ->whereIn('status', ['published', 'approved'])
                  ->where('id', '!=', $timetable->id);
            })
            ->with('timetable')
            ->get();

        foreach ($externalEntries as $ee) {
            $day = (int)$ee->day_of_week;
            $slot = (int)$ee->slot;
            
            if ($ee->lecturer_id) {
                $lecturerSchedule[$ee->lecturer_id][$day][$slot] = true;
            }
            if ($ee->room_id) {
                $roomSchedule[$ee->room_id][$day][$slot] = true;
            }
            if ($ee->course_id && $ee->timetable) {
                $extYear = $this->convertAcademicYear($ee->timetable->academic_year);
                if ($extYear) {
                    $groupSchedule[$ee->course_id][$extYear][$day][$slot] = true;
                }
            }
            $timeSlotUsage[$day][$slot] = ($timeSlotUsage[$day][$slot] ?? 0) + 1;
        }

        // Shuffle assignments to randomize scheduling order
        $assignmentsArray = $assignments->shuffle();

        foreach ($assignmentsArray as $assignment) {
            $scheduled = false;
            $attempts = 0;
            $maxAttempts = 20; // Prevent infinite loops

            while (!$scheduled && $attempts < $maxAttempts) {
                $attempts++;
                
                // Use different strategies based on attempts to ensure placement
                if ($attempts <= 10) {
                    // First 10 attempts: Try optimal scheduling
                    $day = $this->getOptimalDay($assignment, $lecturerSchedule, $timeSlotUsage, $days);
                    $slot = $this->getOptimalSlot($assignment, $lecturerSchedule, $timeSlotUsage, $day, $slots, $availabilityMatrix);
                } else {
                    // After 10 attempts: Try any available slot
                    $availableSlots = $this->findAnyAvailableSlot($assignment, $lecturerSchedule, $roomSchedule, $rooms, $days, $slots, $availabilityMatrix);
                    if (!empty($availableSlots)) {
                        $randomSlot = $availableSlots[array_rand($availableSlots)];
                        $day = $randomSlot['day'];
                        $slot = $randomSlot['slot'];
                    } else {
                        break; // No available slots found
                    }
                }
                
                if (!$day || !$slot) {
                    continue; // Skip if no suitable slot found
                }

                // Check lecturer and group availability
                if ($this->isLecturerAvailable($assignment, $day, $slot, $lecturerSchedule, $availabilityMatrix) &&
                    $this->isGroupAvailable($assignment, $day, $slot, $groupSchedule)) {
                    // Find suitable room
                    $room = $this->findSuitableRoom($assignment, $rooms, $roomSchedule, $day, $slot);
                    
                    if ($room) {
                        // Create timetable entry
                        $entry = TimetableEntry::create([
                                'timetable_id' => $timetable->id,
                                'day_of_week' => $day,
                                'slot' => $slot,
                                'lecturer_id' => $assignment->lecturer_id,
                                'teaching_group_id' => null,
                                'unit_id' => $assignment->unit_id,
                                'course_id' => $assignment->course_id,
                                'room_id' => $room->id,
                            ]);

                        // Track the scheduling in-memory for this run
                        $lecturerSchedule[$assignment->lecturer_id][$day][$slot] = true;
                        $roomSchedule[$room->id][$day][$slot] = true;
                        $groupSchedule[$assignment->course_id][$assignment->academic_year][$day][$slot] = true;
                        $timeSlotUsage[$day][$slot] = ($timeSlotUsage[$day][$slot] ?? 0) + 1;
                        $scheduledEntries[] = $entry;
                        $scheduled = true;
                    }
                }
            }

            if (!$scheduled) {
                // Log failed scheduling attempt for debugging
                \Log::warning("Failed to schedule assignment", [
                    'unit_id' => $assignment->unit_id,
                    'lecturer_id' => $assignment->lecturer_id,
                    'is_lab_only' => $assignment->is_lab_only
                ]);
            }
        }

        \Log::info('TimetableGenerator: Generation completed', [
            'total_assignments' => $assignments->count(),
            'scheduled_entries' => count($scheduledEntries),
            'failed_schedules' => $assignments->count() - count($scheduledEntries)
        ]);

        return $scheduledEntries;
    }

    /**
     * Get optimal day for scheduling based on overall time slot usage and lecturer workload
     */
    private function getOptimalDay($assignment, $lecturerSchedule, $timeSlotUsage, $days)
    {
        $lecturerId = $assignment->lecturer_id;
        $dayScores = [];
        
        // Calculate score for each day (lower is better)
        foreach ($days as $day) {
            $lecturerLoad = count($lecturerSchedule[$lecturerId][$day] ?? []);
            $totalTimeSlotUsage = array_sum($timeSlotUsage[$day] ?? []);
            
            // Combine lecturer load and overall day usage
            $dayScores[$day] = $lecturerLoad * 2 + $totalTimeSlotUsage; // Weight lecturer load more heavily
        }
        
        // Sort days by score (ascending)
        asort($dayScores);
        
        // Return the day with best score
        return array_key_first($dayScores);
    }

    /**
     * Get optimal slot for scheduling
     */
    private function getOptimalSlot($assignment, $lecturerSchedule, $timeSlotUsage, $day, $slots, array $availabilityMatrix)
    {
        $lecturerId = $assignment->lecturer_id;

        // Restrict to slots where lecturer is available (Default is busy unless explicitly available)
        $filteredSlots = [];
        foreach ($slots as $slot) {
            $status = $availabilityMatrix[$lecturerId][$day][$slot] ?? 'busy';
            
            if ($status === 'available' || $status === 'auto_busy') {
                $filteredSlots[] = $slot;
            }
        }

        if (empty($filteredSlots)) {
            return null;
        }

        // Find best available slot considering usage and lecturer allocation in this generation
        $slotScores = [];
        foreach ($filteredSlots as $slot) {
            if (!isset($lecturerSchedule[$lecturerId][$day][$slot])) {
                $slotUsage = $timeSlotUsage[$day][$slot] ?? 0;
                $slotScores[$slot] = $slotUsage; // Lower usage is better
            }
        }
        
        if (empty($slotScores)) {
            return null;
        }
        
        // Sort by usage (ascending) and return the least used slot
        asort($slotScores);
        return array_key_first($slotScores);
    }

    /**
     * Check if lecturer is available at the given time
     */
    private function isLecturerAvailable($assignment, $day, $slot, $lecturerSchedule, array $availabilityMatrix)
    {
        $lecturerId = $assignment->lecturer_id;
        
        // Check if lecturer is already scheduled at this time in current run
        if (isset($lecturerSchedule[$lecturerId][$day][$slot])) {
            return false;
        }

        // Check availability records - assume busy if NO record, OR if status is 'available' or 'auto_busy'
        $status = $availabilityMatrix[$lecturerId][$day][$slot] ?? 'busy';

        return $status === 'available' || $status === 'auto_busy';
    }

    /**
     * Find suitable room for the assignment
     */
    private function findSuitableRoom($assignment, $rooms, $roomSchedule, $day, $slot)
    {
        $suitableRooms = $rooms->filter(function ($room) use ($assignment, $roomSchedule, $day, $slot) {
            // Check if room is already booked
            if (isset($roomSchedule[$room->id][$day][$slot])) {
                return false;
            }

            // Check room type requirements (Strict Separation)
            if ($assignment->is_lab_only) {
                if ($room->room_type !== 'lab') return false;
            } else {
                // If not lab only, prefer non-lab rooms to preserve labs for actual lab units
                if ($room->room_type === 'lab') return false;
            }

            return true;
        });

        if ($suitableRooms->isEmpty()) {
            return null;
        }

        // Prioritize rooms by type and capacity, then add some randomization for better distribution
        $prioritizedRooms = $suitableRooms->sort(function ($a, $b) use ($assignment) {
            // Prefer labs for lab-only assignments
            if ($assignment->is_lab_only) {
                if ($a->room_type === 'lab' && $b->room_type !== 'lab') return -1;
                if ($a->room_type !== 'lab' && $b->room_type === 'lab') return 1;
            }

            // Sort by capacity (smaller rooms first to optimize usage)
            return $a->capacity <=> $b->capacity;
        });

        // Add some randomization to distribute rooms more evenly
        // Take the first 3 suitable rooms and randomly pick one
        $topRooms = $prioritizedRooms->take(3);
        if ($topRooms->count() > 1) {
            return $topRooms->random();
        }

        return $prioritizedRooms->first();
    }

    /**
     * Find any available slot for the assignment (fallback method)
     */
    private function findAnyAvailableSlot($assignment, $lecturerSchedule, $roomSchedule, $rooms, $days, $slots, array $availabilityMatrix)
    {
        $availableSlots = [];
        
        foreach ($days as $day) {
            foreach ($slots as $slot) {
                // Check if lecturer is available
                if ($this->isLecturerAvailable($assignment, $day, $slot, $lecturerSchedule, $availabilityMatrix)) {
                    // Check if any suitable room is available
                    $room = $this->findSuitableRoom($assignment, $rooms, $roomSchedule, $day, $slot);
                    if ($room) {
                        $availableSlots[] = ['day' => $day, 'slot' => $slot];
                    }
                }
            }
        }
        
        return $availableSlots;
    }

    /**
     * Check if the student group (Course + Year) is available at the given time
     */
    private function isGroupAvailable($assignment, $day, $slot, $groupSchedule)
    {
        $courseId = $assignment->course_id;
        $academicYear = $assignment->academic_year;

        // Check if this course group is already scheduled for another unit at this time
        if (isset($groupSchedule[$courseId][$academicYear][$day][$slot])) {
            return false;
        }

        return true;
    }

    /**
     * Convert timetable academic year format (e.g., "2025-2026") to course_unit_year format (e.g., "Y4")
     */
    public function convertAcademicYear(?string $academicYear): ?string
    {
        if (!$academicYear) {
            return null;
        }

        // If already in Y1-Y5 format, return as-is
        if (preg_match('/^Y[1-5]$/', $academicYear)) {
            return $academicYear;
        }

        // Convert "2025-2026" format to year level based on current patterns
        // This is a simple mapping - you might want to make this more sophisticated
        $yearMappings = [
            '2024-2025' => 'Y3',
            '2025-2026' => 'Y4', // Based on your current data
            '2026-2027' => 'Y5',
        ];

        return $yearMappings[$academicYear] ?? null;
    }

    /**
     * Convert timetable semester format (e.g., "Semester 1") to course_unit_year format (e.g., "S1")
     */
    public function convertSemester(?string $semester): ?string
    {
        if (!$semester) {
            return null;
        }

        // If already in S1/S2 format, return as-is
        if (preg_match('/^S[1-2]$/', $semester)) {
            return $semester;
        }

        // Convert "Semester 1" format to S1, etc.
        $semesterMappings = [
            'Semester 1' => 'S1',
            'Semester 2' => 'S2',
            'S1' => 'S1',
            'S2' => 'S2',
        ];

        return $semesterMappings[$semester] ?? null;
    }
}
