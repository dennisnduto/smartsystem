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
        
        // Group entries by lecturer, day, and slot
        $entries = $timetable->entries()->with(['unit', 'room', 'lecturer'])->get();
        
        $grouped = $entries
            ->whereNotNull('lecturer_id')
            ->groupBy(function ($entry) {
                return $entry->lecturer_id . '|' . $entry->day_of_week . '|' . $entry->slot;
            })
            ->filter(function ($entries) {
                return $entries->count() > 1;
            });
            
        foreach ($grouped as $key => $conflictingEntries) {
            $parts = explode('|', $key);
            $lecturerId = $parts[0];
            $day = $parts[1];
            $slot = $parts[2];
            
            $conflicts->push([
                'type' => 'lecturer_conflict',
                'lecturer_id' => $lecturerId,
                'lecturer_name' => $conflictingEntries->first()->lecturer->name ?? 'Unknown',
                'day' => $day,
                'slot' => $slot,
                'entries' => $conflictingEntries,
                'severity' => 'high'
            ]);
        }
        
        return $conflicts;
    }
    
    /**
     * Detect room double-booking conflicts
     */
    private function detectRoomConflicts(Timetable $timetable): Collection
    {
        $conflicts = collect();
        
        // Group entries by room, day, and slot
        $entries = $timetable->entries()->with(['unit', 'room', 'lecturer'])->get();
        
        $grouped = $entries
            ->whereNotNull('room_id')
            ->groupBy(function ($entry) {
                return $entry->room_id . '|' . $entry->day_of_week . '|' . $entry->slot;
            })
            ->filter(function ($entries) {
                return $entries->count() > 1;
            });
            
        foreach ($grouped as $key => $conflictingEntries) {
            $parts = explode('|', $key);
            $roomId = $parts[0];
            $day = $parts[1];
            $slot = $parts[2];
            
            $conflicts->push([
                'type' => 'room_conflict',
                'room_id' => $roomId,
                'room_name' => $conflictingEntries->first()->room->name ?? 'Unknown',
                'day' => $day,
                'slot' => $slot,
                'entries' => $conflictingEntries,
                'severity' => 'high'
            ]);
        }
        
        return $conflicts;
    }
    
    /**
     * Detect lecturer availability violations
     */
    private function detectAvailabilityViolations(Timetable $timetable): Collection
    {
        $violations = collect();
        
        $entries = $timetable->entries()->with(['lecturer'])->get();
        
        foreach ($entries as $entry) {
            if (!$entry->lecturer || !$entry->lecturer->availability) {
                continue;
            }
            
            try {
                $availability = json_decode($entry->lecturer->availability, true);
                if (!is_array($availability)) continue;
                
                $dayAvailability = $availability[(string)$entry->day_of_week] ?? $availability[$entry->day_of_week] ?? [];
                
                if (is_array($dayAvailability) && !in_array($entry->slot, $dayAvailability)) {
                    $violations->push([
                        'type' => 'availability_violation',
                        'lecturer_id' => $entry->lecturer_id,
                        'lecturer_name' => $entry->lecturer->name,
                        'day' => $entry->day_of_week,
                        'slot' => $entry->slot,
                        'available_slots' => $dayAvailability,
                        'entry' => $entry,
                        'severity' => 'medium'
                    ]);
                }
            } catch (\Exception $e) {
                // Skip if availability format is invalid
                continue;
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
        $totalSlots = 5 * 4; // 5 days × 4 slots
        $usedSlots = $timetable->entries->count();
        
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
                    'utilization_percentage' => round(($entries->count() / 20) * 100, 1) // 20 total slots
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
}