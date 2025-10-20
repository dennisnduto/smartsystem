<?php

namespace App\Http\Controllers;

use App\Services\TimetableGenerator;
use App\Services\ConflictDetector;
use App\Models\Timetable;
use App\Models\Department;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function generate(Request $request, TimetableGenerator $generator)
    {
        if (!$request->user()) abort(401);
        if ($request->user()->role !== 'institution_admin') abort(403);

        $data = $request->validate([
            'name' => 'required|string',
            'department_id' => 'nullable|integer|exists:departments,id',
            'academic_year' => 'nullable|string',
            'semester' => 'nullable|string',
        ]);

        $deptId = $data['department_id'] ?? null;
        if ($deptId) {
            $dept = Department::findOrFail($deptId);
            if ($dept->institution_id !== $request->user()->institution_id) abort(403);
        } else {
            $dept = Department::where('institution_id', $request->user()->institution_id)->first();
            if (!$dept) abort(422, 'No department found for your institution.');
            $deptId = $dept->id;
        }

        $institutionId = $request->user()->institution_id;
        $tt = $generator->generate($data['name'], $institutionId, $data['academic_year'] ?? null, $data['semester'] ?? null);
        return response()->json($tt);
    }

    public function show(Timetable $timetable, ConflictDetector $conflictDetector)
    {
        $user = auth()->user();
        if ($user && $user->role === 'super_admin') {
            // allow
        } else {
            $timetable->loadMissing('department');
            $ttInstitutionId = optional($timetable->department)->institution_id ?? $timetable->institution_id;
            if (!$user || $user->institution_id !== $ttInstitutionId) abort(403);
        }

        // Load relationships and detect conflicts
        $timetable->load('entries.room','entries.unit','entries.lecturer','entries.teachingGroup');
        $conflicts = $conflictDetector->detectConflicts($timetable);
        $recommendations = $conflictDetector->getOptimizationRecommendations($timetable);

        return view('timetables.show', [
            'timetable' => $timetable,
            'conflicts' => $conflicts,
            'recommendations' => $recommendations
        ]);
    }

    public function generateWeb(Request $request, TimetableGenerator $generator)
    {
        if (!$request->user()) abort(401);
        if ($request->user()->role !== 'institution_admin') abort(403);

        $name = $request->query('name', 'Default Week');
        $academicYear = $request->query('academic_year');
        $semester = $request->query('semester');

        $dept = Department::where('institution_id', $request->user()->institution_id)->first();
        if (!$dept) abort(422, 'No department found for your institution.');

        $tt = $generator->generate($name, $request->user()->institution_id, $academicYear, $semester);
        return redirect()->route('timetables.show', ['timetable' => $tt->id]);

    }
}
