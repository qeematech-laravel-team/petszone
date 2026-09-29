<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'sub_total',
        'order_discount',
        'coupon_id',
        'coupon_discount',
        'total_shipping',
        'points_discount',
        'total',
        'wallet_used',
        'status',
        'payment_status',
        'payment_method',
        'fawry_merchant_ref_num',
        'fawry_ref_number',
        'fawry_payment_reference_number',
        'geidea_payment_intent_id',
        'geidea_order_id',
        'notes',
        'address_id',
        'total_commission',
        'refund_status',
        'refunded_total',
        'paid_at',
        'vendor_balance_processed_at',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'order_discount' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'total_shipping' => 'decimal:2',
        'points_discount' => 'decimal:2',
        'total' => 'decimal:2',
        'wallet_used' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'refunded_total' => 'decimal:2',
        'paid_at' => 'datetime',
        'vendor_balance_processed_at' => 'datetime',
    ];

    /**
     * Remaining amount the customer must pay via a gateway
     * (order total minus wallet balance and loyalty points already applied).
     */
    public function getPayableAmountAttribute(): float
    {
        $payable = (float) $this->total - (float) $this->wallet_used - (float) $this->points_discount;

        return round(max(0, $payable), 2);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function vendorOrders()
    {
        return $this->hasMany(VendorOrder::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrderLog::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(OrderRefundRequest::class);
    }

    /**
     * Get all vendor order items that belong to this order.
     */
    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(
            VendorOrderItem::class,
            VendorOrder::class,
            'order_id',        // Foreign key on vendor_orders table...
            'vendor_order_id', // Foreign key on vendor_order_items table...
            'id',              // Local key on orders table...
            'id'               // Local key on vendor_orders table...
        );
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

    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaymentPaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePaymentFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    public function scopePaymentRefunded($query)
    {
        return $query->where('payment_status', 'refunded');
    }
}
