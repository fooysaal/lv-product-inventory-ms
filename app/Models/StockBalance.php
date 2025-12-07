<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'last_updated',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($balance) {
            $balance->available_quantity = $balance->quantity - $balance->reserved_quantity;
            $balance->last_updated = now();
        });
    }

    /**
     * Get the product that owns the stock balance.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the stock balance.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Scope a query to only include low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->whereColumn('stock_balances.available_quantity', '<', 'products.min_stock_level');
        });
    }

    /**
     * Scope a query to only include out of stock items.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('available_quantity', '<=', 0);
    }

    /**
     * Check if product is low stock.
     */
    public function isLowStock(): bool
    {
        return $this->available_quantity < $this->product->min_stock_level;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->available_quantity <= 0;
    }

    /**
     * Check if product is overstock.
     */
    public function isOverstock(): bool
    {
        if (is_null($this->product->max_stock_level)) {
            return false;
        }

        return $this->quantity > $this->product->max_stock_level;
    }

    /**
     * Reserve quantity for pending orders.
     */
    public function reserve(float $quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->reserved_quantity += $quantity;
        $this->save();

        return true;
    }

    /**
     * Release reserved quantity.
     */
    public function release(float $quantity): bool
    {
        if ($this->reserved_quantity < $quantity) {
            return false;
        }

        $this->reserved_quantity -= $quantity;
        $this->save();

        return true;
    }

    /**
     * Get stock status.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }

        if ($this->isLowStock()) {
            return 'Low Stock';
        }

        if ($this->isOverstock()) {
            return 'Overstock';
        }

        return 'In Stock';
    }
}
