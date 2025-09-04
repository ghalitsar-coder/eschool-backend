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
        Schema::table('user_eschool_roles', function (Blueprint $table) {
            // Tambahkan kolom school_id untuk memudahkan query berdasarkan sekolah
            $table->unsignedBigInteger('school_id')->nullable()->after('eschool_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            
            // Index untuk performa query
            $table->index(['user_id', 'school_id']);
            $table->index(['school_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_eschool_roles', function (Blueprint $table) {
            // Hapus foreign key dan index terlebih dahulu
            $table->dropForeign(['school_id']);
            $table->dropIndex(['user_id', 'school_id']);
            $table->dropIndex(['school_id', 'role']);
            $table->dropColumn('school_id');
        });
    }
};
