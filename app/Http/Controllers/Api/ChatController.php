<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Institution;
use App\Models\User;
use App\Models\Timetable;
use App\Models\Unit;
use App\Models\Room;
use App\Models\TimetableEntry;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('query', ''));
        $user = $request->user();
        // Fallback to explicit role from request (since API routes are stateless)
        $role = $user?->role ?? (string) $request->input('role', '');

        if ($query === '') {
            return response()->json(['response' => 'Please type a question.']);
        }

        $q = mb_strtolower($query);
        $responseText = null;

        // Super admin helpers
        if ($role === 'super_admin') {
            if (str_contains($q, 'institution')) {
                $active = Institution::where('is_active', true)->count();
                $inactive = Institution::where('is_active', false)->count();
                $responseText = "Institutions: {$active} active, {$inactive} deactivated.";
            } elseif (str_contains($q, 'admin')) {
                $active = User::where('role', 'institution_admin')->where('is_active', true)->count();
                $inactive = User::where('role', 'institution_admin')->where('is_active', false)->count();
                $responseText = "Institution admins: {$active} active, {$inactive} deactivated.";
            } elseif (str_contains($q, 'user')) {
                $active = User::where('is_active', true)->count();
                $inactive = User::where('is_active', false)->count();
                $responseText = "Users: {$active} active, {$inactive} deactivated.";
            } elseif (str_contains($q, 'timetable')) {
                $published = Timetable::where('status', 'published')->count();
                $approved = Timetable::where('status', 'approved')->count();
                $responseText = "Timetables: {$published} published, {$approved} approved (not yet published).";
            } elseif (preg_match('/who\s+teaches\s+([a-z]{3}\d{3,})/i', $query, $m)) {
                // Global unit lookup for super admin
                $code = strtoupper($m[1]);
                $unit = Unit::where('code', $code)->first();
                if (!$unit) {
                    $responseText = "I couldn't find a unit with code {$code}.";
                } else {
                    $lecturerNames = $unit->timetableEntries()
                        ->with('lecturer')
                        ->whereHas('timetable', fn($tq) => $tq->whereIn('status', ['approved', 'published']))
                        ->get()
                        ->pluck('lecturer.name')
                        ->filter()
                        ->unique()
                        ->values();
                    if ($lecturerNames->isEmpty()) {
                        $responseText = "{$code} exists, but I can't see a lecturer assigned in approved/published timetables yet.";
                    } else {
                        $responseText = "{$code} is taught by: " . $lecturerNames->implode(', ') . '.';
                    }
                }
            }
        }

        // Student queries
        if ($role === 'student' && $user) {
            if (str_contains($q, 'next class') || str_contains($q, 'where') || str_contains($q, 'next session')) {
                $now = Carbon::now();
                $dayOfWeek = $now->dayOfWeek; // 1 (Mon) to 7 (Sun)
                $currentHour = $now->hour;
                
                // Map current hour to slot
                $currentSlot = 0;
                if ($currentHour < 10) $currentSlot = 1;
                elseif ($currentHour < 13) $currentSlot = 2;
                elseif ($currentHour < 16) $currentSlot = 3;
                elseif ($currentHour < 19) $currentSlot = 4;

                $nextEntry = TimetableEntry::whereHas('timetable', fn($t) => $t->where('status', 'published'))
                    ->where('course_id', $user->course_id)
                    ->where(function($query) use ($dayOfWeek, $currentSlot) {
                        $query->where('day_of_week', '>', $dayOfWeek)
                              ->orWhere(function($sq) use ($dayOfWeek, $currentSlot) {
                                  $sq->where('day_of_week', $dayOfWeek)
                                     ->where('slot', '>', $currentSlot);
                              });
                    })
                    ->with(['unit', 'room', 'lecturer'])
                    ->orderBy('day_of_week')
                    ->orderBy('slot')
                    ->first();

                if ($nextEntry) {
                    $dayName = [1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday'][$nextEntry->day_of_week] ?? 'Later';
                    $slotTime = [1=>'7:00 AM', 2=>'10:00 AM', 3=>'1:00 PM', 4=>'4:00 PM'][$nextEntry->slot];
                    $responseText = "Your next class is {$nextEntry->unit->name} ({$nextEntry->unit->code}) on {$dayName} at {$slotTime} in Room {$nextEntry->room->name}. Lecturer: {$nextEntry->lecturer->name}.";
                } else {
                    $responseText = "You have no more classes scheduled for this week.";
                }
            }
        }

        // Lecturer queries
        if ($role === 'lecturer' && $user) {
            if (str_contains($q, 'today') || str_contains($q, 'schedule') || str_contains($q, 'teaching')) {
                $dayOfWeek = Carbon::now()->dayOfWeek;
                $entries = TimetableEntry::whereHas('timetable', fn($t) => $t->where('status', 'published'))
                    ->where('lecturer_id', $user->lecturer_id)
                    ->where('day_of_week', $dayOfWeek)
                    ->with(['unit', 'room', 'course'])
                    ->orderBy('slot')
                    ->get();

                if ($entries->isEmpty()) {
                    $responseText = "You have no classes scheduled for today.";
                } else {
                    $list = $entries->map(function($e) {
                        $time = [1=>'7-10 AM', 2=>'10-1 PM', 3=>'1-4 PM', 4=>'4-7 PM'][$e->slot];
                        return "• {$e->unit->code} in Room {$e->room->name} ({$time})";
                    })->implode("\n");
                    $responseText = "Your schedule for today:\n" . $list;
                }
            }
        }

        // Room Availability (Global)
        if (preg_match('/is\s+(room\s+)?([a-z0-9\-]+)\s+free/i', $query, $m)) {
            $roomName = trim($m[2]);
            $room = Room::where('name', 'like', "%{$roomName}%")->first();
            
            if (!$room) {
                $responseText = "I couldn't find a room named '{$roomName}'.";
            } else {
                $now = Carbon::now();
                $dayOfWeek = $now->dayOfWeek;
                $currentHour = $now->hour;
                $currentSlot = 0;
                if ($currentHour >= 7 && $currentHour < 10) $currentSlot = 1;
                elseif ($currentHour >= 10 && $currentHour < 13) $currentSlot = 2;
                elseif ($currentHour >= 13 && $currentHour < 16) $currentSlot = 3;
                elseif ($currentHour >= 16 && $currentHour < 19) $currentSlot = 4;

                if ($currentSlot === 0) {
                    $responseText = "Room {$room->name} is currently free, as it is outside normal teaching hours (7 AM - 7 PM).";
                } else {
                    $isOccupied = TimetableEntry::whereHas('timetable', fn($t) => $t->where('status', 'published'))
                        ->where('room_id', $room->id)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('slot', $currentSlot)
                        ->exists();
                    
                    if ($isOccupied) {
                        $responseText = "No, Room {$room->name} is currently occupied by a scheduled session.";
                    } else {
                        $responseText = "Yes, Room {$room->name} is currently free.";
                    }
                }
            }
        }

        // Fallback
        if ($responseText === null) {
            if ($role === 'student') {
                $responseText = "I can help you find your next class or check room availability. Try: 'Where is my next class?' or 'Is Room 101 free?'.";
            } elseif ($role === 'lecturer') {
                $responseText = "I can show your schedule for today or check room availability. Try: 'What am I teaching today?' or 'Is Room 102 free?'.";
            } else {
                $responseText = "I can help with system stats (institutions, admins, users, timetables) and room availability. Try: 'How many institutions?' or 'Is Room 201 free?'.";
            }
        }

        $log = \App\Models\ChatLog::create([
            'user_id' => $user?->id,
            'role' => $role,
            'query' => $query,
            'response' => $responseText,
        ]);

        return response()->json([
            'id' => $log->id,
            'query' => $query,
            'response' => $responseText,
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $user = $request->user();
        \App\Models\ChatLog::where('user_id', $user?->id)->delete();
        
        return response()->json(['message' => 'Chat history cleared.']);
    }
}
