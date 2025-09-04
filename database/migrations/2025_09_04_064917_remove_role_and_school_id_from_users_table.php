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
            // Hapus foreign key constraint dulu sebelum drop kolom
            $table->dropForeign(['school_id']);
            
            // Hapus kolom role dan school_id untuk sistem multi-role murni
            $table->dropColumn(['role', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan kolom role dan school_id jika rollback
            $table->enum('role', ['koordinator', 'bendahara', 'siswa', 'staff'])->after('base_role');
            $table->unsignedBigInteger('school_id')->nullable()->after('role');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
        });
    }
};
