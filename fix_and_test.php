<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use Illuminate\Support\Facades\DB;
use App\Services\TimetableGenerator;

echo "=== Fixing Lecturer Assignments and Testing ===\n";

// First, let's assign lecturer_id to user 13 (rabit)
echo "1. Assigning lecturer_id to user 13...\n";
$user13 = DB::table('users')->where('id', 13)->first();
if ($user13 && !$user13->lecturer_id) {
    // Assign lecturer ID 1 (Dr. Ada Lovelace) to user 13
    DB::table('users')->where('id', 13)->update(['lecturer_id' => 1]);
    echo "   ✓ User 13 now assigned to lecturer_id 1\n";
}

// Let's also check if we need more room data
echo "\n2. Checking rooms...\n";
$rooms = DB::table('rooms')->count();
echo "   Rooms available: {$rooms}\n";

if ($rooms < 3) {
    echo "   Adding sample rooms...\n";
    DB::table('rooms')->insert([
        ['name' => 'Room 101', 'capacity' => 30, 'room_type' => 'classroom', 'department_id' => 1],
        ['name' => 'Lab 201', 'capacity' => 25, 'room_type' => 'lab', 'department_id' => 1],
        ['name' => 'Room 102', 'capacity' => 40, 'room_type' => 'classroom', 'department_id' => 1],
    ]);
    echo "   ✓ Added 3 sample rooms\n";
}

// Now test the assignments query again
echo "\n3. Testing assignments query...\n";
$assignments = DB::table('course_unit_year as cuy')
    ->join('course_unit_year_user as cuyu', 'cuyu.course_unit_year_id', '=', 'cuy.id')
    ->join('users as u', 'u.id', '=', 'cuyu.user_id')
    ->join('lecturers as L', 'L.id', '=', 'u.lecturer_id')
    ->join('courses as c', 'c.id', '=', 'cuy.course_id')
    ->join('departments as d', 'd.id', '=', 'c.department_id')
    ->where('u.institution_id', 2)
    ->where('cuy.academic_year', 'Y4')
    ->where('cuy.semester', 'S1')
    ->select('cuy.id as cuy_id','cuy.unit_id','cuy.academic_year','cuy.semester','u.lecturer_id','cuyu.is_lab_only','L.availability')
    ->get();

echo "   Found " . $assignments->count() . " assignments\n";

if ($assignments->count() > 0) {
    echo "   Sample assignments:\n";
    foreach ($assignments->take(3) as $assignment) {
        echo "     - CUY: {$assignment->cuy_id}, Unit: {$assignment->unit_id}, Lecturer: {$assignment->lecturer_id}\n";
    }
    
    // Now test timetable generation
    echo "\n4. Testing timetable generation...\n";
    $gen = new TimetableGenerator();
    try {
        $tt = $gen->generate('Fixed Test Timetable ' . date('H:i:s'), 2, 'Y4', 'S1');
        echo "   ✓ Created timetable: " . $tt->id . " with " . $tt->entries()->count() . " entries\n";
        
        if ($tt->entries()->count() > 0) {
            echo "   Sample entries:\n";
            foreach ($tt->entries()->with(['unit', 'lecturer', 'room'])->take(5)->get() as $entry) {
                echo "     - Day " . $entry->day_of_week . 
                     ", Slot " . $entry->slot . 
                     ", Unit: " . ($entry->unit->code ?? 'N/A') . 
                     ", Lecturer: " . ($entry->lecturer->name ?? 'N/A') .
                     ", Room: " . ($entry->room->name ?? 'N/A') . "\n";
            }
            
            // Show URL to view timetable
            echo "\n   🎉 View your timetable at: http://localhost:8000/timetables/{$tt->id}\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
}

echo "\n=== Fix and Test Complete ===\n";