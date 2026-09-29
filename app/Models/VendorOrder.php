<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyRelation;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'branch_id',
        'sub_total',
        'discount',
        'shipping_cost',
        'total',
        'status',
        'notes',
        'commission',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the items associated with this vendor order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(VendorOrderItem::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function logs(): HasManyRelation
    {
        return $this->hasMany(OrderLog::class);
    }
}
