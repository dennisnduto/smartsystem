<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use Illuminate\Support\Facades\DB;

echo "=== Debugging Institution Mismatch ===\n";

// Check user 13's institution
$user13 = DB::table('users')->where('id', 13)->first();
echo "User 13 institution_id: " . ($user13->institution_id ?? 'null') . "\n";
echo "User 13 lecturer_id: " . ($user13->lecturer_id ?? 'null') . "\n";

// Remove institution filter and see what we get
echo "\nTesting query WITHOUT institution filter...\n";
$assignmentsAll = DB::table('course_unit_year as cuy')
    ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
    ->join('users as u', 'u.id', '=', 'cuyu.user_id')
    ->join('lecturers as L', 'L.id', '=', 'u.lecturer_id')
    ->join('courses as c', 'c.id', '=', 'cuy.course_id')
    ->join('departments as d', 'd.id', '=', 'c.department_id')
    ->where('cuy.academic_year', 'Y4')
    ->where('cuy.semester', 'S1')
    ->select('cuy.id as cuy_id','cuy.unit_id','cuy.academic_year','cuy.semester','u.lecturer_id','cuyu.is_lab_only','L.availability','u.institution_id')
    ->get();

echo "Found " . $assignmentsAll->count() . " assignments without institution filter\n";

if ($assignmentsAll->count() > 0) {
    echo "Sample assignments:\n";
    foreach ($assignmentsAll as $assignment) {
        echo "   - CUY: {$assignment->cuy_id}, Unit: {$assignment->unit_id}, Lecturer: {$assignment->lecturer_id}, Institution: {$assignment->institution_id}\n";
    }
    
    // Check which institutions we should test with
    $institutions = $assignmentsAll->pluck('institution_id')->unique();
    echo "\nInstitutions with assignments: " . $institutions->implode(', ') . "\n";
    
    // Test generation with the correct institution
    echo "\nTesting timetable generation with correct institution...\n";
    $correctInstitutionId = $institutions->first();
    
    $gen = new App\Services\TimetableGenerator();
    try {
        $tt = $gen->generate('Working Test Timetable ' . date('H:i:s'), $correctInstitutionId, 'Y4', 'S1');
        echo "✓ Created timetable: " . $tt->id . " with " . $tt->entries()->count() . " entries\n";
        
        if ($tt->entries()->count() > 0) {
            echo "Sample entries:\n";
            foreach ($tt->entries()->with(['unit', 'lecturer', 'room'])->take(3)->get() as $entry) {
                echo "   - Day " . $entry->day_of_week . 
                     ", Slot " . $entry->slot . 
                     ", Unit: " . ($entry->unit->code ?? 'N/A') . 
                     ", Room: " . ($entry->room->name ?? 'N/A') . "\n";
            }
            
            echo "\n🎉 SUCCESS! View your timetable at: http://localhost:8000/timetables/{$tt->id}\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Still no assignments found. Let me check the joins step by step...\n";
    
    // Progressive join testing
    $step1 = DB::table('course_unit_year as cuy')
        ->where('cuy.academic_year', 'Y4')
        ->where('cuy.semester', 'S1')
        ->count();
    echo "Step 1 - CUY with Y4/S1: {$step1}\n";
    
    $step2 = DB::table('course_unit_year as cuy')
        ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
        ->where('cuy.academic_year', 'Y4')
        ->where('cuy.semester', 'S1')
        ->count();
    echo "Step 2 - Add CUYU join: {$step2}\n";
    
    $step3 = DB::table('course_unit_year as cuy')
        ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
        ->join('users as u', 'u.id', '=', 'cuyu.user_id')
        ->where('cuy.academic_year', 'Y4')
        ->where('cuy.semester', 'S1')
        ->count();
    echo "Step 3 - Add Users join: {$step3}\n";
    
    $step4 = DB::table('course_unit_year as cuy')
        ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
        ->join('users as u', 'u.id', '=', 'cuyu.user_id')
        ->join('lecturers as L', 'L.id', '=', 'u.lecturer_id')
        ->where('cuy.academic_year', 'Y4')
        ->where('cuy.semester', 'S1')
        ->count();
    echo "Step 4 - Add Lecturers join: {$step4}\n";
}

echo "\n=== Debug Complete ===\n";