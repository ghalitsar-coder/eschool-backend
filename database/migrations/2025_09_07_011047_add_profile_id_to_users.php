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
            // Tambahkan kolom profile_id sebagai foreign key
            $table->unsignedBigInteger('profile_id')->nullable()->after('id');
            
            // Tambahkan foreign key constraint
            $table->foreign('profile_id')
                  ->references('id')
                  ->on('profiles')
                  ->onDelete('set null'); // atau 'cascade' sesuai kebutuhan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key constraint dulu
            $table->dropForeign(['profile_id']);
            
            // Hapus kolom profile_id
            $table->dropColumn('profile_id');
        });
    }
};
