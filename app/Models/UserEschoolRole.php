<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEschoolRole extends Model
{
    protected $fillable = [
        'user_id',
        'eschool_id', 
        'role',
        'assigned_at',
        'status',
        'notes'
    ];
    
    protected $casts = [
        'assigned_at' => 'datetime',
    ];
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function eschool(): BelongsTo
    {
        return $this->belongsTo(Eschool::class);
    }
    
    // Helper methods
    public function getPermissions(): array
    {
        return match($this->role) {
            'koordinator' => ['manage_eschool', 'manage_members', 'manage_attendance', 'view_all_reports', 'assign_roles'],
            'bendahara' => ['manage_kas', 'view_all_kas', 'approve_transactions', 'view_kas_reports', 'view_members'],
            'member' => ['view_own_kas', 'view_attendance', 'view_own_profile', 'submit_attendance'],
            default => []
        };
    }
    
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }
    
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
    
    public function scopeByEschool($query, int $eschoolId)
    {
        return $query->where('eschool_id', $eschoolId);
    }
}
