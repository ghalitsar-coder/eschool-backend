<?php
// Test script for multi-role profile API
// This script can be run from the command line to test the API endpoint

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';

// Create kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get a user to test with (you may need to adjust this)
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Try to find a test user
$user = User::first();
if (!$user) {
    echo "No users found in database\n";
    exit(1);
}

echo "Testing with user: " . $user->name . " (ID: " . $user->id . ")\n";

// Authenticate the user
Auth::login($user);

// Test the multi-role profile endpoint
try {
    $controller = new \App\Http\Controllers\MultiRoleProfileController();
    $request = new \Illuminate\Http\Request();
    
    $response = $controller->getMultiRoleProfile($request);
    
    echo "Response status: " . $response->status() . "\n";
    
    if ($response->status() === 200) {
        $data = $response->getData(true);
        echo "SUCCESS: Multi-role profile data retrieved\n";
        echo "User: " . $data['user']['name'] . "\n";
        echo "Total eschools: " . $data['overall_summary']['total_eschools'] . "\n";
        echo "Roles: Koordinator(" . $data['overall_summary']['roles']['koordinator'] . "), ";
        echo "Bendahara(" . $data['overall_summary']['roles']['bendahara'] . "), ";
        echo "Member(" . $data['overall_summary']['roles']['member'] . ")\n";
    } else {
        echo "ERROR: " . $response->getData(true)['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}