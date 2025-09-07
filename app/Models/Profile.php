<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Staff;

class Profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'date_of_birth',
        'gender',
        'address',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user associated with the profile.
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * Get the teacher associated with the profile.
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Get the student associated with the profile.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the staff associated with the profile.
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }
}