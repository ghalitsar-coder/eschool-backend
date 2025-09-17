<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\School;

class TestSchoolFiltering extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-school-filtering';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test school filtering for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Test 1: Check if staff users have school_id
        $staffUsers = User::where('role', 'staff')->get();
        $this->info('=== Staff Users ===');
        foreach ($staffUsers as $user) {
            $this->line("User: {$user->name} ({$user->email})");
            $this->line("School ID: {$user->school_id}");
            if ($user->school) {
                $this->line("School: {$user->school->name}");
            } else {
                $this->line("School: Not assigned");
            }
            $this->line('---');
        }

        // Test 2: Check if student users have school_id
        $studentUsers = User::where('role', 'siswa')->limit(5)->get();
        $this->info('=== Sample Student Users ===');
        foreach ($studentUsers as $user) {
            $this->line("User: {$user->name} ({$user->email})");
            $this->line("School ID: {$user->school_id}");
            if ($user->school) {
                $this->line("School: {$user->school->name}");
            } else {
                $this->line("School: Not assigned");
            }
            $this->line('---');
        }

        // Test 3: Check total count of users by role
        $this->info('=== User Counts by Role ===');
        $this->line('Students: ' . User::where('role', 'siswa')->count());
        $this->line('Coordinators: ' . User::where('role', 'koordinator')->count());
        $this->line('Treasurers: ' . User::where('role', 'bendahara')->count());
        $this->line('Staff: ' . User::where('role', 'staff')->count());

        $this->info('Test completed successfully!');
    }
}
