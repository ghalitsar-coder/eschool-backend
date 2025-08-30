<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eschool extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'coordinator_id',
        'treasurer_id',
        'name',
        'description',
        'monthly_kas_amount',
        'schedule_days',
        'total_schedule_days',
        'is_active',
    ];

    protected $casts = [
        'schedule_days' => 'array',
        'is_active' => 'boolean',
    ];

    // Define relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Coordinator relationship (a koordinator user)
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    // Treasurer relationship (a bendahara user)
    public function treasurer()
    {
        return $this->belongsTo(User::class, 'treasurer_id');
    }

    // Define many-to-many relationship with Member
    public function members()
    {
        return $this->belongsToMany(Member::class, 'eschool_member');
    }

    public function kasRecords()
    {
        return $this->hasMany(KasRecord::class);
    }

    public function kasPayments()
    {
        return $this->hasMany(KasPayment::class);
    }
    
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
