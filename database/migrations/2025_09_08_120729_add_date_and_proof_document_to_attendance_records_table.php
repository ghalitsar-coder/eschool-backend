<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_record', function (Blueprint $table) {
            // Add date column (required for attendance tracking)
            $table->date('date')->after('user_eschool_role_id');
            
            // Add proof_document column (nullable, for storing file paths)
            $table->string('proof_document', 255)->nullable()->after('notes');
            
            // Add indexes for performance optimization
            $table->index('date', 'idx_attendance_date');
        });
        
        // Add unique constraint separately to avoid foreign key issues
        Schema::table('attendance_record', function (Blueprint $table) {
            $table->unique(['user_eschool_role_id', 'date'], 'unique_user_eschool_role_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_record', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('unique_user_eschool_role_date');
            
            // Drop index
            $table->dropIndex('idx_attendance_date');
            
            // Drop columns
            $table->dropColumn(['date', 'proof_document']);
        });
    }
};
