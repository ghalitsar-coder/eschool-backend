<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role',
        'permission',
        'description',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
    
    // Helper methods
    public static function getPermissionsForRole(string $role): array
    {
        return static::where('role', $role)
                    ->where('is_active', true)
                    ->pluck('permission')
                    ->toArray();
    }
}
