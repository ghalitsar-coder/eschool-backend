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
        Schema::table('members', function (Blueprint $table) {
            // Adding new columns for member details
            $table->string('nip')->nullable()->after('user_id');
            $table->string('name')->nullable()->after('nip');
            $table->date('date_of_birth')->nullable()->after('name');
            $table->string('gender')->nullable()->after('date_of_birth'); // 'L' or 'P'
            $table->text('address')->nullable()->after('gender');
            $table->string('position')->nullable()->after('address');
            $table->string('status')->default('active')->after('position'); // 'active', 'inactive'
            // Note: 'phone' and 'is_active' already exist from previous migration
            // 'email' is handled by the user relationship, so we don't add it here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Dropping the added columns
            $table->dropColumn(['nip', 'name', 'date_of_birth', 'gender', 'address', 'position', 'status']);
        });
    }
};