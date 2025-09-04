<?php
// Simple test script to verify the multi-role profile endpoint works
// Run with: php test_endpoint.php

// This script assumes you're running it from the Laravel project root directory

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Enable query logging for debugging
DB::enableQueryLog();

try {
    // Find a test user (you may need to adjust this based on your database)
    $user = \App\Models\User::first();
    
    if (!$user) {
        echo "No users found in database. Please create a test user first.\n";
        exit(1);
    }
    
    echo "Testing with user: {$user->name} (ID: {$user->id}, Role: {$user->role})\n";
    
    // Authenticate the user
    Auth::login($user);
    
    // Create a request instance
    $request = new \Illuminate\Http\Request();
    
    // Instantiate the controller
    $controller = new \App\Http\Controllers\MultiRoleProfileController();
    
    // Call the method
    $response = $controller->getMultiRoleProfile($request);
    
    echo "Response Status: " . $response->status() . "\n";
    
    if ($response->status() === 200) {
        $data = $response->getData(true);
        echo "SUCCESS!\n";
        echo "User: {$data['user']['name']}\n";
        echo "Total Eschools: {$data['overall_summary']['total_eschools']}\n";
        echo "Roles: Koordinator({$data['overall_summary']['roles']['koordinator']}) ";
        echo "Bendahara({$data['overall_summary']['roles']['bendahara']}) ";
        echo "Member({$data['overall_summary']['roles']['member']})\n";
        echo "Performance Score: {$data['overall_summary']['performance']['overall_activity_score']}\n";
        
        if (!empty($data['eschool_roles'])) {
            echo "Eschool Roles:\n";
            foreach ($data['eschool_roles'] as $role) {
                echo "  - {$role['eschool_name']} (Role: {$role['role_in_eschool']})\n";
            }
        }
        
        if (!empty($data['recent_activities'])) {
            echo "Recent Activities:\n";
            foreach (array_slice($data['recent_activities'], 0, 3) as $activity) {
                echo "  - {$activity['eschool_name']}: {$activity['description']} ({$activity['date']})\n";
            }
        }
    } else {
        echo "ERROR: " . $response->getData(true)['message'] ?? 'Unknown error' . "\n";
        if (isset($response->getData(true)['error'])) {
            echo "Details: " . $response->getData(true)['error'] . "\n";
        }
    }
    
    // Show executed queries for debugging
    echo "\nExecuted Queries:\n";
    foreach (DB::getQueryLog() as $query) {
        echo "  - " . $query['query'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}