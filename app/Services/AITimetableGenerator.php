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
        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY not configured');
        }

        // Convert timetable filters
        $convertedAcademicYear = (new TimetableGenerator())->convertAcademicYear($timetable->academic_year);
        $convertedSemester = (new TimetableGenerator())->convertSemester($timetable->semester);

        // Gather assignments
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
                'u.id as user_id',
                'u.name as lecturer_name',
                'u.lecturer_id',
                'cuyu.is_lab_only'
            )
            ->get();

        if ($assignments->isEmpty()) {
            return; // nothing to schedule
        }

        // Load rooms for institution
        $rooms = DB::table('rooms as r')
            ->when($timetable->institution_id, fn($q) => $q->where('r.institution_id', $timetable->institution_id))
            ->select('r.id', 'r.name', 'r.capacity', 'r.room_type')
            ->get();

        // Prepare prompt payload
        $system = 'You are an academic scheduling assistant. Create a weekly timetable, 5 days (Mon-Fri), 4 slots/day (1..4). Ensure no lecturer or room clashes. Prefer labs for lab-only units. Return strict JSON matching the schema.';

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
            'timetable' => [
                'id' => $timetable->id,
                'academic_year' => $timetable->academic_year,
                'semester' => $timetable->semester,
            ],
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
        ];

        $response = Http::withToken($apiKey)
            ->timeout(25)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => json_encode(['schema' => $schema, 'data' => $userContent])],
                ],
                'temperature' => 0.2,
            ]);

        if (!$response->ok()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->status());
        }

        $content = Arr::get($response->json(), 'choices.0.message.content');
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
}