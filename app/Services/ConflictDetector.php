<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConflictDetector
{
    /**
     * Detect all types of conflicts in a timetable
     */
    public function detectConflicts(Timetable $timetable): array
    {
        return [
            'lecturer_conflicts' => $this->detectLecturerConflicts($timetable),
            'room_conflicts' => $this->detectRoomConflicts($timetable),
            'availability_violations' => $this->detectAvailabilityViolations($timetable),
            'room_type_mismatches' => $this->detectRoomTypeMismatches($timetable),
            'statistics' => $this->generateStatistics($timetable)
        ];
    }
    
    /**
     * Detect lecturer double-booking conflicts
     */
    private function detectLecturerConflicts(Timetable $timetable): Collection
    {
        $conflicts = collect();
        
        // Group entries by lecturer, day, and slot in current timetable
        $entries = $timetable->entries()->with(['unit', 'room', 'lecturer'])->get();
        
        // Check for internal conflicts
        $internal = $entries
            ->whereNotNull('lecturer_id')
            ->groupBy(fn($e) => $e->lecturer_id . '|' . $e->day_of_week . '|' . $e->slot)
            ->filter(fn($e) => $e->count() > 1);
            
        foreach ($internal as $key => $conflictingEntries) {
            $parts = explode('|', $key);
            $conflicts->push([
                'type' => 'lecturer_conflict',
                'subtype' => 'internal',
                'lecturer_id' => $parts[0],
                'lecturer_name' => $conflictingEntries->first()->lecturer->name ?? 'Unknown',
                'day' => $parts[1],
                'slot' => $parts[2],
                'entries' => $conflictingEntries,
                'severity' => 'high'
            ]);
        }

        // Check for external conflicts with other published timetables in the same institution
        $otherEntries = TimetableEntry::whereHas('timetable', function($q) use ($timetable) {
                $q->where('institution_id', $timetable->institution_id)
                  ->where('status', 'published')
                  ->where('id', '!=', $timetable->id);
            })
            ->whereIn('lecturer_id', $entries->pluck('lecturer_id')->filter())
            ->with(['timetable', 'unit', 'room', 'lecturer'])
            ->get();

        foreach ($entries as $entry) {
            if (!$entry->lecturer_id) continue;
            
            $external = $otherEntries->filter(function($oe) use ($entry) {
                return $oe->lecturer_id == $entry->lecturer_id && 
                       $oe->day_of_week == $entry->day_of_week && 
                       $oe->slot == $entry->slot;
            });

            if ($external->isNotEmpty()) {
                $conflicts->push([
                    'type' => 'lecturer_conflict',
                    'subtype' => 'external',
                    'lecturer_id' => $entry->lecturer_id,
                    'lecturer_name' => $entry->lecturer->name ?? 'Unknown',
                    'day' => $entry->day_of_week,
                    'slot' => $entry->slot,
                    'entries' => collect([$entry])->concat($external),
                    'severity' => 'high'
                ]);
            }
        }
        
        return $conflicts->unique(fn($c) => $c['lecturer_id'] . '|' . $c['day'] . '|' . $c['slot']);
    }
    
    /**
     * Detect room double-booking conflicts
     */
    private function detectRoomConflicts(Timetable $timetable): Collection
    {
        $conflicts = collect();
        
        // Group entries by room, day, and slot
        $entries = $timetable->entries()->with(['unit', 'room', 'lecturer'])->get();
        
        // Check for internal conflicts
        $internal = $entries
            ->whereNotNull('room_id')
            ->groupBy(fn($e) => $e->room_id . '|' . $e->day_of_week . '|' . $e->slot)
            ->filter(fn($e) => $e->count() > 1);
            
        foreach ($internal as $key => $conflictingEntries) {
            $parts = explode('|', $key);
            $conflicts->push([
                'type' => 'room_conflict',
                'subtype' => 'internal',
                'room_id' => $parts[0],
                'room_name' => $conflictingEntries->first()->room->name ?? 'Unknown',
                'day' => $parts[1],
                'slot' => $parts[2],
                'entries' => $conflictingEntries,
                'severity' => 'high'
            ]);
        }

        // Check for external conflicts
        $otherEntries = TimetableEntry::whereHas('timetable', function($q) use ($timetable) {
                $q->where('institution_id', $timetable->institution_id)
                  ->where('status', 'published')
                  ->where('id', '!=', $timetable->id);
            })
            ->whereIn('room_id', $entries->pluck('room_id')->filter())
            ->with(['timetable', 'unit', 'room', 'lecturer'])
            ->get();

        foreach ($entries as $entry) {
            if (!$entry->room_id) continue;
            
            $external = $otherEntries->filter(function($oe) use ($entry) {
                return $oe->room_id == $entry->room_id && 
                       $oe->day_of_week == $entry->day_of_week && 
                       $oe->slot == $entry->slot;
            });

            if ($external->isNotEmpty()) {
                $conflicts->push([
                    'type' => 'room_conflict',
                    'subtype' => 'external',
                    'room_id' => $entry->room_id,
                    'room_name' => $entry->room->name ?? 'Unknown',
                    'day' => $entry->day_of_week,
                    'slot' => $entry->slot,
                    'entries' => collect([$entry])->concat($external),
                    'severity' => 'high'
                ]);
            }
        }
        
        return $conflicts->unique(fn($c) => $c['room_id'] . '|' . $c['day'] . '|' . $c['slot']);
    }
    
    /**
     * Detect lecturer availability violations
     */
    private function detectAvailabilityViolations(Timetable $timetable): Collection
    {
        $violations = collect();

        // Load entries and associated lecturer user
        $entries = $timetable->entries()->with(['lecturer'])->get();

        if ($entries->isEmpty()) {
            return $violations;
        }

        // Build a lookup of lecturer availability from lecturer_availability table
        $lecturerIds = $entries->pluck('lecturer_id')->filter()->unique();
        if ($lecturerIds->isEmpty()) {
            return $violations;
        }

        $availabilityRows = \App\Models\LecturerAvailability::whereIn('lecturer_id', $lecturerIds)->get();
        $availabilityMatrix = [];
        foreach ($availabilityRows as $row) {
            $availabilityMatrix[(int) $row->lecturer_id][(int) $row->day][(int) $row->slot] = $row->status;
        }

        foreach ($entries as $entry) {
            if (!$entry->lecturer || !$entry->lecturer_id) {
                continue;
            }

            $lecturerId = (int) $entry->lecturer_id;
            $day = (int) $entry->day_of_week;
            $slot = (int) $entry->slot;

            $status = $availabilityMatrix[$lecturerId][$day][$slot] ?? 'available';

            // IF no row or status != available, it is a violation
            // Ignore 'auto_busy' as it is handled by lecturer double-booking logic
            if ($status !== 'available' && $status !== 'auto_busy') {
                \Log::debug("Violation found for Lec $lecturerId at D$day S$slot. Status: $status");
                $violations->push([
                    'type' => 'availability_violation',
                    'lecturer_id' => $lecturerId,
                    'lecturer_name' => $entry->lecturer->name ?? 'Unknown',
                    'day' => $day,
                    'slot' => $slot,
                    'status' => $status ?? 'unavailable',
                    'entry' => $entry,
                    'severity' => 'medium',
                ]);
            }
        }

        return $violations;
    }
    
    /**
     * Detect room type mismatches (e.g. lab course in regular classroom)
     */
    private function detectRoomTypeMismatches(Timetable $timetable): Collection
    {
        $mismatches = collect();
        
        // Get entries that are marked as lab-only but assigned to non-lab rooms
        $entries = DB::table('timetable_entries as te')
            ->join('course_unit_year_user as cuyu', function($join) {
                $join->on('cuyu.user_id', '=', 'te.lecturer_id')
                     ->whereColumn('cuyu.course_unit_year_id', '=', DB::raw('(SELECT cuy.id FROM course_unit_year cuy WHERE cuy.unit_id = te.unit_id LIMIT 1)'));
            })
            ->join('rooms as r', 'r.id', '=', 'te.room_id')
            ->join('units as u', 'u.id', '=', 'te.unit_id')
            ->where('te.timetable_id', $timetable->id)
            ->where('cuyu.is_lab_only', true)
            ->where('r.room_type', '!=', 'lab')
            ->select('te.*', 'r.name as room_name', 'r.room_type', 'u.code as unit_code')
            ->get();
            
        foreach ($entries as $entry) {
            $mismatches->push([
                'type' => 'room_type_mismatch',
                'unit_code' => $entry->unit_code,
                'room_name' => $entry->room_name,
                'room_type' => $entry->room_type,
                'expected_type' => 'lab',
                'day' => $entry->day_of_week,
                'slot' => $entry->slot,
                'severity' => 'medium'
            ]);
        }
        
        return $mismatches;
    }
    
    /**
     * Generate utilization statistics
     */
    private function generateStatistics(Timetable $timetable): array
    {
        $roomCount = \App\Models\Room::where('institution_id', $timetable->institution_id)->count() ?: 1;
        $totalSlots = $roomCount * 5 * 4; // Total capacity across all rooms over 5 days
        $usedSlots = $timetable->entries->count();
        
        \Log::debug("Stats calc: RoomCount=$roomCount, TotalSlots=$totalSlots, UsedSlots=$usedSlots. Percentage=" . round(($usedSlots / $totalSlots) * 100, 1));
        
        $lecturerWorkload = $timetable->entries
            ->groupBy('lecturer_id')
            ->map(function ($entries, $lecturerId) {
                return [
                    'lecturer_id' => $lecturerId,
                    'lecturer_name' => $entries->first()->lecturer->name ?? 'Unknown',
                    'total_hours' => $entries->count() * 3, // 3 hours per slot
                    'entries_count' => $entries->count(),
                    'days_working' => $entries->pluck('day_of_week')->unique()->count()
                ];
            })
            ->values();
            
        $roomUtilization = $timetable->entries
            ->groupBy('room_id')
            ->map(function ($entries, $roomId) {
                $room = $entries->first()->room;
                return [
                    'room_id' => $roomId,
                    'room_name' => $room->name ?? 'Unknown',
                    'capacity' => $room->capacity ?? 0,
                    'utilization_slots' => $entries->count(),
                    'utilization_percentage' => round(($entries->count() / 20) * 100, 1) // Each room has 20 slots max
                ];
            })
            ->values();
        
        return [
            'total_slots' => $totalSlots,
            'used_slots' => $usedSlots,
            'utilization_percentage' => round(($usedSlots / $totalSlots) * 100, 1),
            'lecturer_workload' => $lecturerWorkload,
            'room_utilization' => $roomUtilization,
            'peak_hours' => $this->findPeakHours($timetable),
        ];
    }
    
    /**
     * Find the busiest time slots
     */
    private function findPeakHours(Timetable $timetable): Collection
    {
        $peakHours = collect();
        
        $grouped = $timetable->entries
            ->groupBy(function ($entry) {
                return $entry->day_of_week . '|' . $entry->slot;
            });
            
        foreach ($grouped as $key => $entries) {
            $parts = explode('|', $key);
            $peakHours->push([
                'day' => (int)$parts[0],
                'slot' => (int)$parts[1],
                'entry_count' => $entries->count(),
                'day_name' => ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'][(int)$parts[0]] ?? 'Unknown'
            ]);
        }
        
        return $peakHours->sortByDesc('entry_count')->take(5)->values();
    }
    
    /**
     * Get optimization recommendations
     */
    public function getOptimizationRecommendations(Timetable $timetable): Collection
    {
        $recommendations = collect();
        $conflicts = $this->detectConflicts($timetable);
        
        // High priority: Fix conflicts
        if ($conflicts['lecturer_conflicts']->isNotEmpty()) {
            $recommendations->push([
                'priority' => 'high',
                'type' => 'conflict_resolution',
                'title' => 'Resolve Lecturer Conflicts',
                'description' => 'There are ' . $conflicts['lecturer_conflicts']->count() . ' lecturer double-booking conflicts that need immediate attention.',
                'action' => 'Review and reschedule conflicting entries'
            ]);
        }
        
        if ($conflicts['room_conflicts']->isNotEmpty()) {
            $recommendations->push([
                'priority' => 'high',
                'type' => 'conflict_resolution',
                'title' => 'Resolve Room Conflicts',
                'description' => 'There are ' . $conflicts['room_conflicts']->count() . ' room double-booking conflicts.',
                'action' => 'Assign alternative rooms or reschedule'
            ]);
        }
        
        // Medium priority: Optimize utilization
        $stats = $conflicts['statistics'];
        if ($stats['utilization_percentage'] < 60) {
            $recommendations->push([
                'priority' => 'medium',
                'type' => 'utilization',
                'title' => 'Low Timetable Utilization',
                'description' => 'Only ' . $stats['utilization_percentage'] . '% of available time slots are used.',
                'action' => 'Consider adding more courses or units'
            ]);
        }
        
        // Check for unbalanced lecturer workload
        $maxWorkload = $stats['lecturer_workload']->max('entries_count') ?? 0;
        $minWorkload = $stats['lecturer_workload']->min('entries_count') ?? 0;
        
        if ($maxWorkload - $minWorkload > 3) {
            $recommendations->push([
                'priority' => 'medium',
                'type' => 'workload_balance',
                'title' => 'Unbalanced Lecturer Workload',
                'description' => 'Some lecturers have significantly more classes than others.',
                'action' => 'Redistribute teaching assignments for better balance'
            ]);
        }
        
        return $recommendations->sortBy(function ($rec) {
            return $rec['priority'] === 'high' ? 1 : ($rec['priority'] === 'medium' ? 2 : 3);
        });
    }

    /**
     * Find free slots for a lecturer and room combination
     */
    public function findFreeSlots(TimetableEntry $entry, int $limit = 5): Collection
    {
        $institutionId = $entry->timetable->institution_id;
        $lecturerId = $entry->lecturer_id;
        $roomId = $entry->room_id;
        
        if (!$lecturerId || !$roomId) return collect();

        // 1. Get lecturer availability
        $availableRows = DB::table('lecturer_availability')
            ->where('lecturer_id', $lecturerId)
            ->where('status', 'available')
            ->get()
            ->groupBy('day');

        // 2. Get all occupied slots for this room across all relevant timetables
        $occupiedRoomSlots = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)
                  ->whereIn('status', ['published', 'approved']);
            })
            ->where('room_id', $roomId)
            ->get()
            ->groupBy('day_of_week');

        // 3. Get all occupied slots for this lecturer across all relevant timetables
        $occupiedLecturerSlots = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)
                  ->whereIn('status', ['published', 'approved']);
            })
            ->where('lecturer_id', $lecturerId)
            ->get()
            ->groupBy('day_of_week');

        $freeSlots = collect();
        $days = [1, 2, 3, 4, 5];
        $slots = [1, 2, 3, 4];

        foreach ($days as $day) {
            foreach ($slots as $slot) {
                // Skip the current position
                if ($day == $entry->day_of_week && $slot == $entry->slot) continue;

                // Check lecturer availability row
                $isAvailable = $availableRows->get($day)?->contains('slot', $slot) ?? false;
                if (!$isAvailable) continue;

                // Check room occupancy
                $isRoomFree = !($occupiedRoomSlots->get($day)?->contains('slot', $slot) ?? false);
                if (!$isRoomFree) continue;

                // Check lecturer occupancy
                $isLecturerFree = !($occupiedLecturerSlots->get($day)?->contains('slot', $slot) ?? false);
                if (!$isLecturerFree) continue;

                $freeSlots->push([
                    'day' => $day,
                    'slot' => $slot,
                    'day_name' => ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'][$day],
                    'time' => [1=>'7-10 AM', 2=>'10-1 PM', 3=>'1-4 PM', 4=>'4-7 PM'][$slot]
                ]);

                if ($freeSlots->count() >= $limit) break 2;
            }
        }

        return $freeSlots;
    }
}