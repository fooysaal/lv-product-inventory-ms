<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIn extends Model
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
        'supplier_name',
        'supplier_invoice',
        'received_date',
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
        'received_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stockIn) {
            if (empty($stockIn->reference_number)) {
                $stockIn->reference_number = 'SI-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
            if (empty($stockIn->total_amount)) {
                $stockIn->total_amount = $stockIn->quantity * $stockIn->unit_price;
            }
        });
    }

    /**
     * Get the product that owns the stock in.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the stock in.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who created the stock in.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the stock in.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending stock ins.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved stock ins.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected stock ins.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if stock in is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if stock in is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if stock in is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the stock in.
     */
    public function approve(int $approvedBy): bool
    {
        if (!$this->isPending()) {
            return false;
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
     * Reject the stock in.
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
        $balance = StockBalance::firstOrCreate(
            [
                'product_id' => $this->product_id,
                'warehouse_id' => $this->warehouse_id,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
            ]
        );

        $balance->quantity += $this->quantity;
        $balance->available_quantity = $balance->quantity - $balance->reserved_quantity;
        $balance->last_updated = now();
        $balance->save();
    }
}
