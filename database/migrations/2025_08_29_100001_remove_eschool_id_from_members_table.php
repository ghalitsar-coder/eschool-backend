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
            // Dropping the eschool_id column as it's now handled by the pivot table
            $table->dropForeign(['eschool_id']);
            $table->dropColumn('eschool_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Re-adding the eschool_id column in case of rollback
            $table->unsignedBigInteger('eschool_id')->nullable();
            $table->foreign('eschool_id')->references('id')->on('eschools')->onDelete('set null');
        });
    }
};
