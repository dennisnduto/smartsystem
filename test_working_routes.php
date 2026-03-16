<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Working vs Non-Working Routes ===\n\n";

// Get Mariga user
$user = \App\Models\User::where('email', 'mariga@gmail.com')->first();
echo "Testing as user: {$user->email}\n\n";

// Test if we can access other working routes
echo "Testing other lecturer routes:\n";

$lecturerRoutes = [
    'lecturer.assigned' => route('lecturer.assigned'),
    'lecturer.rooms' => route('lecturer.rooms'),
    'lecturer.room-bookings.index' => route('lecturer.room-bookings.index'),
];

foreach ($lecturerRoutes as $routeName => $routeUrl) {
    echo "$routeName: $routeUrl\n";
}

echo "\nTesting controller methods:\n";

$controller = new \App\Http\Controllers\Lecturer\SelfServiceController();

// Create a proper request
$request = \Illuminate\Http\Request::create('/lecturer/dashboard', 'GET');
$request->setUserResolver(function() use ($user) {
    return $user;
});

// Test each method
$methods = [
    'timetable' => 'dashboard/timetable',
    'assigned' => 'assigned',
    'rooms' => 'rooms',
];

foreach ($methods as $method => $description) {
    echo "\nTesting $method method ($description):\n";
    
    try {
        $result = $controller->$method($request);
        
        if ($result instanceof \Illuminate\View\View) {
            echo "✓ Returns View: " . $result->getName() . "\n";
            
            // Try to render
            $content = $result->render();
            echo "✓ Renders: " . strlen($content) . " characters\n";
            
            // Check if content has actual data
            if (preg_match('/<main class="py-6">(.*?)<\/main>/s', $content, $matches)) {
                $mainContent = $matches[1];
                if (!empty(trim($mainContent))) {
                    echo "✓ Has main content\n";
                } else {
                    echo "✗ Empty main content\n";
                }
            } else {
                echo "? No main content section found\n";
            }
            
        } else {
            echo "✗ Returns: " . gettype($result) . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== End Test ===\n";
