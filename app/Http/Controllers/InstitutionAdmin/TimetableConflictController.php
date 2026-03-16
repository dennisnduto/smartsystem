<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\TimetableEntry;
use App\Services\ConflictDetector;
use Illuminate\Http\Request;

class TimetableConflictController extends Controller
{
    protected $conflictDetector;

    public function __construct(ConflictDetector $conflictDetector)
    {
        $this->conflictDetector = $conflictDetector;
    }

    /**
     * Get suggestions for a conflicting entry
     */
    public function getSuggestions(TimetableEntry $entry)
    {
        $this->authorize('update', $entry->timetable);

        $entry->load(['timetable', 'lecturer', 'room', 'unit']);
        $suggestions = $this->conflictDetector->findFreeSlots($entry);

        return response()->json([
            'entry' => [
                'id' => $entry->id,
                'current_day' => $entry->day_of_week,
                'current_slot' => $entry->slot,
                'unit_code' => $entry->unit->code ?? 'N/A',
                'lecturer' => $entry->lecturer->name ?? 'N/A',
                'room' => $entry->room->name ?? 'N/A',
            ],
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Resolve a conflict by moving an entry
     */
    public function resolve(Request $request, TimetableEntry $entry)
    {
        $this->authorize('update', $entry->timetable);

        $request->validate([
            'day' => 'required|integer|between:1,5',
            'slot' => 'required|integer|between:1,4',
        ]);

        $entry->update([
            'day_of_week' => $request->day,
            'slot' => $request->slot,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Conflict resolved successfully by moving to ' . 
                    ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'][$request->day] . ' ' .
                    [1=>'7-10 AM', 2=>'10-1 PM', 3=>'1-4 PM', 4=>'4-7 PM'][$request->slot]
            ]);
        }

        return redirect()->back()->with('success', 'Entry rescheduled successfully.');
    }
}
