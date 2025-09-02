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
        Schema::table('attendance_records', function (Blueprint $table) {
            // Menambahkan kolom untuk menyimpan path dokumen bukti ketidakhadiran
            $table->string('proof_document_path')->nullable()->after('notes');
            $table->string('proof_document_name')->nullable()->after('proof_document_path');
            $table->string('proof_document_type')->nullable()->after('proof_document_name');
            $table->integer('proof_document_size')->nullable()->after('proof_document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['proof_document_path', 'proof_document_name', 'proof_document_type', 'proof_document_size']);
        });
    }
};