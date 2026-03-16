<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AITimetableGenerator
{
    /**
     * Generate timetable entries using OpenAI. Falls back by throwing if no API key.
     */
    public function generateForTimetable(Timetable $timetable): void
    {
        $provider = config('services.ai_provider', 'openai');
        $apiKey = config("services.{$provider}.key");
        
        // Auto-detect Gemini if key starts with AIza
        if ($apiKey && str_starts_with($apiKey, 'AIza') && $provider !== 'gemini') {
            $provider = 'gemini';
            $apiKey = config('services.gemini.key');
        }

        if (empty($apiKey)) {
            throw new \RuntimeException('AI API key not configured');
        }

        // Convert timetable filters
        $convertedAcademicYear = (new TimetableGenerator())->convertAcademicYear($timetable->academic_year);
        $convertedSemester = (new TimetableGenerator())->convertSemester($timetable->semester);

        // ... (assignments, rooms, availability logic remains same)
        $assignments = DB::table('course_unit_year as cuy')
            ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
            ->join('users as u', 'u.id', '=', 'cuyu.user_id')
            ->join('lecturers as L', 'L.id', '=', 'u.lecturer_id')
            ->join('courses as c', 'c.id', '=', 'cuy.course_id')
            ->join('units as un', 'un.id', '=', 'cuy.unit_id')
            ->when($timetable->institution_id, fn($q) => $q->where('u.institution_id', $timetable->institution_id))
            ->when($convertedAcademicYear, fn($q) => $q->where('cuy.academic_year', $convertedAcademicYear))
            ->when($convertedSemester, fn($q) => $q->where('cuy.semester', $convertedSemester))
            ->select(
                'cuy.id as cuy_id',
                'cuy.unit_id',
                'un.code as unit_code',
                'un.name as unit_name',
                'cuy.course_id',
                'c.name as course_name',
                'cuy.academic_year',
                'cuy.semester',
                'u.id as lecturer_id', // Changed from u.lecturer_id to u.id
                'u.name as lecturer_name',
                'cuyu.is_lab_only'
            )
            ->get();

        if ($assignments->isEmpty()) {
            return;
        }

        $rooms = DB::table('rooms as r')
            ->when($timetable->institution_id, fn($q) => $q->where('r.institution_id', $timetable->institution_id))
            ->select('r.id', 'r.name', 'r.capacity', 'r.room_type')
            ->get();

        $lecturerIds = $assignments->pluck('lecturer_id')->unique()->filter()->values()->all();
        $availability = DB::table('lecturer_availability')
            ->whereIn('lecturer_id', $lecturerIds)
            ->get();

        // Preload external entries to prevent cross-timetable conflicts
        $externalEntries = TimetableEntry::whereHas('timetable', function($q) use ($timetable) {
                $q->where('institution_id', $timetable->institution_id)
                  ->whereIn('status', ['published', 'approved'])
                  ->where('id', '!=', $timetable->id);
            })
            ->get();

        // Merge external entries into availability context as 'busy'
        $availabilityLookup = $availability->groupBy('lecturer_id');
        foreach ($externalEntries as $ee) {
            if ($ee->lecturer_id) {
                // We add an virtual 'busy' record for the lecturer
                $item = (object)['lecturer_id' => $ee->lecturer_id, 'day' => $ee->day_of_week, 'slot' => $ee->slot, 'status' => 'busy'];
                if (!$availabilityLookup->has($ee->lecturer_id)) {
                    $availabilityLookup->put($ee->lecturer_id, collect([$item]));
                } else {
                    $availabilityLookup->get($ee->lecturer_id)->push($item);
                }
            }
        }

        $system = 'You are an academic scheduling assistant. Create a weekly timetable for an institution.
            CRITICAL CONSTRAINTS:
            1) NO LECTURER CLASHES: Do not schedule a lecturer in any slot they are already marked as "busy" or "unavailable".
            2) NO ROOM CLASHES: Do not schedule a unit in a room that is already occupied in the "occupied_room_slots" provided.
            3) STUDENT CLASHES: A student group (Course + Year) cannot have more than 1 unit in the same timeslot.
            4) LAB CONSTRAINT: Units marked "is_lab_only: true" MUST be assigned to rooms with type "lab". Units marked "is_lab_only: false" should prefer non-lab rooms.
            
            Return strict JSON matching the schema.';

        $schema = [
            'type' => 'object',
            'properties' => [
                'entries' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'cuy_id' => ['type' => 'integer'],
                            'day_of_week' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5],
                            'slot' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 4],
                            'room_id' => ['type' => 'integer']
                        ],
                        'required' => ['cuy_id', 'day_of_week', 'slot', 'room_id']
                    ]
                ]
            ],
            'required' => ['entries']
        ];

        $userContent = [
            'timetable' => ['id' => $timetable->id, 'academic_year' => $timetable->academic_year, 'semester' => $timetable->semester],
            'assignments' => $assignments->map(fn($a) => [
                'cuy_id' => (int)$a->cuy_id,
                'unit_id' => (int)$a->unit_id,
                'unit_code' => $a->unit_code,
                'unit_name' => $a->unit_name,
                'course_id' => (int)$a->course_id,
                'course_name' => $a->course_name,
                'lecturer_id' => (int)$a->lecturer_id,
                'lecturer_name' => $a->lecturer_name,
                'is_lab_only' => (bool)$a->is_lab_only,
            ])->values()->all(),
            'rooms' => $rooms->map(fn($r) => [
                'id' => (int)$r->id,
                'name' => $r->name,
                'type' => $r->room_type,
                'capacity' => (int)$r->capacity,
            ])->values()->all(),
            'lecturer_availability' => $availabilityLookup->map(function($rows) {
                return $rows->map(fn($row) => ['day' => (int)$row->day, 'slot' => (int)$row->slot, 'status' => $row->status]);
            })->all(),
            'occupied_room_slots' => $externalEntries->filter(fn($ee) => $ee->room_id)->map(fn($ee) => [
                'room_id' => (int)$ee->room_id,
                'day' => (int)$ee->day_of_week,
                'slot' => (int)$ee->slot
            ])->values()->all()
        ];

        $content = null;
        if ($provider === 'gemini') {
            $content = $this->callGemini($system, $userContent, $apiKey);
        } else {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->acceptJson()
                ->post(config('services.openai.url'), [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => json_encode(['schema' => $schema, 'data' => $userContent])],
                    ],
                    'temperature' => 0.2,
                ]);

            if ($response->ok()) {
                $content = $response->json('choices.0.message.content');
            } else {
                throw new \RuntimeException("OpenAI API error: " . $response->status() . " - " . $response->body());
            }
        }
        if (!$content) {
            throw new \RuntimeException('OpenAI returned empty content');
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded) || !isset($decoded['entries']) || !is_array($decoded['entries'])) {
            throw new \RuntimeException('OpenAI response not in expected schema');
        }

        // Clear existing entries, then create new ones
        TimetableEntry::where('timetable_id', $timetable->id)->delete();

        foreach ($decoded['entries'] as $e) {
            $cuy = DB::table('course_unit_year')->where('id', $e['cuy_id'] ?? null)->first();
            if (!$cuy) continue;

                TimetableEntry::create([
                    'timetable_id' => $timetable->id,
                'day_of_week' => (int)($e['day_of_week'] ?? 0),
                'slot' => (int)($e['slot'] ?? 0),
                'lecturer_id' => DB::table('course_unit_year_user')->where('course_unit_year_id', $cuy->id)->value('user_id') ?? null,
                'teaching_group_id' => null,
                'unit_id' => (int)$cuy->unit_id,
                'course_id' => (int)$cuy->course_id,
                'room_id' => (int)($e['room_id'] ?? 0),
            ]);
        }
    }

    private function callGemini(string $system, array $userContext, string $apiKey): string
    {
        $baseUrl = config('services.gemini.url');
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $url = "{$baseUrl}{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(60)
            ->acceptJson()
            ->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $system]]
                ],
                'contents' => [
                    ['parts' => [['text' => json_encode($userContext)]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'response_mime_type' => 'application/json',
                ]
            ]);

        if ($response->ok()) {
            return $response->json('candidates.0.content.parts.0.text') ?? '';
        }

        \Log::error("Gemini Timetable API error: " . $response->status() . " - " . $response->body());
        throw new \RuntimeException("Gemini API error: " . $response->status());
    }
}