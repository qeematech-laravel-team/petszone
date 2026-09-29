<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchProductStock extends Model
{

    protected $table = 'branch_product_stocks';

    protected $fillable = [
        'branch_id',
        'product_id',
        'quantity',
        'price',
        'discount',
        'discount_type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
    ];


    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include in-stock items
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope a query to only include out-of-stock items
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Scope a query by branch
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope a query by product
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Check if stock is available
     */
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if stock is low (less than threshold)
     */
    public function isLowStock(int $threshold = 10): bool
    {
        return $this->quantity > 0 && $this->quantity <= $threshold;
    }

    /**
     * Calculate final price after discount
     */
    public function getFinalPriceAttribute(): float
    {
        $basePrice = $this->price ?? $this->product->price ?? 0;
        
        if (!$this->discount || $this->discount <= 0) {
            return (float) $basePrice;
        }

        if ($this->discount_type === 'percentage') {
            $discountedPrice = $basePrice - (($basePrice * $this->discount) / 100);
            return (float) max(0, $discountedPrice);
        }

        // Fixed discount
        return (float) max(0, $basePrice - $this->discount);
    }

    /**
     * Check if price has discount
     */
    public function hasDiscount(): bool
    {
        return $this->discount > 0;
    }

    /**
     * Get the price to use (branch price or product default)
     */
    public function getPriceToUseAttribute(): float
    {
        return $this->price ?? $this->product->price ?? 0;
    }
}
