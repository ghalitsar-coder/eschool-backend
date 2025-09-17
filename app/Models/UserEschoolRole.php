<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Eschool;
use App\Models\AttendanceRecord;
use App\Models\KasPayment;

class UserEschoolRole extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'eschool_id',
        'role',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role' => 'string',
    ];

    /**
     * Get the user that owns the user eschool role.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the eschool that owns the user eschool role.
     */
    public function eschool()
    {
        return $this->belongsTo(Eschool::class);
    }

    /**
     * Get the attendance records for the user eschool role.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the kas payments for the user eschool role.
     */
    public function kasPayments()
    {
        return $this->hasMany(KasPayment::class);
    }
}