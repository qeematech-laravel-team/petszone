<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRelation extends Model
{
    protected $fillable = [
        'product_id',
        'related_product_id',
        'type',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function relatedProduct()
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }

    /**
     * Scope a query to only include related products
     */
    public function scopeRelated($query)
    {
        return $query->where('type', 'related');
    }

    /**
     * Scope a query to only include upsell products
     */
    public function scopeUpsell($query)
    {
        return $query->where('type', 'upsell');
    }

    /**
     * Scope a query to only include cross-sell products
     */
    public function scopeCrossSell($query)
    {
        return $query->where('type', 'cross_sell');
    }

    /**
     * Scope a query by relation type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
