<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'school_id',
        'base_role', // Add base_role field
        'is_system_admin', // Add is_system_admin field
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'is_system_admin' => 'boolean', // Cast is_system_admin as boolean
        ];
    }

    // Scope untuk masing-masing role
    public function scopeSiswa($query)
    {
        return $query->where('role', 'siswa');
    }

    public function scopeBendahara($query)
    {
        return $query->where('role', 'bendahara');
    }

    public function scopeKoordinator($query)
    {
        return $query->where('role', 'koordinator');
    }

    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }

    // Method checker untuk masing-masing role
    public function isSiswa()
    {
        return $this->role === 'siswa';
    }

    public function isBendahara()
    {
        return $this->role === 'bendahara';
    }

    public function isKoordinator()
    {
        return $this->role === 'koordinator';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    // Method untuk check multiple roles
    public function hasRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        
        return $this->role === $roles;
    }

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'email' => $this->email,
            'school_id' => $this->school_id,
        ];
    }
    
    // Eschool relationships
    public function coordinatedEschool()
    {
        return $this->hasOne(Eschool::class, 'coordinator_id');
    }

    public function treasuredEschool()
    {
        return $this->hasOne(Eschool::class, 'treasurer_id');
    }
    
    // School relationship (for staff)
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    
    // Member relationship (if user is a member)
    public function member()
    {
        return $this->hasOne(Member::class);
    }
    
    // Multiple eschools where user is a member
    public function memberEschools()
    {
        return $this->hasManyThrough(Eschool::class, Member::class, 'user_id', 'id', 'id', 'school_id')
                   ->join('eschool_member', 'eschools.id', '=', 'eschool_member.eschool_id')
                   ->join('members', 'eschool_member.member_id', '=', 'members.id')
                   ->where('members.user_id', '=', $this->id);
    }
    
    // New multi-role relationships
    public function eschoolRoles()
    {
        return $this->hasMany(UserEschoolRole::class);
    }
    
    /**
     * Check if user has a specific role in a specific eschool
     * Used by the eschool.role middleware
     */
    public function hasRoleInEschool($eschoolId, $roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        // Check if user has any of the specified roles in this eschool
        return $this->eschoolRoles()
            ->where('eschool_id', $eschoolId)
            ->whereIn('role', $roles)
            ->exists();
    }
    
    /**
     * Get all eschools data with roles and permissions for this user
     * This method is used in AuthController for login and refresh
     */
    public function getEschoolsData()
    {
        $eschoolRoles = $this->eschoolRoles()->with(['eschool:id,name,description'])->get();
        
        return $eschoolRoles->map(function ($role) {
            return [
                'eschool_id' => $role->eschool_id,
                'eschool_name' => $role->eschool->name ?? 'Unknown',
                'eschool_description' => $role->eschool->description ?? '',
                'role_in_eschool' => $role->role,
                'role_status' => $role->status,
                'permissions' => $role->getPermissions(),
                'assigned_at' => $role->assigned_at,
            ];
        })->toArray();
    }
}
