<?php

namespace App\Services;

use App\Models\{Institution, Department, User, Timetable, Room, Unit, Course, TimetableEntry};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class InstitutionAdminAIService
{
    public function generateResponse(string $question, $user): string
    {
        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            return $this->fallbackResponse($question, $user);
        }

        // Get institution-wide data for context
        $institutionData = $this->getInstitutionData($user);
        
        $systemPrompt = "You are a helpful university institution administrator AI assistant. 
        You have access to comprehensive institution-wide data including departments, courses, rooms, 
        lecturers, students, and timetables. Provide helpful, accurate answers about institution management, 
        resource allocation, scheduling, conflict resolution, and administrative tasks. Be concise and professional. 
        If you don't know something, say so honestly.";

        $userPrompt = [
            'question' => $question,
            'admin_name' => $user->name,
            'institution_name' => $user->institution->name ?? 'Unknown Institution',
            'current_time' => now()->format('Y-m-d H:i:s'),
            'institution_data' => $institutionData
        ];

        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => json_encode($userPrompt)],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 400,
                ]);

            if ($response->ok()) {
                $content = $response->json('choices.0.message.content');
                return $content ?? $this->fallbackResponse($question, $user);
            }
        } catch (\Exception $e) {
            \Log::error('Institution Admin AI Chatbot error: ' . $e->getMessage());
        }

        return $this->fallbackResponse($question, $user);
    }

    private function getInstitutionData($user): array
    {
        $institution = $user->institution;
        if (!$institution) {
            return [];
        }

        $now = now();
        $currentDay = max(1, min(5, (int)$now->dayOfWeekIso));
        $currentSlot = $this->timeToSlot($now->format('H:i'));

        return [
            'total_departments' => $institution->departments()->count(),
            'total_schools' => $institution->schools()->count(),
            'total_courses' => $institution->departments()->withCount('courses')->get()->sum('courses_count'),
            'total_units' => Unit::where('institution_id', $institution->id)->count(),
            'total_lecturers' => $institution->users()->where('role', 'lecturer')->count(),
            'total_students' => $institution->users()->where('role', 'student')->count(),
            'total_rooms' => Room::where('institution_id', $institution->id)->count(),
            'active_timetables' => Timetable::where('institution_id', $institution->id)
                ->where('status', 'published')
                ->count(),
            'draft_timetables' => Timetable::where('institution_id', $institution->id)
                ->where('status', 'draft')
                ->count(),
            'available_rooms_now' => $this->getAvailableRooms($institution->id, $currentDay, $currentSlot),
            'current_conflicts' => $this->getCurrentConflicts($institution->id),
            'department_stats' => $this->getDepartmentStats($institution->id),
            'room_utilization' => $this->getRoomUtilization($institution->id),
            'lecturer_workload' => $this->getLecturerWorkloadAnalysis($institution->id),
            'timetable_optimization' => $this->getTimetableOptimizationSuggestions($institution->id),
            'peak_hours_analysis' => $this->getPeakHoursAnalysis($institution->id),
            'resource_efficiency' => $this->getResourceEfficiencyMetrics($institution->id),
        ];
    }

    private function getAvailableRooms($institutionId, $day, $slot): array
    {
        $busyRoomIds = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })
            ->where('day_of_week', $day)
            ->where('slot', $slot)
            ->pluck('room_id')
            ->unique();

        return Room::where('institution_id', $institutionId)
            ->whereNotIn('id', $busyRoomIds)
            ->with('department')
            ->limit(10)
            ->get()
            ->map(fn($room) => [
                'name' => $room->name,
                'department' => $room->department->name ?? 'Unassigned',
                'capacity' => $room->capacity,
                'type' => $room->room_type ?? 'Standard'
            ])
            ->toArray();
    }

    private function getCurrentConflicts($institutionId): array
    {
        $conflicts = [];
        
        // Check for lecturer conflicts
        $lecturerConflicts = TimetableEntry::select('lecturer_id', 'day_of_week', 'slot', DB::raw('count(*) as count'))
            ->whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })
            ->groupBy('lecturer_id', 'day_of_week', 'slot')
            ->having('count', '>', 1)
            ->with('lecturer')
            ->get();

        foreach ($lecturerConflicts as $conflict) {
            $conflicts[] = [
                'type' => 'lecturer',
                'description' => "Lecturer {$conflict->lecturer->name} scheduled for multiple classes at the same time",
                'day' => $conflict->day_of_week,
                'slot' => $conflict->slot
            ];
        }

        // Check for room conflicts
        $roomConflicts = TimetableEntry::select('room_id', 'day_of_week', 'slot', DB::raw('count(*) as count'))
            ->whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })
            ->groupBy('room_id', 'day_of_week', 'slot')
            ->having('count', '>', 1)
            ->with('room')
            ->get();

        foreach ($roomConflicts as $conflict) {
            $conflicts[] = [
                'type' => 'room',
                'description' => "Room {$conflict->room->name} double-booked at the same time",
                'day' => $conflict->day_of_week,
                'slot' => $conflict->slot
            ];
        }

        return $conflicts;
    }

    private function getDepartmentStats($institutionId): array
    {
        return Department::where('institution_id', $institutionId)
            ->withCount(['courses', 'rooms', 'users' => function($q) {
                $q->where('role', 'lecturer');
            }])
            ->get()
            ->map(fn($dept) => [
                'name' => $dept->name,
                'courses' => $dept->courses_count,
                'rooms' => $dept->rooms_count,
                'lecturers' => $dept->users_count
            ])
            ->toArray();
    }

    private function getRoomUtilization($institutionId): array
    {
        $totalRooms = Room::where('institution_id', $institutionId)->count();
        if ($totalRooms === 0) return ['utilization_rate' => 0, 'total_slots' => 0, 'used_slots' => 0];

        $totalSlots = 20 * 5; // 20 slots per week (4 slots × 5 days) per room
        $usedSlots = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })->distinct('room_id', 'day_of_week', 'slot')->count();

        return [
            'utilization_rate' => $totalSlots > 0 ? round(($usedSlots / ($totalSlots * $totalRooms)) * 100, 2) : 0,
            'total_slots' => $totalSlots * $totalRooms,
            'used_slots' => $usedSlots
        ];
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

    private function getLecturerWorkloadAnalysis($institutionId): array
    {
        $workloadData = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })
            ->with('lecturer')
            ->get()
            ->groupBy('lecturer_id')
            ->map(function($entries) {
                $lecturer = $entries->first()->lecturer;
                return [
                    'name' => $lecturer->name ?? 'Unknown',
                    'total_classes' => $entries->count(),
                    'departments_covered' => $entries->pluck('unit.course.department_id')->unique()->count(),
                    'rooms_used' => $entries->pluck('room_id')->unique()->count(),
                    'workload_score' => $this->calculateWorkloadScore($entries),
                    'efficiency_rating' => $this->calculateEfficiencyRating($entries)
                ];
            })
            ->sortByDesc('workload_score')
            ->values();

        return [
            'total_lecturers' => $workloadData->count(),
            'overloaded_lecturers' => $workloadData->where('workload_score', '>', 20)->count(),
            'underutilized_lecturers' => $workloadData->where('workload_score', '<', 8)->count(),
            'top_performers' => $workloadData->take(3)->toArray(),
            'workload_distribution' => $this->getWorkloadDistribution($workloadData)
        ];
    }

    private function calculateWorkloadScore($entries): int
    {
        // Base score: 1 point per class
        $score = $entries->count();
        
        // Bonus for variety (different departments, rooms)
        $score += $entries->pluck('unit.course.department_id')->unique()->count() * 2;
        $score += $entries->pluck('room_id')->unique()->count();
        
        return $score;
    }

    private function calculateEfficiencyRating($entries): string
    {
        $totalClasses = $entries->count();
        $uniqueRooms = $entries->pluck('room_id')->unique()->count();
        $uniqueDepartments = $entries->pluck('unit.course.department_id')->unique()->count();
        
        $efficiency = ($uniqueRooms + $uniqueDepartments) / max(1, $totalClasses);
        
        if ($efficiency > 0.8) return 'Excellent';
        if ($efficiency > 0.6) return 'Good';
        if ($efficiency > 0.4) return 'Average';
        return 'Needs Improvement';
    }

    private function getWorkloadDistribution($workloadData): array
    {
        $distribution = [
            'Light (0-8 classes)' => 0,
            'Moderate (9-16 classes)' => 0,
            'Heavy (17-24 classes)' => 0,
            'Very Heavy (25+ classes)' => 0
        ];

        foreach ($workloadData as $lecturer) {
            $classes = $lecturer['total_classes'];
            if ($classes <= 8) $distribution['Light (0-8 classes)']++;
            elseif ($classes <= 16) $distribution['Moderate (9-16 classes)']++;
            elseif ($classes <= 24) $distribution['Heavy (17-24 classes)']++;
            else $distribution['Very Heavy (25+ classes)']++;
        }

        return $distribution;
    }

    private function getTimetableOptimizationSuggestions($institutionId): array
    {
        $suggestions = [];
        
        // Check for underutilized time slots
        $slotUtilization = $this->getSlotUtilization($institutionId);
        $underutilizedSlots = collect($slotUtilization)->filter(fn($slot) => $slot['utilization'] < 50);
        
        if ($underutilizedSlots->isNotEmpty()) {
            $suggestions[] = [
                'type' => 'utilization',
                'priority' => 'medium',
                'description' => 'Consider redistributing classes to underutilized time slots',
                'details' => $underutilizedSlots->take(3)->map(fn($slot) => 
                    "Slot {$slot['slot']} ({$slot['day']}): {$slot['utilization']}% utilization"
                )->implode(', ')
            ];
        }

        // Check for room capacity mismatches
        $capacityIssues = $this->getRoomCapacityIssues($institutionId);
        if (!empty($capacityIssues)) {
            $suggestions[] = [
                'type' => 'capacity',
                'priority' => 'high',
                'description' => 'Room capacity mismatches detected',
                'details' => implode(', ', array_slice($capacityIssues, 0, 3))
            ];
        }

        // Check for department clustering
        $clusteringIssues = $this->getDepartmentClusteringIssues($institutionId);
        if (!empty($clusteringIssues)) {
            $suggestions[] = [
                'type' => 'clustering',
                'priority' => 'low',
                'description' => 'Consider spreading classes across different time slots',
                'details' => implode(', ', array_slice($clusteringIssues, 0, 2))
            ];
        }

        return $suggestions;
    }

    private function getSlotUtilization($institutionId): array
    {
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
        $slots = [1 => '7-10am', 2 => '10am-1pm', 3 => '1-4pm', 4 => '4-7pm'];
        $totalRooms = Room::where('institution_id', $institutionId)->count();
        
        $utilization = [];
        
        foreach ($days as $dayNum => $dayName) {
            foreach ($slots as $slotNum => $slotName) {
                $usedSlots = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                        $q->where('institution_id', $institutionId)->where('status', 'published');
                    })
                    ->where('day_of_week', $dayNum)
                    ->where('slot', $slotNum)
                    ->distinct('room_id')
                    ->count();
                
                $utilization[] = [
                    'day' => $dayName,
                    'slot' => $slotName,
                    'day_number' => $dayNum,
                    'slot_number' => $slotNum,
                    'utilization' => $totalRooms > 0 ? round(($usedSlots / $totalRooms) * 100, 1) : 0
                ];
            }
        }
        
        return $utilization;
    }

    private function getRoomCapacityIssues($institutionId): array
    {
        $issues = [];
        
        $entries = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })
            ->with(['room', 'unit'])
            ->get();

        foreach ($entries as $entry) {
            if ($entry->room && $entry->unit && $entry->room->capacity < 50) {
                $issues[] = "{$entry->room->name} (Capacity: {$entry->room->capacity})";
            }
        }
        
        return array_unique($issues);
    }

    private function getDepartmentClusteringIssues($institutionId): array
    {
        $issues = [];
        
        $departmentSlots = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })
            ->with(['unit.course.department'])
            ->get()
            ->groupBy(function($entry) {
                return $entry->day_of_week . '-' . $entry->slot;
            })
            ->filter(function($entries) {
                $departments = $entries->pluck('unit.course.department_id')->unique();
                return $departments->count() === 1; // Only one department in this slot
            })
            ->count();

        if ($departmentSlots > 10) {
            $issues[] = "{$departmentSlots} slots dominated by single departments";
        }
        
        return $issues;
    }

    private function getPeakHoursAnalysis($institutionId): array
    {
        $peakAnalysis = [];
        
        for ($slot = 1; $slot <= 4; $slot++) {
            $totalClasses = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                    $q->where('institution_id', $institutionId)->where('status', 'published');
                })
                ->where('slot', $slot)
                ->count();
            
            $totalRooms = Room::where('institution_id', $institutionId)->count();
            $totalPossibleSlots = $totalRooms * 5; // 5 days
            
            $peakAnalysis[] = [
                'slot' => $slot,
                'slot_name' => $this->getSlotName($slot),
                'total_classes' => $totalClasses,
                'utilization_rate' => $totalPossibleSlots > 0 ? round(($totalClasses / $totalPossibleSlots) * 100, 1) : 0,
                'is_peak' => $totalClasses > ($totalPossibleSlots * 0.7)
            ];
        }
        
        return $peakAnalysis;
    }

    private function getSlotName($slot): string
    {
        $slots = [
            1 => '7:00am - 10:00am',
            2 => '10:00am - 1:00pm',
            3 => '1:00pm - 4:00pm',
            4 => '4:00pm - 7:00pm'
        ];
        
        return $slots[$slot] ?? 'Unknown';
    }

    private function getResourceEfficiencyMetrics($institutionId): array
    {
        $totalRooms = Room::where('institution_id', $institutionId)->count();
        $totalLecturers = User::where('institution_id', $institutionId)->where('role', 'lecturer')->count();
        
        $totalClasses = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })->count();
        
        $uniqueRoomUsage = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })->distinct('room_id')->count();
        
        $uniqueLecturerUsage = TimetableEntry::whereHas('timetable', function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)->where('status', 'published');
            })->distinct('lecturer_id')->count();
        
        return [
            'room_efficiency' => $totalRooms > 0 ? round(($uniqueRoomUsage / $totalRooms) * 100, 1) : 0,
            'lecturer_efficiency' => $totalLecturers > 0 ? round(($uniqueLecturerUsage / $totalLecturers) * 100, 1) : 0,
            'overall_efficiency' => round((($uniqueRoomUsage / max(1, $totalRooms)) + ($uniqueLecturerUsage / max(1, $totalLecturers))) * 50, 1),
            'classes_per_room' => $uniqueRoomUsage > 0 ? round($totalClasses / $uniqueRoomUsage, 1) : 0,
            'classes_per_lecturer' => $uniqueLecturerUsage > 0 ? round($totalClasses / $uniqueLecturerUsage, 1) : 0
        ];
    }

    private function fallbackResponse(string $question, $user): string
    {
        $q = strtolower($question);
        $institution = $user->institution;

        if (str_contains($q, 'room') && (str_contains($q, 'available') || str_contains($q, 'free'))) {
            $now = now();
            $day = max(1, min(5, (int)$now->dayOfWeekIso));
            $slot = $this->timeToSlot($now->format('H:i'));
            
            $availableRooms = $this->getAvailableRooms($institution->id, $day, $slot);
            
            if (empty($availableRooms)) {
                return "No rooms are currently available. All rooms are in use at this time.";
            }
            
            $roomList = collect($availableRooms)->take(5)->map(fn($room) => 
                "{$room['name']} ({$room['department']}, Capacity: {$room['capacity']})"
            )->implode(', ');
            
            return "Available rooms now: {$roomList}";
        }

        if (str_contains($q, 'conflict')) {
            $conflicts = $this->getCurrentConflicts($institution->id);
            
            if (empty($conflicts)) {
                return "No scheduling conflicts detected in current timetables.";
            }
            
            $conflictList = collect($conflicts)->take(3)->map(fn($conflict) => 
                $conflict['description']
            )->implode('; ');
            
            return "Current conflicts: {$conflictList}";
        }

        if (str_contains($q, 'workload') || str_contains($q, 'lecturer')) {
            $workload = $this->getLecturerWorkloadAnalysis($institution->id);
            
            return "Lecturer Workload Analysis: {$workload['total_lecturers']} total lecturers, {$workload['overloaded_lecturers']} overloaded, {$workload['underutilized_lecturers']} underutilized. Top performers: " . 
                   collect($workload['top_performers'])->take(2)->map(fn($l) => $l['name'])->implode(', ');
        }

        if (str_contains($q, 'optimization') || str_contains($q, 'optimize')) {
            $suggestions = $this->getTimetableOptimizationSuggestions($institution->id);
            
            if (empty($suggestions)) {
                return "Timetable is well-optimized. No immediate improvements needed.";
            }
            
            $suggestionList = collect($suggestions)->take(3)->map(fn($s) => 
                "• {$s['description']} ({$s['priority']} priority)"
            )->implode("\n");
            
            return "Optimization Suggestions:\n{$suggestionList}";
        }

        if (str_contains($q, 'peak') || str_contains($q, 'busy')) {
            $peakAnalysis = $this->getPeakHoursAnalysis($institution->id);
            $peakSlots = collect($peakAnalysis)->filter(fn($slot) => $slot['is_peak']);
            
            if ($peakSlots->isEmpty()) {
                return "No peak hours detected. Schedule is evenly distributed.";
            }
            
            $peakTimes = $peakSlots->map(fn($slot) => 
                "{$slot['slot_name']} ({$slot['utilization_rate']}% utilization)"
            )->implode(', ');
            
            return "Peak hours: {$peakTimes}";
        }

        if (str_contains($q, 'efficiency') || str_contains($q, 'resource')) {
            $efficiency = $this->getResourceEfficiencyMetrics($institution->id);
            
            return "Resource Efficiency: Room efficiency {$efficiency['room_efficiency']}%, Lecturer efficiency {$efficiency['lecturer_efficiency']}%, Overall efficiency {$efficiency['overall_efficiency']}%. Average {$efficiency['classes_per_room']} classes per room, {$efficiency['classes_per_lecturer']} classes per lecturer.";
        }

        if (str_contains($q, 'department')) {
            $deptStats = $this->getDepartmentStats($institution->id);
            $totalDepts = count($deptStats);
            
            if ($totalDepts === 0) {
                return "No departments found in the institution.";
            }
            
            return "The institution has {$totalDepts} departments with active courses and lecturers.";
        }

        if (str_contains($q, 'generate') && str_contains($q, 'timetable')) {
            return "To generate a new timetable: Click the 'Generate Timetable' button in the dashboard, provide a name, select academic year/semester if needed, and the system will create an optimized schedule.";
        }

        if (str_contains($q, 'statistic') || str_contains($q, 'overview') || str_contains($q, 'summary')) {
            $data = $this->getInstitutionData($user);
            $efficiency = $data['resource_efficiency'];
            
            return "📊 Institution Overview:\n" .
                   "• {$data['total_departments']} departments, {$data['total_courses']} courses\n" .
                   "• {$data['total_lecturers']} lecturers, {$data['total_students']} students\n" .
                   "• {$data['total_rooms']} rooms, {$data['active_timetables']} active timetables\n" .
                   "• Overall efficiency: {$efficiency['overall_efficiency']}%\n" .
                   "• Room utilization: {$efficiency['room_efficiency']}%\n" .
                   "• Lecturer utilization: {$efficiency['lecturer_efficiency']}%";
        }

        return "I can help you with:\n" .
               "• Room availability and conflicts\n" .
               "• Lecturer workload analysis\n" .
               "• Timetable optimization suggestions\n" .
               "• Peak hours analysis\n" .
               "• Resource efficiency metrics\n" .
               "• Department statistics\n" .
               "• Institution overview\n\n" .
               "Try asking about 'workload analysis', 'optimization suggestions', 'peak hours', or 'efficiency metrics'.";
    }
}
