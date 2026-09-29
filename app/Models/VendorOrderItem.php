<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorOrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_order_id',
        'product_id',
        'variant_id',
        'price',
        'quantity',
        'total',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'total' => 'decimal:2',
    ];

    public function vendorOrder()
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
