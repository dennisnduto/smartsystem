<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Unit;
use App\Models\TimetableEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AIChatbotService
{
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

        // Get context data
        $timetableData = $this->getLecturerTimetableData($user);
        $roomCatalog = $this->getRoomCatalog($user->institution_id);
        $unitRegistry = $this->getUnitRegistry($user->institution_id);
        
        $systemPrompt = "You are a highly knowledgeable 'Campus Expert' and personal schedule assistant for a lecturer named {$user->name}.
        
        YOUR KNOWLEDGE BASE:
        1. PERSONAL SCHEDULE: Full weekly schedule and availability for {$user->name}.
        2. ROOM CATALOG: Details on all available rooms (type, capacity, facilities).
        3. UNIT REGISTRY: All units taught at the institution and which lecturers are assigned to them.
        
        CRITICAL CONTEXT:
        - Workdays: Monday to Friday.
        - Time Slots: 1: 7-10 AM, 2: 10AM-1PM, 3: 1-4 PM, 4: 4-7 PM.
        
        GOAL: Answer ANY question about campus rooms, unit assignments, or your personal schedule accurately. 
        
        LANGUAGE RULE: Always respond in the same language as the user's question (e.g., if asked in Swahili, respond in Swahili).
        
        Be professional, concise, and proactive.";

        $userPrompt = [
            'question' => $question,
            'current_time' => now()->format('Y-m-d H:i:s'),
            'personal_schedule' => $timetableData,
            'campus_rooms' => $roomCatalog,
            'unit_registry' => $unitRegistry
        ];

        try {
            if ($provider === 'gemini') {
                return $this->callGemini($systemPrompt, $userPrompt, $apiKey);
            }

            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->acceptJson()
                ->post(config('services.openai.url', 'https://api.openai.com/v1/chat/completions'), [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => json_encode($userPrompt)],
                    ],
                    'temperature' => 0.7,
                ]);

            if ($response->ok()) {
                return $response->json('choices.0.message.content') ?? $this->fallbackResponse($question, $user);
            }
            
            \Log::error("AI Chatbot ({$provider}) error: " . $response->status() . " - " . $response->body());
        } catch (\Exception $e) {
            \Log::error("AI Chatbot ({$provider}) Exception: " . $e->getMessage());
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

    private function getLecturerTimetableData($user): array
    {
        $lecturerId = $user->lecturer_id;
        if (!$lecturerId && $user->lecturer) {
            $lecturerId = $user->lecturer->id;
        }

        $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
            ->where('lecturer_id', $lecturerId)
            ->whereHas('timetable', function($q) {
                $q->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();

        // Get actual availability settings
        $availabilityRows = \App\Models\LecturerAvailability::where('lecturer_id', $lecturerId)->get();
        $availabilityMatrix = [];
        foreach ($availabilityRows as $row) {
            $availabilityMatrix[$row->day][$row->slot] = $row->status;
        }

        return [
            'summary' => [
                'total_classes_this_week' => $entries->count(),
                'today_classes_count' => $entries->where('day_of_week', max(1, min(5, (int)now()->dayOfWeekIso)))->count(),
                'rooms_used' => $entries->pluck('room.name')->unique()->filter()->values()->all(),
                'units_teaching' => $entries->map(fn($e) => $e->unit->code . ' - ' . $e->unit->name)->unique()->values()->all(),
            ],
            'weekly_schedule' => $entries->map(fn($e) => [
                'day' => [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'][$e->day_of_week] ?? $e->day_of_week,
                'slot' => $e->slot,
                'time' => [1=>'7-10am',2=>'10-1pm',3=>'1-4pm',4=>'4-7pm'][$e->slot] ?? 'Unknown',
                'unit' => $e->unit->code . ' - ' . $e->unit->name,
                'course' => $e->course->name,
                'room' => $e->room->name ?? 'TBA',
            ])->values()->all(),
            'current_availability_settings' => $availabilityMatrix,
            'upcoming_class' => $this->getNextClass($entries),
        ];
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
        $currentDay = max(1, min(5, (int)$now->dayOfWeekIso));
        $currentSlot = $this->timeToSlot($now->format('H:i'));

        $next = $entries->filter(function($e) use ($currentDay, $currentSlot) {
            return $e->day_of_week > $currentDay || 
                   ($e->day_of_week == $currentDay && $e->slot > $currentSlot);
        })->sortBy(['day_of_week','slot'])->first();

        if (!$next) {
            $next = $entries->sortBy(['day_of_week','slot'])->first();
        }

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
        $t = strtotime($time);
        $h = (int)date('H', $t);
        if ($h < 10) return 1;
        if ($h < 13) return 2;
        if ($h < 16) return 3;
        return 4;
    }

    private function fallbackResponse(string $question, $user): string
    {
        $q = strtolower($question);

        if (in_array($q, ['hi', 'hello', 'hey', 'hello there'])) {
            return "Hello {$user->name}! I'm your AI schedule assistant. How can I help you with your classes today?";
        }

        if (str_contains($q, 'next class')) {
            $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
                ->where('lecturer_id', $user->lecturer_id)
                ->whereHas('timetable', function($q) {
                    $q->where('status', 'published');
                })
                ->orderBy('day_of_week')->orderBy('slot')
                ->first();
            
            if ($entries) {
                return sprintf('Your next class is %s (%s) in %s.', 
                    $entries->unit->code, 
                    $entries->course->name ?? '—', 
                    $entries->room->name ?? 'TBA'
                );
            }
            return 'No upcoming classes found.';
        }

        if (str_contains($q, 'today') || str_contains($q, 'teaching today')) {
            $today = max(1, min(5, (int)now()->dayOfWeekIso));
            $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
                ->where('lecturer_id', $user->lecturer_id)
                ->whereHas('timetable', function($q) {
                    $q->where('status', 'published');
                })
                ->where('day_of_week', $today)
                ->orderBy('slot')->get();
            
            if ($entries->isEmpty()) {
                return 'You have no classes today.';
            }
            
            $answer = $entries->map(fn($e) => sprintf('%s in %s (slot %d)', 
                $e->unit->code, 
                $e->room->name ?? 'TBA', 
                $e->slot
            ))->implode('; ');
            
            return "Today's classes: " . $answer;
        }

        if (str_contains($q, 'how many') || str_contains($q, 'total classes')) {
            $count = TimetableEntry::where('lecturer_id', $user->lecturer_id)
                ->whereHas('timetable', function($q) {
                    $q->where('status', 'published');
                })
                ->count();
            
            return "You have {$count} classes scheduled this week.";
        }

        return "I can help you with questions about your schedule, classes, and rooms. Try asking about your next class, today's schedule, or total classes.";
    }
}
