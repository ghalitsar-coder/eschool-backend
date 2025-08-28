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
        Schema::create('kas_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eschool_id')->constrained('eschools')->onDelete('cascade');
            $table->foreignId('recorder_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['income', 'expense']);
            $table->integer('amount');
            $table->text('description');
            $table->timestamp('date');
            $table->timestamps();
        });     
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_records');
    }
};
