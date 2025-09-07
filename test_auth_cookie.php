<?php
// Simple test script to verify the authentication with cookie works
// Run with: php test_auth_cookie.php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Profile;

try {
    // Delete existing test user if exists
    User::where('email', 'test@example.com')->delete();
    
    // Create a test profile
    $profile = Profile::factory()->create();
    
    // Create a test user
    $user = User::create([
        'profile_id' => $profile->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);
    
    echo "Created user: {$user->name} (ID: {$user->id})
";
    
    // Test login
    $loginData = [
        'email' => 'test@example.com',
        'password' => 'password123',
    ];
    
    echo "Testing login...
";
    
    // Create a request instance
    $request = new \Illuminate\Http\Request();
    $request->setMethod('POST');
    $request->request->add($loginData);
    
    // Instantiate the controller
    $controller = new \App\Http\Controllers\Api\AuthController();
    
    // Call the login method
    $response = $controller->login($request);
    
    echo "Login response status: " . $response->status() . "
";
    
    if ($response->status() === 200) {
        echo "SUCCESS: Login successful
";
        
        // Get token from cookie
        $cookies = $response->headers->getCookies();
        $token = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'token') {
                $token = $cookie->getValue();
                break;
            }
        }
        
        if ($token) {
            echo "Token retrieved from cookie: " . substr($token, 0, 20) . "...
";
            
            // Test refresh token
            echo "Testing refresh token...
";
            
            // Create refresh request with cookie
            $refreshRequest = new \Illuminate\Http\Request();
            $refreshRequest->setMethod('POST');
            $refreshRequest->cookies->add(['token' => $token]);
            
            // Add token to request attributes (like middleware would do)
            $refreshRequest->attributes->set('jwt_token', $token);
            
            // Call the refresh method
            $refreshResponse = $controller->refresh($refreshRequest);
            
            echo "Refresh response status: " . $refreshResponse->status() . "
";
            
            if ($refreshResponse->status() === 200) {
                echo "SUCCESS: Token refreshed
";
            } else {
                echo "ERROR: Failed to refresh token
";
                if ($refreshResponse->getContent()) {
                    echo "Response: " . $refreshResponse->getContent() . "
";
                }
            }
        } else {
            echo "ERROR: Token not found in cookie
";
        }
    } else {
        echo "ERROR: Login failed
";
        if ($response->getContent()) {
            echo "Response: " . $response->getContent() . "
";
        }
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "
";
    echo "Trace: " . $e->getTraceAsString() . "
";
}