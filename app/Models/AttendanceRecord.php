<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserEschoolRole;

class AttendanceRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_record';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_eschool_role_id',
        'date',
        'recorder_id',
        'is_present',
        'notes',
        'proof_document',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'is_present' => 'boolean',
    ];

    /**
     * Get the user eschool role that owns the attendance record.
     */
    public function userEschoolRole()
    {
        return $this->belongsTo(UserEschoolRole::class);
    }

    /**
     * Get the user who recorded this attendance.
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorder_id');
    }

    /**
     * Get the user through the userEschoolRole relationship.
     */
    public function getUserAttribute()
    {
        return $this->userEschoolRole?->user;
    }

    /**
     * Get the eschool through the userEschoolRole relationship.
     */
    public function getEschoolAttribute()
    {
        return $this->userEschoolRole?->eschool;
    }

    /**
     * Get the proof document URL if it exists.
     */
    public function getProofDocumentUrlAttribute()
    {
        return $this->proof_document ? asset('storage/' . $this->proof_document) : null;
    }
}