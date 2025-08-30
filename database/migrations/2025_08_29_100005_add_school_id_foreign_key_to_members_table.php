<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if school_id column exists
        if (!Schema::hasColumn('members', 'school_id')) {
            // This should not happen based on the error, but let's be safe
            Schema::table('members', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id');
            });
        }

        // Check if the foreign key constraint already exists
        $foreignKeyExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'members'
            AND COLUMN_NAME = 'school_id'
            AND REFERENCED_TABLE_NAME = 'schools'
        ");

        if (empty($foreignKeyExists)) {
            // If there are existing members with invalid or null school_id, assign them to the first school
            $firstSchoolId = DB::table('schools')->orderBy('id')->first()->id ?? 1;
            
            // Update members with null school_id
            DB::table('members')->whereNull('school_id')->update(['school_id' => $firstSchoolId]);
            
            // Update members with invalid school_id (not in schools table)
            // This is a bit tricky, but we can do it by joining or using a subquery
            // For simplicity, we'll update all members to the first school
            // In a real scenario, you might want to handle this more carefully
            DB::statement("
                UPDATE members m
                LEFT JOIN schools s ON m.school_id = s.id
                SET m.school_id = ?
                WHERE s.id IS NULL
            ", [$firstSchoolId]);

            // Add foreign key constraint
            Schema::table('members', function (Blueprint $table) {
                $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the foreign key constraint exists before dropping
        $foreignKeyExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'members'
            AND COLUMN_NAME = 'school_id'
            AND REFERENCED_TABLE_NAME = 'schools'
        ");

        if (!empty($foreignKeyExists)) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
            });
        }
    }
};