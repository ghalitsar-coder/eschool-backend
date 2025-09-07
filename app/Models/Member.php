<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\School;
use App\Models\Eschool;

class Member extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'school_id',
        'student_id',
        'grade_level',
    ];

    /**
     * Get the user that owns the member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the school that owns the member.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * The eschools that belong to the member.
     */
    public function eschools()
    {
        return $this->belongsToMany(Eschool::class, 'user_eschool_roles', 'user_id', 'eschool_id')
            ->withPivot('role')
            ->withTimestamps();
    }
}