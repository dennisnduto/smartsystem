<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Institution;
use App\Models\User;
use App\Models\Timetable;
use App\Models\Unit;

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

        // Fallback (kept simple and safe)
        if ($responseText === null) {
            $responseText = "I can help with system stats (institutions, admins, users, timetables). Try: 'How many institutions are active?' or 'How many published timetables?'.";
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
}
