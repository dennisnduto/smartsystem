<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing direct route access...\n\n";

// Test the route through Laravel's router
try {
    // Create a request for the lecturer dashboard
    $request = \Illuminate\Http\Request::create('/lecturer/dashboard', 'GET');
    
    // Get the lecturer user
    $lecturer = \App\Models\User::where('lecturer_id', 1)->first();
    
    // Authenticate
    \Illuminate\Support\Facades\Auth::login($lecturer);
    
    echo "✅ User authenticated: {$lecturer->name}\n";
    
    // Dispatch the request through the router
    $response = $kernel->handle($request);
    
    echo "✅ Router handled request\n";
    echo "Status code: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        echo "✅ Route returns 200 OK\n";
        
        $content = $response->getContent();
        echo "Content length: " . strlen($content) . " characters\n";
        
        // Save the actual response to a file for inspection
        file_put_contents(public_path('actual_dashboard_response.html'), $content);
        echo "✅ Saved actual response to: public/actual_dashboard_response.html\n";
        
        // Check for specific issues
        if (strpos($content, '<!DOCTYPE html') === 0) {
            echo "✅ Valid HTML document\n";
        } else {
            echo "⚠️  Invalid HTML start\n";
        }
        
        if (strpos($content, 'Lecturer Dashboard') !== false) {
            echo "✅ Dashboard title found\n";
        } else {
            echo "❌ Dashboard title not found\n";
        }
        
        if (strpos($content, 'Welcome back') !== false) {
            echo "✅ User welcome message found\n";
        } else {
            echo "❌ User welcome message not found\n";
        }
        
        // Check for JavaScript errors in the response
        if (strpos($content, 'console.error') !== false) {
            echo "⚠️  JavaScript console.error found in response\n";
        }
        
        // Check for CSS loading
        if (strpos($content, 'app.css') !== false || strpos($content, 'vite') !== false) {
            echo "✅ CSS assets referenced\n";
        } else {
            echo "❌ CSS assets not found\n";
        }
        
    } else {
        echo "❌ Route returns error: " . $response->getStatusCode() . "\n";
        echo "Content: " . $response->getContent() . "\n";
    }
    
    // Terminate the kernel
    $kernel->terminate($request, $response);
    
} catch (\Exception $e) {
    echo "❌ Router error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\nDirect route test complete.\n";
