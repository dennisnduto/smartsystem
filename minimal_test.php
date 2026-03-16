<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Minimal Test ===\n\n";

// Test with minimal data to isolate the issue
$user = \App\Models\User::where('email', 'mariga@gmail.com')->first();

echo "Testing with minimal view data...\n";

try {
    // Create minimal test data
    $minimalData = [
        'user' => $user,
        'entries' => collect([]),
        'entriesByDay' => collect([]),
        'availability' => [],
        'labCount' => 0
    ];
    
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
    
    // Render just a simple test
    $testHtml = '<x-app-layout>
    <x-slot name="header">
        <h2>Test Header</h2>
    </x-slot>
    
    <div class="max-w-7xl mx-auto p-6">
        <h1>Test Content</h1>
        <p>User: {{ $user->name }}</p>
        <p>This is a test to see if content renders.</p>
    </div>
</x-app-layout>';
    
    $result = view()->make(['string' => $testHtml], $minimalData)->render();
    
    echo "Test rendered: " . strlen($result) . " characters\n";
    
    // Check if content is in the result
    if (strpos($result, 'Test Content') !== false) {
        echo "✓ Test content found\n";
    } else {
        echo "✗ Test content not found\n";
    }
    
    if (strpos($result, $user->name) !== false) {
        echo "✓ User name found\n";
    } else {
        echo "✗ User name not found\n";
    }
    
    // Save for inspection
    file_put_contents('minimal_test.html', $result);
    echo "Saved to minimal_test.html\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== End Test ===\n";
