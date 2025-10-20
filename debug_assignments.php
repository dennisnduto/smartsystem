<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use Illuminate\Support\Facades\DB;

echo "=== Debugging Timetable Generation Data ===\n";

// Check the assignments query that TimetableGenerator uses
echo "Testing the assignment query from TimetableGenerator...\n";
$assignments = DB::table('course_unit_year as cuy')
    ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
    ->join('users as u', 'u.id', '=', 'cuyu.user_id')
    ->join('lecturers as L', 'L.id', '=', 'u.lecturer_id')
    ->join('courses as c', 'c.id', '=', 'cuy.course_id')
    ->join('departments as d', 'd.id', '=', 'c.department_id')
    ->where('u.institution_id', 1)
    ->where('cuy.academic_year', 'Y4')
    ->where('cuy.semester', 'S1')
    ->select('cuy.id as cuy_id','cuy.unit_id','cuy.academic_year','cuy.semester','u.lecturer_id','cuyu.is_lab_only','L.availability')
    ->get();

echo "Found " . $assignments->count() . " assignments\n";

if ($assignments->isEmpty()) {
    echo "No assignments found. Let's check each table:\n\n";
    
    echo "1. CourseUnitYear table:\n";
    $cuy = DB::table('course_unit_year')->get();
    echo "   Records: " . $cuy->count() . "\n";
    foreach ($cuy->take(3) as $record) {
        echo "   - ID: {$record->id}, Course: {$record->course_id}, Unit: {$record->unit_id}, Year: {$record->academic_year}, Semester: {$record->semester}\n";
    }
    
    echo "\n2. course_unit_year_user table:\n";
    $cuyu = DB::table('course_unit_year_user')->get();
    echo "   Records: " . $cuyu->count() . "\n";
    foreach ($cuyu->take(3) as $record) {
        echo "   - CUY_ID: {$record->course_unit_year_id}, User_ID: {$record->user_id}\n";
    }
    
    echo "\n3. Users with lecturer_id:\n";
    $lecturerUsers = DB::table('users')->whereNotNull('lecturer_id')->get();
    echo "   Records: " . $lecturerUsers->count() . "\n";
    foreach ($lecturerUsers->take(3) as $user) {
        echo "   - User ID: {$user->id}, Lecturer ID: {$user->lecturer_id}, Institution: {$user->institution_id}\n";
    }
    
    echo "\n4. Lecturers table:\n";
    $lecturers = DB::table('lecturers')->get();
    echo "   Records: " . $lecturers->count() . "\n";
    foreach ($lecturers->take(3) as $lecturer) {
        echo "   - ID: {$lecturer->id}, Name: {$lecturer->name}\n";
    }
    
    echo "\n5. Testing individual joins:\n";
    
    // Test CUY -> CUYU join
    $joinTest1 = DB::table('course_unit_year as cuy')
        ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
        ->count();
    echo "   CUY -> CUYU join: {$joinTest1} records\n";
    
    // Test CUYU -> Users join
    $joinTest2 = DB::table('course_unit_year_user as cuyu')
        ->join('users as u', 'u.id', '=', 'cuyu.user_id')
        ->count();
    echo "   CUYU -> Users join: {$joinTest2} records\n";
    
    // Test Users -> Lecturers join
    $joinTest3 = DB::table('users as u')
        ->join('lecturers as L', 'L.id', '=', 'u.lecturer_id')
        ->count();
    echo "   Users -> Lecturers join: {$joinTest3} records\n";
    
} else {
    echo "Sample assignments:\n";
    foreach ($assignments->take(3) as $assignment) {
        echo "   - CUY: {$assignment->cuy_id}, Unit: {$assignment->unit_id}, Lecturer: {$assignment->lecturer_id}\n";
    }
}

echo "\n=== Debug Complete ===\n";