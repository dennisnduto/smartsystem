<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing simple view rendering...\n\n";

try {
    // Get the data exactly as the controller does
    $user = \App\Models\User::where('lecturer_id', 1)->first();
    
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

    $labCount = 0;

    $entriesWithYear = $entries->map(function($entry) {
        $entry->year_of_study = null;
        return $entry;
    });

    $entriesByDay = $entriesWithYear->groupBy('day_of_week')->map(function($col) {
        return $col->sortBy('slot')->values();
    });

    echo "✅ Data loaded successfully\n";
    echo "   - User: {$user->name}\n";
    echo "   - Entries: {$entries->count()}\n";
    echo "   - Availability: " . count($availability) . " entries\n";

    // Test rendering the view directly
    echo "\nTesting view rendering...\n";
    
    $view = view('lecturer.timetable', [
        'entries' => $entriesWithYear,
        'entriesByDay' => $entriesByDay,
        'availability' => $availability,
        'labCount' => $labCount,
        'user' => $user
    ]);
    
    echo "✅ View object created\n";
    
    // Try to render
    $rendered = $view->render();
    
    echo "✅ View rendered successfully\n";
    echo "Content length: " . strlen($rendered) . " characters\n";
    
    // Check for key elements
    $checks = [
        'DOCTYPE html' => strpos($rendered, '<!DOCTYPE html') !== false,
        'Lecturer Dashboard' => strpos($rendered, 'Lecturer Dashboard') !== false,
        'Welcome back' => strpos($rendered, 'Welcome back') !== false,
        'CSRF token' => strpos($rendered, 'csrf-token') !== false,
        'JavaScript' => strpos($rendered, '<script>') !== false,
        'CSS classes' => strpos($rendered, 'bg-white') !== false,
    ];
    
    echo "\nContent checks:\n";
    foreach ($checks as $check => $passed) {
        echo $passed ? "✅ $check" : "❌ $check";
        echo "\n";
    }
    
    // Save the rendered content
    file_put_contents(public_path('rendered_dashboard.html'), $rendered);
    echo "\n✅ Saved rendered content to: public/rendered_dashboard.html\n";
    
    // Now test with the layout
    echo "\nTesting with layout...\n";
    
    $layoutView = view('lecturer.timetable', [
        'entries' => $entriesWithYear,
        'entriesByDay' => $entriesByDay,
        'availability' => $availability,
        'labCount' => $labCount,
        'user' => $user
    ]);
    
    // The layout should be automatically applied via x-app-layout
    $withLayout = $layoutView->render();
    
    echo "✅ Layout rendering successful\n";
    echo "Layout content length: " . strlen($withLayout) . " characters\n";
    
    file_put_contents(public_path('dashboard_with_layout.html'), $withLayout);
    echo "✅ Saved with layout to: public/dashboard_with_layout.html\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nSimple render test complete.\n";
