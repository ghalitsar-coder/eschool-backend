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
        // Tidak ada perubahan yang diperlukan saat migrasi ke depan
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Karena ada masalah dengan constraint, kita tidak perlu melakukan apa pun saat rollback
        // Migration refresh akan menggunakan migrate:fresh yang lebih aman
    }
};
