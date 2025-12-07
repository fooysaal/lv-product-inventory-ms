<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'category_id',
        'unit_id',
        'cost_price',
        'selling_price',
        'min_stock_level',
        'max_stock_level',
        'barcode',
        'image',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the unit that owns the product.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the stock ins for the product.
     */
    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    /**
     * Get the stock outs for the product.
     */
    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class);
    }

    /**
     * Get the stock balances for the product.
     */
    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    /**
     * Get total stock across all warehouses.
     */
    public function getTotalStockAttribute(): float
    {
        return $this->stockBalances()->sum('quantity');
    }

    /**
     * Get total available stock across all warehouses.
     */
    public function getTotalAvailableStockAttribute(): float
    {
        return $this->stockBalances()->sum('available_quantity');
    }

    /**
     * Check if product is low stock.
     */
    public function isLowStock(): bool
    {
        return $this->total_available_stock < $this->min_stock_level;
    }

    /**
     * Check if product is overstock.
     */
    public function isOverstock(): bool
    {
        if (is_null($this->max_stock_level)) {
            return false;
        }

        return $this->total_stock > $this->max_stock_level;
    }

    /**
     * Get profit margin.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price == 0) {
            return 0;
        }

        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * Get stock balance for a specific warehouse.
     */
    public function getWarehouseStock(int $warehouseId): ?StockBalance
    {
        return $this->stockBalances()->where('warehouse_id', $warehouseId)->first();
    }
}
