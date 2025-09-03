<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\AttendanceRecord;

// Login as koordinator user
$user = User::find(3);
$token = auth('api')->login($user);

echo "Token: " . $token . PHP_EOL;
echo "User: " . $user->name . " (Role: " . $user->role . ")" . PHP_EOL;

// Check if attendance record exists
$attendance = AttendanceRecord::find(47);
if ($attendance) {
    echo "Attendance record found: ID {$attendance->id}, Member ID {$attendance->member_id}" . PHP_EOL;
    
    // Attempt to delete
    try {
        $deleted = $attendance->delete();
        echo "Delete result: " . ($deleted ? 'true' : 'false') . PHP_EOL;
        
        // Check if still exists
        $check = AttendanceRecord::find(47);
        echo "Record still exists after delete: " . ($check ? 'YES' : 'NO') . PHP_EOL;
        
    } catch (Exception $e) {
        echo "Error during delete: " . $e->getMessage() . PHP_EOL;
    }
} else {
    echo "Attendance record with ID 47 not found" . PHP_EOL;
}

// List some attendance records to see what exists
echo "\nExisting attendance records:" . PHP_EOL;
$records = AttendanceRecord::where('eschool_id', 1)->take(5)->get();
foreach ($records as $record) {
    echo "ID: {$record->id}, Member: {$record->member_id}, Date: {$record->date}" . PHP_EOL;
}
