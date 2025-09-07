<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Eschool;

class School extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
    ];

    /**
     * Get the staff for the school.
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get the teachers for the school.
     */
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Get the students for the school.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the eschools for the school.
     */
    public function eschools()
    {
        return $this->hasMany(Eschool::class);
    }
}