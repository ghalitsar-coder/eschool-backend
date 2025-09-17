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
        Schema::create('kas_record', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('eschool_id');
            $table->text('description');
            $table->string('category');
            $table->decimal('amount', 8, 2);
            $table->date('date');
            $table->unsignedBigInteger('recorder_id');
            $table->timestamps();

            $table->foreign('eschool_id')->references('id')->on('eschools')->onDelete('cascade');
            $table->foreign('recorder_id')->references('id')->on('user_eschool_roles')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kas_record');
    }
};
