<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Laravel integration...\n\n";

// Test 1: Check if user can access the route
try {
    $lecturer = \App\Models\User::where('lecturer_id', 1)->first();
    
    // Create a request
    $request = \Illuminate\Http\Request::create('/lecturer/dashboard', 'GET');
    
    // Authenticate the user
    \Illuminate\Support\Facades\Auth::login($lecturer);
    
    echo "✅ User authenticated: {$lecturer->name}\n";
    
    // Test 2: Try to call the controller directly
    $controller = new \App\Http\Controllers\Lecturer\SelfServiceController();
    
    // Set the user on the request
    $request->setUserResolver(function() use ($lecturer) {
        return $lecturer;
    });
    
    echo "✅ Controller instantiated\n";
    
    // Test 3: Call the controller method
    $response = $controller->timetable($request);
    
    echo "✅ Controller method executed\n";
    echo "Response type: " . get_class($response) . "\n";
    
    if (method_exists($response, 'getContent')) {
        $content = $response->getContent();
        echo "Content length: " . strlen($content) . " characters\n";
        
        // Check for common issues
        if (strpos($content, '500') !== false) {
            echo "⚠️  Found 500 error in content\n";
        }
        
        if (strpos($content, 'error') !== false) {
            echo "⚠️  Found 'error' in content\n";
        }
        
        if (strpos($content, 'undefined') !== false) {
            echo "⚠️  Found 'undefined' in content\n";
        }
        
        if (strpos($content, 'Exception') !== false) {
            echo "⚠️  Found 'Exception' in content\n";
        }
    }
    
    // Test 4: Check if the view exists
    if (view()->exists('lecturer.timetable')) {
        echo "✅ View file exists\n";
    } else {
        echo "❌ View file not found\n";
    }
    
    // Test 5: Check layout
    if (view()->exists('layouts.app')) {
        echo "✅ Layout file exists\n";
    } else {
        echo "❌ Layout file not found\n";
    }
    
    // Test 6: Check navigation
    if (view()->exists('layouts.navigation')) {
        echo "✅ Navigation file exists\n";
    } else {
        echo "❌ Navigation file not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nLaravel integration test complete.\n";
