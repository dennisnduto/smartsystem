<?php

namespace App\Services;

use App\Models\TimetableEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AIChatbotService
{
    public function generateResponse(string $question, $user): string
    {
        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            return $this->fallbackResponse($question, $user);
        }

        // Get lecturer's timetable data for context
        $timetableData = $this->getLecturerTimetableData($user);
        
        $systemPrompt = "You are a helpful university timetable assistant for a lecturer. 
        You have access to their current timetable data. Provide helpful, accurate answers about their schedule, 
        classes, rooms, and availability. Be concise and friendly. If you don't know something, say so honestly.";

        $userPrompt = [
            'question' => $question,
            'lecturer_name' => $user->name,
            'current_time' => now()->format('Y-m-d H:i:s'),
            'timetable_data' => $timetableData
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
                    'max_tokens' => 300,
                ]);

            if ($response->ok()) {
                $content = $response->json('choices.0.message.content');
                return $content ?? $this->fallbackResponse($question, $user);
            }
        } catch (\Exception $e) {
            \Log::error('AI Chatbot error: ' . $e->getMessage());
        }

        return $this->fallbackResponse($question, $user);
    }

    private function getLecturerTimetableData($user): array
    {
        $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
            ->where('lecturer_id', $user->lecturer_id)
            ->whereHas('timetable', function($q) {
                $q->where('status', 'published');
            })
            ->orderBy('day_of_week')
            ->orderBy('slot')
            ->get();

        return [
            'total_classes' => $entries->count(),
            'today_classes' => $entries->where('day_of_week', max(1, min(5, (int)now()->dayOfWeekIso)))->count(),
            'upcoming_class' => $this->getNextClass($entries),
            'rooms_used' => $entries->pluck('room.name')->unique()->filter()->values()->all(),
            'courses_teaching' => $entries->pluck('course.name')->unique()->filter()->values()->all(),
            'units_teaching' => $entries->map(fn($e) => $e->unit->code . ' - ' . $e->unit->name)->unique()->values()->all(),
        ];
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

        if (str_contains($q, 'next class')) {
            $entries = TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
                ->where('lecturer_id', $user->lecturer_id)
                ->whereHas('timetable', function($q) {
                    $q->where('status', 'published');
                })
                ->orderBy('day_of_week')->orderBy('slot')
                ->first();
            
            if ($entry) {
                return sprintf('Your next class is %s (%s) in %s.', 
                    $entry->unit->code, 
                    $entry->course->name ?? '—', 
                    $entry->room->name ?? 'TBA'
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
