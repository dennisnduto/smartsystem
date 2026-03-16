<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Simplified View ===\n\n";

// Get Mariga user and prepare data
$user = \App\Models\User::where('email', 'mariga@gmail.com')->first();

$entries = \App\Models\TimetableEntry::with(['unit', 'course', 'room', 'timetable'])
    ->where('lecturer_id', $user->lecturer_id)
    ->whereHas('timetable', function($q) {
        $q->where('status', 'published');
    })
    ->orderBy('day_of_week')
    ->orderBy('slot')
    ->get();

$viewData = [
    'entries' => $entries,
    'user' => $user,
    'labCount' => 1
];

echo "Testing simplified view...\n";

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
    
    // Render the simplified view
    $html = view('lecturer.timetable-test', $viewData)->render();
    
    echo "Simplified view rendered: " . strlen($html) . " characters\n";
    
    // Check for content
    $checks = [
        'Lecturer Dashboard header' => strpos($html, 'Lecturer Dashboard') !== false,
        'Welcome back message' => strpos($html, 'Welcome back') !== false,
        'User name' => strpos($html, $user->name) !== false,
        'This Week Classes' => strpos($html, 'This Week Classes') !== false,
        'Stats grid' => strpos($html, 'grid grid-cols-1') !== false,
        'Today\'s Classes' => strpos($html, 'Today\'s Classes') !== false,
    ];
    
    foreach ($checks as $test => $passed) {
        echo $passed ? "✓ $test\n" : "✗ $test\n";
    }
    
    // Look for main content
    if (preg_match('/<main class="py-6">(.*?)<\/main>/s', $html, $matches)) {
        $mainContent = $matches[1];
        echo "Main content length: " . strlen($mainContent) . " characters\n";
        
        if (!empty(trim($mainContent))) {
            echo "✓ Main content has data\n";
        } else {
            echo "✗ Main content is empty\n";
        }
    }
    
    // Save for inspection
    file_put_contents('simplified_test.html', $html);
    echo "Saved to simplified_test.html\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== End Test ===\n";
