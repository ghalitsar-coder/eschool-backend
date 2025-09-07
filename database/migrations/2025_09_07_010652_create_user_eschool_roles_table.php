<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
    {
        Schema::create('user_eschool_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('eschool_id')->nullable(); // Nullable for supervisor role
            $table->enum('role', ['supervisor', 'coordinator', 'treasurer', 'member']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('eschool_id')->references('id')->on('eschools')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_eschool_roles');
    }
};
