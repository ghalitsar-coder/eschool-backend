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
        ];
    }
    
    // Eschool relationships
    public function coordinatedEschool()
    {
        return $this->hasOne(Eschool::class, 'coordinator_id');
    }

    public function treasurerEschool()
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
}
