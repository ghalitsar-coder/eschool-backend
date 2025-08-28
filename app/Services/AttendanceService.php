<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Member;
use App\Models\Eschool;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AttendanceService
{
    /**
     * Record attendance for multiple members.
     */
  public function recordBatchAttendance(string $eschoolId, array $attendanceData, string $recorderId): Collection
{
    $eschool = Eschool::findOrFail($eschoolId);
    $date = Carbon::parse($attendanceData['date'] ?? now());

    $records = collect();
    $errors = [];

    foreach ($attendanceData['members'] as $memberData) {
        $member = Member::where('eschool_id', $eschoolId)
                        ->findOrFail($memberData['member_id']);

        // Check if attendance already exists for this date
        $existingRecord = AttendanceRecord::where('eschool_id', $eschoolId)
                                        ->where('member_id', $memberData['member_id'])
                                        ->whereDate('date', $date->toDateString())
                                        ->first();

        if ($existingRecord) {
            // Kumpulkan error, jangan langsung throw
            $name = $member->name ?? $member->student_id ?? "ID {$member->id}";
            $errors[] = "Member {$name} sudah absen pada tanggal {$date->toDateString()}";
            continue;
        }

        // Create new record
        $record = AttendanceRecord::create([
            'eschool_id' => $eschoolId,
            'member_id' => $memberData['member_id'],
            'recorder_id' => $recorderId,
            'date' => $date,
            'is_present' => $memberData['is_present'] ?? false,
            'notes' => $memberData['notes'] ?? null
        ]);

        $records->push($record);
    }

    // Kalau ada error, lempar exception dengan gabungan semua pesan
   if (!empty($errors)) {
    throw new \Exception(json_encode($errors));
}

    return AttendanceRecord::whereIn('id', $records->pluck('id'))->get();
}



    /**
     * Get attendance records for a specific date and eschool.
     */
    public function getAttendanceByDate(string $eschoolId, string $date): Collection
    {
        return AttendanceRecord::with(['member.user', 'recorder'])
                              ->byEschool($eschoolId)
                              ->whereDate('date', $date)
                              ->get();
    }

    /**
     * Get attendance statistics for a date range.
     */
    public function getAttendanceStatistics(string $eschoolId, string $startDate, string $endDate): array
    {
        $totalMembers = Member::where('eschool_id', $eschoolId)->count();
        
        $attendanceRecords = AttendanceRecord::byEschool($eschoolId)
                                           ->byDateRange($startDate, $endDate)
                                           ->get();
        
        $totalPresent = $attendanceRecords->where('is_present', true)->count();
        $totalAbsent = $attendanceRecords->where('is_present', false)->count();
        $totalRecords = $attendanceRecords->count();
        
        $attendanceRate = $totalRecords > 0 ? ($totalPresent / $totalRecords) * 100 : 0;
        
        return [
            'total_members' => $totalMembers,
            'total_records' => $totalRecords,
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'attendance_rate' => round($attendanceRate, 2)
        ];
    }

    /**
     * Get member attendance history.
     */
    public function getMemberAttendanceHistory(string $memberId, string $startDate = null, string $endDate = null): Collection
    {
        $query = AttendanceRecord::with(['recorder'])
                                ->byMember($memberId);
        
        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }
        
        return $query->orderBy('date', 'desc')->get();
    }

    /**
     * Get daily attendance summary for a date range.
     */
    public function getDailyAttendanceSummary(string $eschoolId, string $startDate, string $endDate): Collection
    {
        return AttendanceRecord::selectRaw('DATE(date) as attendance_date, COUNT(*) as total_records, SUM(is_present) as present_count')
                              ->byEschool($eschoolId)
                              ->byDateRange($startDate, $endDate)
                              ->groupBy('attendance_date')
                              ->orderBy('attendance_date', 'desc')
                              ->get();
    }

    /**
     * Check if attendance exists for a specific date and eschool.
     */
    public function hasAttendanceForDate(string $eschoolId, string $date): bool
    {
        return AttendanceRecord::byEschool($eschoolId)
                              ->whereDate('date', $date)
                              ->exists();
    }

    /**
     * Delete attendance records for a specific date.
     */
    public function deleteAttendanceByDate(string $eschoolId, string $date): int
    {
        return AttendanceRecord::byEschool($eschoolId)
                              ->whereDate('date', $date)
                              ->delete();
    }

    /**
     * Get members who haven't been recorded for attendance on a specific date.
     */
    public function getMembersWithoutAttendance(string $eschoolId, string $date): Collection
    {
        $recordedMemberIds = AttendanceRecord::byEschool($eschoolId)
                                           ->whereDate('date', $date)
                                           ->pluck('member_id');
        
        return Member::where('eschool_id', $eschoolId)
                    ->whereNotIn('id', $recordedMemberIds)
                    ->get();
    }
}