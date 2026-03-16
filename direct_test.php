<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Direct View Test ===\n\n";

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get Mariga user
$user = \App\Models\User::where('email', 'mariga@gmail.com')->first();

// Prepare data exactly as controller does
$entries = \App\Models\TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
    ->where('lecturer_id', $user->lecturer_id)
    ->whereHas('timetable', function($q) {
        $q->where('status', 'published');
    })
    ->orderBy('day_of_week')
    ->orderBy('slot')
    ->get();

$availabilityRows = \App\Models\LecturerAvailability::where('lecturer_id', $user->lecturer_id)->get();
$availability = [];
foreach ($availabilityRows as $row) {
    $availability[(int) $row->day][(int) $row->slot] = $row->status;
}

$labCount = \App\Models\TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
    ->where('lecturer_id', $user->lecturer_id)
    ->whereHas('timetable', function($q) {
        $q->where('status', 'published');
    })
    ->whereHas('room', function($q) {
        $q->where('room_type', 'LIKE', '%lab%')
          ->orWhere('room_type', 'LIKE', '%Laboratory%')
          ->orWhere('room_type', 'LIKE', '%LAB%');
    })
    ->distinct('unit_id')
    ->count('unit_id');

$entriesWithYear = $entries->map(function($entry) {
    $yearOfStudy = null;
    if ($entry->unit_id && $entry->course_id) {
        $courseUnitYear = DB::table('course_unit_year')
            ->where('course_id', $entry->course_id)
            ->where('unit_id', $entry->unit_id)
            ->first();
        $yearOfStudy = $courseUnitYear ? $courseUnitYear->academic_year : null;
    }
    $entry->year_of_study = $yearOfStudy;
    return $entry;
});

$entriesByDay = $entriesWithYear->groupBy('day_of_week')->map(function($col) {
    return $col->sortBy('slot')->values();
});

$viewData = [
    'entries' => $entriesWithYear,
    'entriesByDay' => $entriesByDay,
    'availability' => $availability,
    'labCount' => $labCount,
    'user' => $user
];

echo "Data prepared, testing view rendering...\n";

try {
    // Clear view cache
    $viewPath = storage_path('framework/views');
    if (is_dir($viewPath)) {
        $files = glob($viewPath . '/*.php');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    // Set auth context
    auth()->login($user);
    
    // Render the view
    $html = view('lecturer.timetable', $viewData)->render();
    
    echo "View rendered successfully\n";
    echo "HTML length: " . strlen($html) . "\n";
    
    // Look for main content
    if (preg_match('/<main class="py-6">(.*?)<\/main>/s', $html, $matches)) {
        $mainContent = $matches[1];
        echo "Main content found: " . strlen($mainContent) . " characters\n";
        
        if (empty(trim($mainContent))) {
            echo "✗ Main content is empty\n";
        } else {
            echo "✓ Main content has data\n";
            echo "First 200 chars:\n" . substr($mainContent, 0, 200) . "\n";
        }
    } else {
        echo "✗ Main content section not found\n";
    }
    
    // Save full HTML for inspection
    file_put_contents('direct_test_output.html', $html);
    echo "Full HTML saved to direct_test_output.html\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== End Test ===\n";
