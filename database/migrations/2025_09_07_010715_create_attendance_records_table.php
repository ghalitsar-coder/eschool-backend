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
        Schema::create('attendance_record', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_eschool_role_id');
            $table->boolean('is_present')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_eschool_role_id')->references('id')->on('user_eschool_roles')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_record');
    }
};
