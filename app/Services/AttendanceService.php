<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Member;
use App\Models\Eschool;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

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
            // Ensure member_id is properly cast
            $memberId = is_string($memberData['member_id']) ? (int)$memberData['member_id'] : $memberData['member_id'];
            
            // Check if member is associated with the eschool using many-to-many relationship
            $member = $eschool->members()->where('members.id', $memberId)->first();
            
            if (!$member) {
                $errors[] = "Member with ID {$memberId} is not associated with this eschool.";
                continue;
            }

            // Check if attendance already exists for this date
            $existingRecord = AttendanceRecord::where('eschool_id', $eschoolId)
                                            ->where('member_id', $memberId)
                                            ->whereDate('date', $date->toDateString())
                                            ->first();

            if ($existingRecord) {
                // Kumpulkan error, jangan langsung throw
                $name = $member->name ?? $member->student_id ?? "ID {$member->id}";
                $errors[] = "Member {$name} sudah absen pada tanggal {$date->toDateString()}";
                continue;
            }

            // Handle proof document upload if member is absent
            $proofDocumentData = null;
            if (isset($memberData['proof_document']) && $memberData['proof_document'] instanceof UploadedFile) {
                $proofDocumentData = $this->uploadProofDocument($memberData['proof_document'], $eschoolId, $memberId, $date);
            }

            // Create new record
            $record = AttendanceRecord::create([
                'eschool_id' => $eschoolId,
                'member_id' => $memberId,
                'recorder_id' => $recorderId,
                'date' => $date,
                'is_present' => isset($memberData['is_present']) ? (bool)$memberData['is_present'] : false,
                'notes' => $memberData['notes'] ?? null,
                'proof_document_path' => $proofDocumentData['path'] ?? null,
                'proof_document_name' => $proofDocumentData['name'] ?? null,
                'proof_document_type' => $proofDocumentData['type'] ?? null,
                'proof_document_size' => $proofDocumentData['size'] ?? null,
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
     * Update attendance record.
     */
    public function updateAttendance(AttendanceRecord $attendanceRecord, array $attendanceData): AttendanceRecord
    {
        // Handle proof document upload if provided
        $proofDocumentData = null;
        if (isset($attendanceData['proof_document']) && $attendanceData['proof_document'] instanceof UploadedFile) {
            // Delete old document if exists
            if ($attendanceRecord->proof_document_path) {
                Storage::disk('public')->delete($attendanceRecord->proof_document_path);
            }
            
            $proofDocumentData = $this->uploadProofDocument(
                $attendanceData['proof_document'], 
                $attendanceRecord->eschool_id, 
                $attendanceRecord->member_id, 
                Carbon::parse($attendanceRecord->date)
            );
        }

        // Update record with new data
        $updateData = [
            'is_present' => isset($attendanceData['is_present']) ? (bool)$attendanceData['is_present'] : $attendanceRecord->is_present,
            'notes' => $attendanceData['notes'] ?? $attendanceRecord->notes,
        ];

        // Add proof document data if uploaded
        if ($proofDocumentData) {
            $updateData['proof_document_path'] = $proofDocumentData['path'];
            $updateData['proof_document_name'] = $proofDocumentData['name'];
            $updateData['proof_document_type'] = $proofDocumentData['type'];
            $updateData['proof_document_size'] = $proofDocumentData['size'];
        }

        $attendanceRecord->update($updateData);

        return $attendanceRecord;
    }

    /**
     * Upload proof document for absent member.
     */
    private function uploadProofDocument(UploadedFile $file, string $eschoolId, string $memberId, Carbon $date): array
    {
        // Validate file type
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file->getClientMimeType(), $allowedTypes)) {
            throw new \Exception('Invalid file type. Only PDF, JPEG, JPG, and PNG files are allowed.');
        }

        // Validate file size (max 5MB)
        if ($file->getSize() > 5242880) { // 5MB in bytes
            throw new \Exception('File size exceeds 5MB limit.');
        }

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = 'proof_' . $eschoolId . '_' . $memberId . '_' . $date->format('Y_m_d') . '_' . time() . '.' . $extension;

        // Define storage path
        $path = 'attendance_proofs/' . $eschoolId . '/' . $date->format('Y/m');

        // Store file
        $storedPath = $file->storeAs($path, $filename, 'public');

        return [
            'path' => $storedPath,
            'name' => $filename,
            'type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Delete proof document.
     */
    public function deleteProofDocument(AttendanceRecord $attendanceRecord): bool
    {
        if ($attendanceRecord->proof_document_path) {
            Storage::disk('public')->delete($attendanceRecord->proof_document_path);
            
            $attendanceRecord->update([
                'proof_document_path' => null,
                'proof_document_name' => null,
                'proof_document_type' => null,
                'proof_document_size' => null,
            ]);
            
            return true;
        }
        
        return false;
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
        $eschool = Eschool::findOrFail($eschoolId); // To ensure eschool exists
        $totalMembers = $eschool->members()->count();
        
        $attendanceRecords = AttendanceRecord::byEschool($eschoolId)
                                           ->byDateRange($startDate, $endDate)
                                           ->get();
        
        $totalPresent = $attendanceRecords->where('is_present', true)->count();
        $totalAbsent = $attendanceRecords->where('is_present', false)->count();
        $totalRecords = $attendanceRecords->count();
        
        $attendanceRate = $totalRecords > 0 ? ($totalPresent / $totalRecords) * 100 : 0;
        
        return [
            'total_members' => $totalMembers,
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'total_records' => $totalRecords,
            'attendance_rate' => round($attendanceRate, 2),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ];
    }

    /**
     * Get daily attendance summary for a date range.
     */
    public function getDailyAttendanceSummary(string $eschoolId, string $startDate, string $endDate): Collection
    {
        return AttendanceRecord::selectRaw('
                DATE(date) as attendance_date,
                COUNT(*) as total_records,
                SUM(CASE WHEN is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN is_present = 0 THEN 1 ELSE 0 END) as absent_count
            ')
            ->where('eschool_id', $eschoolId)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();
    }

    /**
     * Get member attendance history for a date range.
     */
    public function getMemberAttendanceHistory(string $memberId, string $startDate, string $endDate): Collection
    {
        return AttendanceRecord::with(['eschool', 'recorder'])
            ->where('member_id', $memberId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Delete all attendance records for a specific date and eschool.
     */
    public function deleteAttendanceByDate(string $eschoolId, string $date): int
    {
        $deletedCount = AttendanceRecord::where('eschool_id', $eschoolId)
            ->whereDate('date', $date)
            ->count();

        // Delete the records
        AttendanceRecord::where('eschool_id', $eschoolId)
            ->whereDate('date', $date)
            ->delete();

        return $deletedCount;
    }
}