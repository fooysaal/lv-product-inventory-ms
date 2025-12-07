<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_type_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user type that owns the user.
     */
    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    /**
     * Get the warehouses managed by this user.
     */
    public function managedWarehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'manager_id');
    }

    /**
     * Get the stock ins created by this user.
     */
    public function createdStockIns(): HasMany
    {
        return $this->hasMany(StockIn::class, 'created_by');
    }

    /**
     * Get the stock ins approved by this user.
     */
    public function approvedStockIns(): HasMany
    {
        return $this->hasMany(StockIn::class, 'approved_by');
    }

    /**
     * Get the stock outs created by this user.
     */
    public function createdStockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class, 'created_by');
    }

    /**
     * Get the stock outs approved by this user.
     */
    public function approvedStockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class, 'approved_by');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->userType->slug === 'admin';
    }

    /**
     * Check if user is stock manager.
     */
    public function isStockManager(): bool
    {
        return $this->userType->slug === 'stock_manager';
    }

    /**
     * Check if user is stock executive.
     */
    public function isStockExecutive(): bool
    {
        return $this->userType->slug === 'stock_executive';
    }
}
