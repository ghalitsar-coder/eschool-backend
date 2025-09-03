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
        Schema::table('users', function (Blueprint $table) {
            // Add base_role column with default value 'siswa'
            $table->enum('base_role', ['staff', 'guru', 'siswa'])->default('siswa')->after('role');
            
            // Add is_system_admin column
            $table->boolean('is_system_admin')->default(false)->after('base_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn(['base_role', 'is_system_admin']);
        });
    }
};
