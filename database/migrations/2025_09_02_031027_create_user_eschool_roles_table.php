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
        Schema::create('user_eschool_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('eschool_id')->constrained('eschools')->onDelete('cascade');
            $table->enum('role', ['koordinator', 'bendahara', 'member']);
            $table->timestamp('assigned_at')->default(now());
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Prevent duplicate role assignments
            $table->unique(['user_id', 'eschool_id', 'role'], 'unique_user_eschool_role');
            
            // Optimize queries
            $table->index(['user_id', 'status']);
            $table->index(['eschool_id', 'role', 'status']);
            $table->index(['role', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_eschool_roles');
    }
};
