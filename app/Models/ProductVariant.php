<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductVariant extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'name',
        'product_id',
        'slug',
        'sku',
        'price',
        'is_active',
        'thumbnail',
    ];

    protected $casts = [
        'name' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variation) {
            // Check if product type is variable (not simple)
            if ($variation->product->type !== 'variable') {
                throw new \Exception('Cannot add variations to a simple product.');
            }
        });
    }

    public $translatable = ['name'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }

    public function options()
    {
        return $this->hasMany(VariantOption::class);
    }

    public function images()
    {
        return $this->morphMany(ProductImage::class, 'imageable');
    }

    public function branchVariantStocks()
    {
        return $this->hasMany(BranchProductVariantStock::class);
    }

    /**
     * Scope a query to only include active variants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if variant is active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Get total stock across all branches
     */
    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('branchVariantStocks')) {
            return (int) $this->branchVariantStocks->sum('quantity');
        }

        return (int) $this->branchVariantStocks()->sum('quantity');
    }

    /**
     * Check if variant has stock
     */
    public function hasStock(): bool
    {
        return $this->total_stock > 0;
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/'.$value)
                : asset('dashboard/images/product_image.jpg')
        );
    }
}
