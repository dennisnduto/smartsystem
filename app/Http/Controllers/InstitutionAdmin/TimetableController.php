<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Timetable, TimetableEntry, Department, Course, Room, User};
use App\Services\{TimetableGenerator, AITimetableGenerator, ConflictDetector};
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
        
        // Pivot into Matrix for premium side-by-side view (matches lecturer/student views)
        $matrix = [];
        $programs = [];
        $entries = $timetable->entries;

        // Ensure academic year retrieval with fallback
        $entries = $entries->map(function($entry) {
            $cuy = \Illuminate\Support\Facades\DB::table('course_unit_year')
                ->where('course_id', $entry->course_id)
                ->where('unit_id', $entry->unit_id)
                ->first();
            
            $entry->academic_year_val = $cuy->academic_year ?? $entry->timetable->academic_year ?? 'N/A';
            return $entry;
        });

        foreach ($entries as $entry) {
            $courseCode = !empty($entry->course->code) 
                ? strtoupper($entry->course->code) 
                : $this->abbreviateCourseName($entry->course->name ?? 'Unknown Course');
                
            $academicYear = $entry->academic_year_val;
            $key = $courseCode . ' | ' . $academicYear;
            
            if (!isset($programs[$key])) {
                $programs[$key] = [
                    'course' => $courseCode,
                    'year' => $academicYear,
                ];
            }
            $matrix[$entry->day_of_week][$entry->slot][$key] = $entry;
        }

        // Sort programs naturally
        uksort($programs, function($a, $b) {
            return strnatcmp($a, $b);
        });

        // Group and chunk
        $programsByCourse = [];
        foreach ($programs as $key => $details) {
            $course = $details['course'];
            $programsByCourse[$course][$key] = $details;
        }

        $programChunks = [];
        foreach ($programsByCourse as $course => $coursePrograms) {
            $chunks = array_chunk($coursePrograms, 7, true);
            foreach ($chunks as $chunk) {
                $programChunks[] = [
                    'course' => $course,
                    'programs' => $chunk
                ];
            }
        }

        $days = $this->days;
        $slots = [1 => '7:00-10:00', 2 => '10:00-13:00', 3 => '13:00-16:00', 4 => '16:00-19:00'];
        
        // Keep legacy timetableGrid for the "Grouped View" toggle which still uses it
        $timetableGrid = $this->generateTimetableGrid($timetable);
        $courses = collect(); 
        $rooms = Room::whereHas('department', function($q) use ($timetable) {
            $q->where('institution_id', $timetable->institution_id);
        })->get();
        
        $lecturers = User::where('role', 'lecturer')
            ->where('institution_id', $timetable->institution_id)
            ->get();

        $conflictDetector = app(ConflictDetector::class);
        $conflicts = $conflictDetector->detectConflicts($timetable);
        $recommendations = $conflictDetector->getOptimizationRecommendations($timetable);
        
        return view('institution-admin.timetables.show', compact(
            'timetable', 'timetableGrid', 'courses', 'rooms', 'lecturers',
            'matrix', 'programChunks', 'days', 'slots', 'conflicts', 'recommendations'
        ));
    }

    /**
     * Generate entries for a timetable using AI
     */
    public function generateEntries(Timetable $timetable, AITimetableGenerator $aiGenerator)
    {
        $this->authorize('update', $timetable);

        $institutionId = $timetable->institution_id;
        
        // Check if there are any lecturer-unit assignments for this institution
        // Just do a basic check - the generator will handle the actual filtering
        $totalAssignments = \Illuminate\Support\Facades\DB::table('course_unit_year as cuy')
            ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
            ->join('users as u', 'u.id', '=', 'cuyu.user_id')
            ->where('u.institution_id', $institutionId)
            ->count();
        
        if ($totalAssignments === 0) {
            return redirect()->back()->with('error', 
                'No lecturer-unit assignments found in your institution. Please assign units to lecturers first.'
            );
        }
        
        // Log conversion for debugging (but don't block generation)
        $generator = new TimetableGenerator();
        $convertedAcademicYear = $generator->convertAcademicYear($timetable->academic_year);
        $convertedSemester = $generator->convertSemester($timetable->semester);
        
        \Log::info('Timetable generation starting', [
            'timetable_id' => $timetable->id,
            'timetable_year' => $timetable->academic_year,
            'timetable_semester' => $timetable->semester,
            'converted_year' => $convertedAcademicYear,
            'converted_semester' => $convertedSemester,
            'total_assignments' => $totalAssignments
        ]);

        // Ensure all lecturers for this institution who HAVE assignments have submitted availability
        $lecturerUserIds = \Illuminate\Support\Facades\DB::table('users')
            ->where('institution_id', $institutionId)
            ->where('role', 'lecturer')
            ->whereNotNull('lecturer_id')
            ->whereExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('course_unit_year_user')
                    ->whereColumn('course_unit_year_user.user_id', 'users.id');
            })
            ->pluck('id')
            ->unique()
            ->values();

        if ($lecturerUserIds->isNotEmpty()) {
            $withAvailability = \App\Models\LecturerAvailability::whereIn('lecturer_id', $lecturerUserIds)
                ->select('lecturer_id')
                ->distinct()
                ->pluck('lecturer_id');

            $missing = $lecturerUserIds->diff($withAvailability);

            if ($missing->isNotEmpty()) {
                $names = \App\Models\User::whereIn('id', $missing)->pluck('name')->implode(', ');
                return redirect()->back()->with('error', 'Cannot generate timetable. The following lecturers have not filled their availability: ' . $names);
            }
        }
        
        $before = $timetable->entries()->count();

        // Check if OpenAI API key is configured
        $apiKey = config('services.openai.key');
        $useAI = !empty($apiKey);

        if ($useAI) {
            // Try AI generation first if API key is available
            try {
                $aiGenerator->generateForTimetable($timetable);
                
                $timetable->refresh();
                $after = $timetable->entries()->count();
                $added = $after - $before;
                
                if ($added > 0) {
                    return redirect()->route('institution-admin.timetables.show', $timetable)
                        ->with('success', "AI generated $added entries successfully!");
                } else {
                    // AI returned 0 entries, fall back to rule-based
                    \Log::info('AI generation returned 0 entries, falling back to rule-based generator');
                    $useAI = false; // Will use rule-based below
                }
            } catch (\Exception $e) {
                \Log::warning('AI generation failed, using rule-based generator: ' . $e->getMessage());
                $useAI = false; // Will use rule-based below
            }
        }

        // Use rule-based TimetableGenerator (primary method or fallback)
        try {
            $generator = new TimetableGenerator();
            $generator->generateForTimetable($timetable);
            
            $timetable->refresh();
            $after = $timetable->entries()->count();
            $added = $after - $before;
            
            if ($added > 0) {
                $message = $useAI 
                    ? "AI generation failed. Generated $added entries using rule-based method."
                    : "Generated $added entries successfully!";
                    
                return redirect()->route('institution-admin.timetables.show', $timetable)
                    ->with('success', $message);
            } else {
                return redirect()->route('institution-admin.timetables.show', $timetable)
                    ->with('error', 'Generation completed but no entries were created. Please check that lecturers have units assigned and availability set.');
            }
        } catch (\Exception $e) {
            \Log::error('Timetable generation error: ' . $e->getMessage(), [
                'exception' => $e,
                'timetable_id' => $timetable->id
            ]);
            
            return redirect()->route('institution-admin.timetables.show', $timetable)
                ->with('error', "Generation failed: {$e->getMessage()}. Please check your lecturer assignments and availability.");
        }
    }

    /**
     * Approve and publish in one step (simplified flow)
     */
    public function approveAndPublish(Timetable $timetable)
    {
        $this->authorize('update', $timetable);

        if ($timetable->entries->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Cannot approve & publish: Timetable has no entries. Please generate entries first.');
        }

        // Mark approved and then published in one go
        $user = auth()->user();
        $timetable->approve($user);
        $timetable->publish($user);

        return redirect()->back()
            ->with('success', 'Timetable approved and published successfully. It is now visible to students and lecturers.');
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
        
        $timetable->load(['institution', 'department', 'entries.unit', 'entries.course', 'entries.room', 'entries.lecturer', 'entries.teachingGroup']);
        
        $user = auth()->user();
        $courseId = $request->get('course_id');
        $courseName = '';
        
        if ($courseId) {
            $course = $timetable->entries->where('course_id', $courseId)->first()?->course;
            if ($course) {
                $courseName = $course->name;
                $timetable->setRelation('entries', $timetable->entries->where('course_id', $courseId));
            }
        }

        // Generate Matrix logic for PDF (matches show() and SelfServiceController)
        $matrix = [];
        $programs = [];
        $entries = $timetable->entries;

        $entries = $entries->map(function($entry) {
            $cuy = \Illuminate\Support\Facades\DB::table('course_unit_year')
                ->where('course_id', $entry->course_id)
                ->where('unit_id', $entry->unit_id)
                ->first();
            
            $entry->academic_year_val = $cuy->academic_year ?? $entry->timetable->academic_year ?? 'N/A';
            return $entry;
        });

        foreach ($entries as $entry) {
            $courseCode = !empty($entry->course->code) 
                ? strtoupper($entry->course->code) 
                : $this->abbreviateCourseName($entry->course->name ?? 'Unknown Course');
                
            $academicYear = $entry->academic_year_val;
            $key = $courseCode . ' | ' . $academicYear;
            
            if (!isset($programs[$key])) {
                $programs[$key] = [
                    'course' => $courseCode,
                    'year' => $academicYear,
                ];
            }
            $matrix[$entry->day_of_week][$entry->slot][$key] = $entry;
        }

        uksort($programs, function($a, $b) {
            return strnatcmp($a, $b);
        });

        $programsByCourse = [];
        foreach ($programs as $key => $details) {
            $course = $details['course'];
            $programsByCourse[$course][$key] = $details;
        }

        $programChunks = [];
        foreach ($programsByCourse as $course => $coursePrograms) {
            $chunks = array_chunk($coursePrograms, 7, true);
            foreach ($chunks as $chunk) {
                $programChunks[] = [
                    'course' => $course,
                    'programs' => $chunk
                ];
            }
        }

        $days = $this->days;
        $slots = [1 => '7:00-10:00', 2 => '10:00-13:00', 3 => '13:00-16:00', 4 => '16:00-19:00'];
        $title = $courseId ? $courseName . ' Timetable' : 'Institution Wide Timetable';
        $isInstitutional = true;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('institution-admin.timetables.pdf', [
            'timetable' => $timetable,
            'courseName' => $courseName,
            'matrix' => $matrix,
            'programChunks' => $programChunks,
            'days' => $days,
            'slots' => $slots,
            'user' => $user,
            'title' => $title,
            'isInstitutional' => $isInstitutional
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);
        
        $baseFilename = 'Timetable_' . $timetable->name;
        if ($courseName) {
            $baseFilename .= '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $courseName);
        }
        $filename = $baseFilename . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Request approval for timetable
     */
    public function requestApproval(Timetable $timetable)
    {
        $this->authorize('update', $timetable);
        
        if ($timetable->entries->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Cannot request approval: Timetable has no entries. Please generate entries first.');
        }
        
        $timetable->requestApproval();
        
        return redirect()->back()
            ->with('success', 'Timetable submitted for approval. It will be visible to students and lecturers once approved.');
    }

    /**
     * Approve timetable
     */
    public function approve(Timetable $timetable)
    {
        $this->authorize('update', $timetable);
        
        $timetable->approve(auth()->user());
        
        return redirect()->back()
            ->with('success', 'Timetable approved! It is now visible to students and lecturers.');
    }

    /**
     * Reject timetable (send back to draft)
     */
    public function reject(Timetable $timetable)
    {
        $this->authorize('update', $timetable);
        
        $timetable->update(['status' => 'draft']);
        
        return redirect()->back()
            ->with('success', 'Timetable rejected and moved back to draft.');
    }

    /**
     * Publish/Unpublish timetable (final publish after approval)
     */
    public function toggleStatus(Timetable $timetable)
    {
        $this->authorize('update', $timetable);
        
        if ($timetable->status === 'published') {
            $timetable->unpublish();
            $message = 'Timetable unpublished but remains approved.';
        } else {
            $timetable->publish(auth()->user());
            $message = 'Timetable published successfully!';
        }
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Show approval page for timetables
     */
    public function approvals()
    {
        $institution = auth()->user()->institution;
        
        $pendingTimetables = Timetable::where('institution_id', $institution->id)
            ->where('status', 'pending_approval')
            ->with(['department', 'publishedBy'])
            ->latest()
            ->get();
        
        $approvedTimetables = Timetable::where('institution_id', $institution->id)
            ->whereIn('status', ['approved', 'published'])
            ->with(['department', 'approvedBy', 'publishedBy'])
            ->latest()
            ->paginate(15);
        
        return view('institution-admin.timetables.approvals', compact('pendingTimetables', 'approvedTimetables', 'institution'));
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
        
    }

    /**
     * Abbreviate long course names to save space in dense views.
     */
    private function abbreviateCourseName($name)
    {
        $replacements = [
            'Bachelor of Science in Software Engineering' => 'BSE',
            'Bachelor of Science Software Engineering' => 'BSE',
            'Software Engineering' => 'BSE',
            
            'Bachelor of Science in Computer Science' => 'BCS',
            'Bachelor of Science Computer Science' => 'BCS',
            'Computer Science' => 'BCS',
            
            'Bachelor of Science in Computer Technology' => 'BST',
            'Bachelor of Science Computer Technology' => 'BST',
            'Computer Technology' => 'BST',
            
            'Bachelor of Business Information Technology' => 'BBIT',
            'Business Information Technology' => 'BBIT',
            
            'Bachelor of Science in Information Technology' => 'BIT',
            'Bachelor of Science Information Technology' => 'BIT',
            'Information Technology' => 'BIT',
        ];

        foreach ($replacements as $long => $short) {
            if (stripos($name, $long) !== false) {
                return $short;
            }
        }

        // Generic abbreviation if no match
        $words = explode(' ', str_replace(['(', ')', '-', '/'], ' ', $name));
        $abbr = '';
        foreach ($words as $word) {
            if (strlen($word) > 2 && !in_array(strtolower($word), ['and', 'for', 'the', 'with'])) {
                $abbr .= strtoupper($word[0]);
            }
        }
        
        return !empty($abbr) ? $abbr : strtoupper(substr($name, 0, 3));
    }
}
