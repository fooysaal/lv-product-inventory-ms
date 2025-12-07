<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;

class StockOut extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_number',
        'product_id',
        'warehouse_id',
        'quantity',
        'unit_price',
        'total_amount',
        'customer_name',
        'order_number',
        'issued_date',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issued_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stockOut) {
            if (empty($stockOut->reference_number)) {
                $stockOut->reference_number = 'SO-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
            if (empty($stockOut->total_amount)) {
                $stockOut->total_amount = $stockOut->quantity * $stockOut->unit_price;
            }
        });
    }

    /**
     * Get the product that owns the stock out.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the stock out.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who created the stock out.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the stock out.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending stock outs.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved stock outs.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected stock outs.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if stock out is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if stock out is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if stock out is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the stock out.
     */
    public function approve(int $approvedBy): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        // Check if sufficient stock is available
        $balance = StockBalance::where('product_id', $this->product_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->first();

        if (!$balance || $balance->available_quantity < $this->quantity) {
            throw new Exception('Insufficient stock available for this product in the warehouse.');
        }

        $this->status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approved_at = now();
        $this->save();

        // Update stock balance
        $this->updateStockBalance();

        return true;
    }

    /**
     * Reject the stock out.
     */
    public function reject(int $rejectedBy, string $reason): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = 'rejected';
        $this->approved_by = $rejectedBy;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        $this->save();

        return true;
    }

    /**
     * Update stock balance after approval.
     */
    protected function updateStockBalance(): void
    {
        $balance = StockBalance::where('product_id', $this->product_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->first();

        if ($balance) {
            $balance->quantity -= $this->quantity;
            $balance->available_quantity = $balance->quantity - $balance->reserved_quantity;
            $balance->last_updated = now();
            $balance->save();
        }
    }
}
