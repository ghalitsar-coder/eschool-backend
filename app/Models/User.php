<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Profile;
use App\Models\Student;
use App\Models\UserEschoolRole;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profile_id',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the profile associated with the user.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the user eschool roles for the user.
     */
    public function userEschoolRoles()
    {
        return $this->hasMany(UserEschoolRole::class);
    }

    /**
     * Get the student record through the profile.
     */
    public function student()
    {
        return $this->hasOneThrough(Student::class, Profile::class, 'id', 'profile_id', 'profile_id', 'id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

     /**
     * Check if user has specific role(s)
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        // Ensure userEschoolRoles relation is loaded
        if (!$this->relationLoaded('userEschoolRoles')) {
            $this->load('userEschoolRoles');
        }
        
        // Get user's roles from user_eschool_roles table
        $userRoles = $this->userEschoolRoles->pluck('role')->toArray();
        
        if (is_array($roles)) {
            // Check if user has any of the specified roles
            return !empty(array_intersect($roles, $userRoles));
        }
        
        // Check if user has the specific role
        return in_array($roles, $userRoles);
    }
}
