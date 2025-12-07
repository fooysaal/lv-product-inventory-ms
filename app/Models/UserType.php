<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users for the user type.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if user type has a specific permission.
     */
    public function hasPermission(string $module, string $action): bool
    {
        $permissions = $this->permissions ?? [];

        if (!isset($permissions[$module])) {
            return false;
        }

        return in_array($action, $permissions[$module]);
    }

    /**
     * Get all permissions for a module.
     */
    public function getModulePermissions(string $module): array
    {
        return $this->permissions[$module] ?? [];
    }
}
