<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\TimetableEntry;
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
        $assignments = DB::table('course_unit_year as cuy')
            ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
            ->join('users as u', 'u.id', '=', 'cuyu.user_id')
            ->join('lecturers as L', 'L.id', '=', 'u.lecturer_id')
            ->join('courses as c', 'c.id', '=', 'cuy.course_id')
            ->join('departments as d', 'd.id', '=', 'c.department_id')
            ->when($timetable->institution_id, fn($q) => $q->where('u.institution_id', $timetable->institution_id))
            ->when($convertedAcademicYear, fn($q) => $q->where('cuy.academic_year', $convertedAcademicYear))
            ->when($convertedSemester, fn($q) => $q->where('cuy.semester', $convertedSemester))
            ->select('cuy.id as cuy_id','cuy.unit_id','cuy.course_id','cuy.academic_year','cuy.semester','u.lecturer_id','cuyu.is_lab_only','L.availability')
            ->get();

        if ($assignments->isEmpty()) {
            return $timetable; // nothing to schedule
        }

        // Clear existing entries for this timetable to avoid duplicates
        TimetableEntry::where('timetable_id', $timetable->id)->delete();

        $this->generateSmartTimetable($timetable, $assignments, $timetable->institution_id);

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

        // Don't group by unit - schedule each assignment individually
        $scheduledEntries = [];
        $lecturerSchedule = []; // Track lecturer availability
        $roomSchedule = []; // Track room availability
        $timeSlotUsage = []; // Track which time slots are used

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
                    $slot = $this->getOptimalSlot($assignment, $lecturerSchedule, $timeSlotUsage, $day, $slots);
                } else {
                    // After 10 attempts: Try any available slot
                    $availableSlots = $this->findAnyAvailableSlot($assignment, $lecturerSchedule, $roomSchedule, $rooms, $days, $slots);
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

                // Check lecturer availability
                if ($this->isLecturerAvailable($assignment, $day, $slot, $lecturerSchedule)) {
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

                        // Track the scheduling
                        $lecturerSchedule[$assignment->lecturer_id][$day][$slot] = true;
                        $roomSchedule[$room->id][$day][$slot] = true;
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
    private function getOptimalSlot($assignment, $lecturerSchedule, $timeSlotUsage, $day, $slots)
    {
        $lecturerId = $assignment->lecturer_id;
        
        // Check lecturer availability if defined
        if (!empty($assignment->availability)) {
            try {
                $avail = json_decode($assignment->availability, true) ?: [];
                $availableSlots = $avail[(string)$day] ?? $avail[$day] ?? $slots;
                if (is_array($availableSlots)) {
                    $slots = array_intersect($slots, $availableSlots);
                }
            } catch (\Throwable $e) {
                // Continue with all slots if availability parsing fails
            }
        }

        // Find best available slot considering usage and lecturer availability
        $slotScores = [];
        foreach ($slots as $slot) {
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
    private function isLecturerAvailable($assignment, $day, $slot, $lecturerSchedule)
    {
        $lecturerId = $assignment->lecturer_id;
        
        // Check if lecturer is already scheduled at this time
        if (isset($lecturerSchedule[$lecturerId][$day][$slot])) {
            return false;
        }

        // Check availability constraints from lecturer profile
        if (!empty($assignment->availability)) {
            try {
                $avail = json_decode($assignment->availability, true) ?: [];
                $availableSlots = $avail[(string)$day] ?? $avail[$day] ?? [];
                if (is_array($availableSlots) && !in_array($slot, $availableSlots)) {
                    return false;
                }
            } catch (\Throwable $e) {
                // Continue if availability parsing fails
            }
        }

        return true;
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

            // Check room type requirements
            if ($assignment->is_lab_only && $room->room_type !== 'lab') {
                return false;
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
    private function findAnyAvailableSlot($assignment, $lecturerSchedule, $roomSchedule, $rooms, $days, $slots)
    {
        $availableSlots = [];
        
        foreach ($days as $day) {
            foreach ($slots as $slot) {
                // Check if lecturer is available
                if ($this->isLecturerAvailable($assignment, $day, $slot, $lecturerSchedule)) {
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
     * Convert timetable academic year format (e.g., "2025-2026") to course_unit_year format (e.g., "Y4")
     */
    private function convertAcademicYear(?string $academicYear): ?string
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
    private function convertSemester(?string $semester): ?string
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
