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
        Schema::create('eschool_member', function (Blueprint $table) {
            $table->id();
            // Defining foreign keys
            $table->unsignedBigInteger('eschool_id');
            $table->unsignedBigInteger('member_id');

            // Adding foreign key constraints
            $table->foreign('eschool_id')->references('id')->on('eschools')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');

            // Ensuring a member can't be attached to the same eschool multiple times
            $table->unique(['eschool_id', 'member_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eschool_member');
    }
};
