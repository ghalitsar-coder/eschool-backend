<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory ;

    protected $fillable = [
        'eschool_id',
        'member_id',
        'recorder_id',
        'date',
        'is_present',
        'notes'
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_present' => 'boolean'
    ];

    /**
     * Get the eschool that owns the attendance record.
     */
    public function eschool(): BelongsTo
    {
        return $this->belongsTo(Eschool::class);
    }

    /**
     * Get the member that owns the attendance record.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the user who recorded the attendance.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorder_id');
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by eschool.
     */
    public function scopeByEschool($query, $eschoolId)
    {
        return $query->where('eschool_id', $eschoolId);
    }

    /**
     * Scope for filtering by member.
     */
    public function scopeByMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Scope for present members only.
     */
    public function scopePresent($query)
    {
        return $query->where('is_present', true);
    }

    /**
     * Scope for absent members only.
     */
    public function scopeAbsent($query)
    {
        return $query->where('is_present', false);
    }
}