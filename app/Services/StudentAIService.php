<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Unit;
use App\Models\TimetableEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class StudentAIService
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

    public function generateResponse(string $question, $user): string
    {
        $provider = config('services.ai_provider', 'openai');
        $apiKey = config("services.{$provider}.key");
        
        // Auto-detect Gemini if key starts with AIza
        if ($apiKey && str_starts_with($apiKey, 'AIza') && $provider !== 'gemini') {
            $provider = 'gemini';
            $apiKey = config('services.gemini.key');
        }

        if (empty($apiKey)) {
            return $this->fallbackResponse($question, $user);
        }

        // Get student's timetable data for context
        $timetableEntries = $this->getStudentTimetableEntries($user);
        $roomCatalog = $this->getRoomCatalog($user->institution_id);
        $unitRegistry = $this->getUnitRegistry($user->institution_id);
        
        $systemPrompt = "You are a highly knowledgeable 'Campus Expert' and personal schedule assistant for a student named {$user->name}. 
        
        YOUR KNOWLEDGE BASE:
        1. STUDENT SCHEDULE: Full weekly classes for all enrolled courses for {$user->name}.
        2. ROOM CATALOG: Details on all campus rooms (type, capacity, facilities).
        3. UNIT REGISTRY: All units taught at the institution and their assigned lecturers.
        
        CONTEXT:
        - Time Slots: 1: 7-10 AM, 2: 10AM-1PM, 3: 1-4 PM, 4: 4-7 PM.
        - Days: 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday.
        
        GOAL: Answer ANY question about campus rooms, unit assignments, or the student's personal schedule accurately. 
        
        LANGUAGE RULE: Always respond in the same language as the user's question (e.g., if asked in Swahili, respond in Swahili).
        
        Be professional, concise, and helpful.";

        $userContext = [
            'question' => $question,
            'current_time' => now()->format('Y-m-d H:i:s'),
            'student_schedule' => $timetableEntries,
            'campus_rooms' => $roomCatalog,
            'unit_registry' => $unitRegistry
        ];

        try {
            if ($provider === 'gemini') {
                return $this->callGemini($systemPrompt, $userContext, $apiKey);
            }

            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->acceptJson()
                ->post(config('services.openai.url', 'https://api.openai.com/v1/chat/completions'), [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => json_encode($userContext)],
                    ],
                    'temperature' => 0.7,
                ]);

            if ($response->ok()) {
                return $response->json('choices.0.message.content') ?? $this->fallbackResponse($question, $user);
            }
            
            \Log::error("Student AI ({$provider}) error: " . $response->status() . " - " . $response->body());
        } catch (\Exception $e) {
            \Log::error("Student AI ({$provider}) Exception: " . $e->getMessage());
        }

        return $this->fallbackResponse($question, $user);
    }

    private function callGemini(string $system, array $userContext, string $apiKey): string
    {
        $baseUrl = config('services.gemini.url');
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $url = "{$baseUrl}{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(20)
            ->acceptJson()
            ->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $system]]
                ],
                'contents' => [
                    ['parts' => [['text' => json_encode($userContext)]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                ]
            ]);

        if ($response->ok()) {
            return $response->json('candidates.0.content.parts.0.text') ?? $this->fallbackResponse($userContext['question'], null);
        }

        \Log::error("Gemini API error: " . $response->status() . " - " . $response->body());
        throw new \Exception("Gemini API error: " . $response->status());
    }

    private function getStudentTimetableData($user): array
    {
        $entries = $this->getStudentTimetableEntries($user);
        
        $now = now();
        $currentDay = max(1, min(5, (int)$now->dayOfWeekIso));

        return [
            'summary' => [
                'total_classes_this_week' => $entries->count(),
                'today_classes_count' => $entries->where('day_of_week', $currentDay)->count(),
                'courses_enrolled' => $user->courses()->pluck('name')->unique()->filter()->values()->all(),
                'units_enrolled' => $entries->map(fn($e) => $e->unit->code . ' - ' . $e->unit->name)->unique()->values()->all(),
            ],
            'weekly_schedule' => $entries->map(fn($e) => [
                'day' => $this->days[$e->day_of_week] ?? $e->day_of_week,
                'slot' => $e->slot,
                'time' => $this->timeSlots[$e->slot] ?? 'Unknown',
                'unit' => $e->unit->code . ' - ' . $e->unit->name,
                'course' => $e->course->name,
                'room' => $e->room->name ?? 'TBA',
                'lecturer' => $e->lecturer->name ?? 'Unknown',
            ])->values()->all(),
            'upcoming_class' => $this->getNextClass($entries),
        ];
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
        $query = TimetableEntry::with(['unit', 'course', 'room', 'lecturer', 'timetable'])
            ->whereHas('timetable', function($q) use ($user) {
                $q->where('institution_id', $user->institution_id)
                  ->where('status', 'published');
            })
            ->where(function($q) use ($courseIds, $studentYear) {
                // Match by course_id first
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

        // Remove duplicates based on unit_id, day_of_week, and slot
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

    private function getRoomCatalog(?int $institutionId): array
    {
        return Room::where('institution_id', $institutionId)
            ->select('name', 'room_type', 'capacity', 'facilities')
            ->get()
            ->toArray();
    }

    private function getUnitRegistry(?int $institutionId): array
    {
        return Unit::where('institution_id', $institutionId)
            ->with(['timetableEntries' => function($q) {
                $q->whereHas('timetable', fn($t) => $t->where('status', 'published'))
                  ->with('lecturer:id,name');
            }])
            ->get()
            ->map(function($unit) {
                return [
                    'code' => $unit->code,
                    'name' => $unit->name,
                    'assigned_lecturers' => $unit->timetableEntries->pluck('lecturer.name')->unique()->values()->toArray()
                ];
            })
            ->toArray();
    }

    private function getNextClass($entries)
    {
        $now = now();
        $currentDay = (int)$now->dayOfWeekIso;
        $currentTime = $now->format('H:i');

        // If it's weekend, just return the first class of the week (Monday)
        if ($currentDay >= 6) {
            return $this->formatClassEntry($entries->sortBy(['day_of_week', 'slot'])->first());
        }

        $currentSlot = $this->timeToSlot($currentTime);

        $next = $entries->first(function($e) use ($currentDay, $currentSlot) {
            return $e->day_of_week > $currentDay || 
                   ($e->day_of_week == $currentDay && $e->slot > $currentSlot);
        });

        // Special case: before 7am today
        if (!$next && $currentDay <= 5 && $currentTime < '07:00') {
            $next = $entries->where('day_of_week', $currentDay)->sortBy('slot')->first();
        }

        // Wrap around fallback
        if (!$next) {
            $next = $entries->sortBy(['day_of_week', 'slot'])->first();
        }

        return $this->formatClassEntry($next);
    }

    private function formatClassEntry($next)
    {
        if (!$next) return null;

        return [
            'unit_code' => $next->unit->code ?? null,
            'unit_name' => $next->unit->name ?? null,
            'course_name' => $next->course->name ?? null,
            'room_name' => $next->room->name ?? null,
            'day_of_week' => $next->day_of_week,
            'slot' => $next->slot,
        ];
    }

    private function timeToSlot(string $time): int
    {
        if ($time < '07:00') return 0;
        if ($time < '10:00') return 1;
        if ($time < '13:00') return 2;
        if ($time < '16:00') return 3;
        if ($time < '19:00') return 4;
        return 5;
    }

    private function fallbackResponse(string $question, $user): string
    {
        $query = strtolower($question);

        // Basic greetings
        if (in_array($query, ['hi', 'hello', 'hey', 'hello there', 'morning', 'afternoon', 'evening'])) {
            return "Hello {$user->name}! I'm your AI schedule assistant. How can I help you with your timetable today?";
        }

        if (str_contains($query, 'how are you')) {
            return "I'm doing great, thank you for asking! I'm ready to help you with any questions about your classes or rooms. What's on your mind?";
        }

        if (str_contains($query, 'thank') || $query === 'thanks') {
            return "You're very welcome, {$user->name}! Let me know if you need anything else.";
        }

        // "When is my next lecture?"
        if (str_contains($query, 'next lecture') || str_contains($query, 'next class')) {
            $entries = $this->getStudentTimetableEntries($user);
            $nextLecture = $this->getNextClass($entries);
            if ($nextLecture) {
                $dayName = $this->days[$nextLecture['day_of_week']] ?? 'Unknown';
                $timeSlot = $this->timeSlots[$nextLecture['slot']] ?? 'Unknown';
                $answer = sprintf(
                    'Your next lecture is %s (%s) in %s on %s, %s.',
                    $nextLecture['unit_code'] ?? 'Unknown',
                    $nextLecture['unit_name'] ?? 'Unknown',
                    $nextLecture['room_name'] ?? 'TBA',
                    $dayName,
                    $timeSlot
                );
            } else {
                $answer = 'You have no upcoming lectures.';
            }
            return $answer;
        }

        // "Which room is free now?"
        if (str_contains($query, 'room') && (str_contains($query, 'free') || str_contains($query, 'available'))) {
            $now = now();
            $day = (int)$now->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
            $slot = $this->timeToSlot($now->format('H:i'));

            $allBusyRoomIds = collect();

            // Only check for busy rooms during active slots on weekdays (Mon-Fri)
            if ($day >= 1 && $day <= 5 && $slot >= 1 && $slot <= 4) {
                $busyRoomIds = TimetableEntry::where('day_of_week', $day)
                    ->where('slot', $slot)
                    ->whereHas('timetable', function($q) use ($user) {
                        $q->where('institution_id', $user->institution_id)
                          ->where('status', 'published');
                    })
                    ->pluck('room_id');

                $bookingBusyRoomIds = \App\Models\RoomBooking::where('institution_id', $user->institution_id)
                    ->where('status', 'active')
                    ->where('booking_date', $now->toDateString())
                    ->where(function($q) use ($now) {
                        $q->where('start_time', '<=', $now->format('H:i:s'))
                          ->where('end_time', '>=', $now->format('H:i:s'));
                    })
                    ->pluck('room_id');

                $allBusyRoomIds = $busyRoomIds->merge($bookingBusyRoomIds)->unique();
            }

            $availableRooms = Room::where('institution_id', $user->institution_id)
                ->whereNotIn('id', $allBusyRoomIds)
                ->orderBy('name')
                ->limit(10)
                ->pluck('name');

            if ($availableRooms->isNotEmpty()) {
                $statusMsg = ($day >= 6 || $slot === 0) ? 'It\'s currently outside normal class hours, so most rooms are free. ' : '';
                $answer = $statusMsg . 'Available rooms: ' . $availableRooms->implode(', ');
            } else {
                $answer = 'No rooms are currently available in the system.';
            }
            
            return $answer;
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
            return $answer;
        }

        return 'Sorry, I did not understand. Try: "Hi", "When is my next lecture?", "Which room is free now?", or "Who teaches SIT401?"';
    }
}
