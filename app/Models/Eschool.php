<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\School;
use App\Models\UserEschoolRole;
use App\Models\AttendanceRecord;
use App\Models\KasRecord;

class Eschool extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'schedule_days',
        'description',
        'is_active',
        'monthly_fee_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'monthly_fee_amount' => 'decimal:2',
    ];

    /**
     * Get the school that owns the eschool.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the user eschool roles for the eschool.
     */
    public function userEschoolRoles()
    {
        return $this->hasMany(UserEschoolRole::class);
    }

    /**
     * Get the attendance records for the eschool.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the kas records for the eschool.
     */
    public function kasRecords()
    {
        return $this->hasMany(KasRecord::class);
    }
}