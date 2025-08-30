<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
        'nip',
        'name',
        'student_id',
        'date_of_birth',
        'gender',
        'address',
        'phone',
        'email',
        'position',
        'status',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    // Automatically sync name from user when saving
    public static function boot()
    {
        parent::boot();

        // When creating or updating a member, sync the name from the user
        static::creating(function ($member) {
            if ($member->user_id && empty($member->name)) {
                $user = User::find($member->user_id);
                if ($user) {
                    $member->name = $user->name;
                }
            }
        });

        static::updating(function ($member) {
            if ($member->isDirty('user_id') && $member->user_id) {
                $user = User::find($member->user_id);
                if ($user) {
                    $member->name = $user->name;
                }
            }
        });
    }

    // Define relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Define many-to-many relationship with Eschool
    public function eschools()
    {
        return $this->belongsToMany(Eschool::class, 'eschool_member');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
