<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo "Testing web route with proper middleware...\n\n";

try {
    // Bootstrap the application for web requests
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Create a web request (not console)
    $request = \Illuminate\Http\Request::create('/lecturer/dashboard', 'GET', [], [], [], [
        'HTTP_HOST' => '127.0.0.1:8000',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'REMOTE_ADDR' => '127.0.0.1',
    ]);
    
    // Get the lecturer user
    $lecturer = \App\Models\User::where('lecturer_id', 1)->first();
    
    echo "✅ Found lecturer: {$lecturer->name}\n";
    
    // Manually create a session and authenticate
    $session = app('session');
    $session->start();
    $session->put('login_web_' . config('auth.defaults.guard'), $lecturer->id);
    
    // Add session to request
    $request->setLaravelSession($session);
    
    // Authenticate the user
    \Illuminate\Support\Facades\Auth::setUser($lecturer);
    
    echo "✅ User authenticated in session\n";
    
    // Create a response
    $response = new \Illuminate\Http\Response();
    
    // Test the route directly
    $route = $app->router->getRoutes()->match($request);
    
    if ($route) {
        echo "✅ Route found: " . $route->getName() . "\n";
        echo "   URI: " . $route->uri() . "\n";
        echo "   Action: " . $route->getActionName() . "\n";
        echo "   Middleware: " . implode(', ', $route->middleware()) . "\n";
        
        // Test if we can call the controller
        $controller = $route->getController();
        if ($controller) {
            echo "✅ Controller resolved\n";
            
            // Call the controller method
            $controllerResponse = call_user_func($route->getAction('uses'), $request);
            
            if ($controllerResponse instanceof \Illuminate\View\View) {
                echo "✅ Controller returned View\n";
                
                try {
                    $rendered = $controllerResponse->render();
                    echo "✅ View rendered successfully\n";
                    echo "Content length: " . strlen($rendered) . " characters\n";
                    
                    // Save for inspection
                    file_put_contents(public_path('web_route_test.html'), $rendered);
                    echo "✅ Saved to: public/web_route_test.html\n";
                    
                } catch (\Exception $e) {
                    echo "❌ View rendering failed: " . $e->getMessage() . "\n";
                }
            } else {
                echo "⚠️  Controller returned: " . get_class($controllerResponse) . "\n";
            }
        } else {
            echo "❌ Could not resolve controller\n";
        }
    } else {
        echo "❌ Route not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\nWeb route test complete.\n";
