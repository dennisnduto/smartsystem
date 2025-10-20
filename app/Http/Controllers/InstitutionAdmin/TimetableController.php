<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Timetable, Department, Course, Room, User};
use App\Services\{TimetableGenerator, AITimetableGenerator};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TimetableController extends Controller
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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $institution = Auth::user()->institution;
        $timetables = Timetable::where('institution_id', $institution->id)
            ->with(['department'])
            ->latest()
            ->paginate(10);
        
        return view('institution-admin.timetables.index', compact('timetables', 'institution'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $institution = Auth::user()->institution;
        
        return view('institution-admin.timetables.create', compact('institution'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $institution = Auth::user()->institution;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'week_start' => 'required|date'
        ]);

        $timetable = Timetable::create([
            'name' => $request->name,
            'department_id' => null, // Institution-wide timetable
            'institution_id' => $institution->id,
            'semester' => $request->semester,
            'academic_year' => $request->academic_year,
            'week_start' => $request->week_start,
            'status' => 'draft'
        ]);

        return redirect()->route('institution-admin.timetables.show', $timetable)
            ->with('success', 'Institution-wide timetable created successfully. You can now generate entries.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Timetable $timetable)
    {
        $this->authorize('view', $timetable);
        
        $timetable->load(['department', 'entries.unit', 'entries.course', 'entries.room', 'entries.lecturer', 'entries.teachingGroup']);
        
        // Generate timetable grid
        $timetableGrid = $this->generateTimetableGrid($timetable);
        
        $courses = collect(); // optional legacy
        $rooms = Room::whereHas('department', function($q) use ($timetable) {
            $q->where('institution_id', $timetable->institution_id);
        })->get();
        
        $lecturers = User::where('role', 'lecturer')
            ->where('institution_id', $timetable->institution_id)
            ->get();
        
        return view('institution-admin.timetables.show', compact(
            'timetable', 'timetableGrid', 'courses', 'rooms', 'lecturers'
        ));
    }

    /**
     * Generate entries for a timetable using AI
     */
    public function generateEntries(Timetable $timetable, AITimetableGenerator $aiGenerator)
    {
        $this->authorize('update', $timetable);
        
        $before = $timetable->entries()->count();

        try {
            // Try AI generation first
            $aiGenerator->generateForTimetable($timetable);
            
            // Refresh the timetable to get updated entries count
            $timetable->refresh();
            $after = $timetable->entries()->count();
            $added = $after - $before;
            
            if ($added > 0) {
                return redirect()->route('institution-admin.timetables.show', $timetable)
                    ->with('success', "AI generated $added entries successfully!");
            } else {
                // Fallback to rule-based generation
                $fallbackGenerator = new TimetableGenerator();
                $fallbackGenerator->generateForTimetable($timetable);
                
                $timetable->refresh();
                $after = $timetable->entries()->count();
                $added = $after - $before;
                
                return redirect()->route('institution-admin.timetables.show', $timetable)
                    ->with('warning', "AI generation failed. Used fallback method. Generated $added entries.");
            }
            
        } catch (\Exception $e) {
            // Fallback to rule-based generation on error
            $fallbackGenerator = new TimetableGenerator();
            $fallbackGenerator->generateForTimetable($timetable);
            
            $timetable->refresh();
            $after = $timetable->entries()->count();
            $added = $after - $before;
            
            return redirect()->route('institution-admin.timetables.show', $timetable)
                ->with('warning', "AI generation failed: {$e->getMessage()}. Used fallback method. Generated $added entries.");
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timetable $timetable)
    {
        $this->authorize('update', $timetable);
        
        $institution = Auth::user()->institution;
        
        return view('institution-admin.timetables.edit', compact('timetable', 'institution'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Timetable $timetable)
    {
        $this->authorize('update', $timetable);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'week_start' => 'required|date'
        ]);

        $timetable->update([
            'name' => $request->name,
            'semester' => $request->semester,
            'academic_year' => $request->academic_year,
            'week_start' => $request->week_start
        ]);

        return redirect()->route('institution-admin.timetables.index')
            ->with('success', 'Timetable updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable)
    {
        $this->authorize('delete', $timetable);
        
        $timetable->delete();

        return redirect()->route('institution-admin.timetables.index')
            ->with('success', 'Timetable deleted successfully.');
    }

    /**
     * Generate timetable PDF export
     */
    public function exportPdf(Timetable $timetable, Request $request)
    {
        $this->authorize('view', $timetable);
        
        $timetable->load(['institution', 'entries.unit', 'entries.course', 'entries.room', 'entries.lecturer']);
        
        // Check if this is a course-specific export
        $courseId = $request->get('course_id');
        $courseName = '';
        
        if ($courseId) {
            $course = $timetable->entries->where('course_id', $courseId)->first()?->course;
            if ($course) {
                $courseName = $course->name;
                // Filter entries to only include this course
                $timetable->setRelation('entries', $timetable->entries->where('course_id', $courseId));
            }
        }
        
        // Generate PDF using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('institution-admin.timetables.pdf', compact('timetable', 'courseName'));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');
        
        // Set options for better PDF generation
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);
        
        // Generate filename
        $baseFilename = 'Timetable_' . $timetable->name;
        if ($courseName) {
            $baseFilename .= '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $courseName);
        }
        $filename = $baseFilename . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Publish/Unpublish timetable
     */
    public function toggleStatus(Timetable $timetable)
    {
        $this->authorize('update', $timetable);
        
        $newStatus = $timetable->status === 'published' ? 'draft' : 'published';
        $timetable->update(['status' => $newStatus]);
        
        $message = $newStatus === 'published' ? 'Timetable published successfully!' : 'Timetable unpublished.';
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Generate empty timetable grid structure
     */
    private function generateTimetableGrid(Timetable $timetable)
    {
        $grid = [];
        
        // Initialize empty grid
        foreach ($this->days as $dayNum => $dayName) {
            foreach ($this->timeSlots as $slotNum => $timeSlot) {
                $grid[$dayNum][$slotNum] = [
                    'day' => $dayName,
                    'time' => $timeSlot,
                    'entries' => []
                ];
            }
        }
        
        // Populate grid with actual entries if they exist
        if ($timetable->entries) {
            foreach ($timetable->entries as $entry) {
                $grid[$entry->day_of_week][$entry->slot]['entries'][] = $entry;
            }
        }
        
        return $grid;
    }
}
