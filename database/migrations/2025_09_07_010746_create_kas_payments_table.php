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
        Schema::create('kas_payment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kas_record_id');
            $table->unsignedBigInteger('member_id');
            $table->decimal('amount', 8, 2);
            $table->string('month');
            $table->integer('year');
            $table->boolean('is_paid')->default(false);
            $table->date('paid_date')->nullable();
            $table->timestamps();

            $table->foreign('kas_record_id')->references('id')->on('kas_record')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('user_eschool_roles')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kas_payment');
    }
};
