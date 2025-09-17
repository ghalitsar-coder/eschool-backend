<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_record', function (Blueprint $table) {
            // Add date column (required for attendance tracking)
            if (!Schema::hasColumn('attendance_record', 'date')) {
                $table->date('date')->after('user_eschool_role_id');
            }
           
            // Add proof_document column (nullable, for storing file paths)
            if (!Schema::hasColumn('attendance_record', 'proof_document')) {
                $table->string('proof_document', 255)->nullable()->after('notes');
            }
        });

        // Add index
        try {
            DB::statement('ALTER TABLE attendance_record ADD INDEX idx_attendance_date (date)');
        } catch (\Exception $e) {
            // Index sudah ada, skip
        }

        // Add unique constraint
        try {
            DB::statement('ALTER TABLE attendance_record ADD CONSTRAINT unique_user_eschool_role_date UNIQUE (user_eschool_role_id, date)');
        } catch (\Exception $e) {
            // Constraint sudah ada, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks sementara
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Drop constraints dan indexes
        try {
            DB::statement('ALTER TABLE attendance_record DROP INDEX unique_user_eschool_role_date');
        } catch (\Exception $e) {
            // Sudah tidak ada, skip
        }

        try {
            DB::statement('ALTER TABLE attendance_record DROP INDEX idx_attendance_date');
        } catch (\Exception $e) {
            // Sudah tidak ada, skip
        }

        // Enable foreign key checks kembali
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // Drop columns
        Schema::table('attendance_record', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_record', 'date')) {
                $table->dropColumn('date');
            }
            if (Schema::hasColumn('attendance_record', 'proof_document')) {
                $table->dropColumn('proof_document');
            }
        });
    }
};