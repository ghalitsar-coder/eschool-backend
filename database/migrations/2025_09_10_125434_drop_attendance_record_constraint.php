<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus constraint dan index dengan cara yang aman
        try {
            DB::statement('ALTER TABLE attendance_record DROP FOREIGN KEY attendance_record_user_eschool_role_id_foreign');
        } catch (\Exception $e) {
            // Constraint mungkin tidak ada, abaikan error
        }
        
        try {
            DB::statement('ALTER TABLE attendance_record DROP INDEX unique_user_eschool_role_date');
        } catch (\Exception $e) {
            // Index mungkin tidak ada, abaikan error
        }
        
        try {
            DB::statement('ALTER TABLE attendance_record DROP INDEX idx_attendance_date');
        } catch (\Exception $e) {
            // Index mungkin tidak ada, abaikan error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu melakukan apa pun saat rollback
    }
};
