<?php

namespace App\Models;

use App\Factories\ProductPriceStockFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'type',
        'name',
        'description',
        'thumbnail',
        'sku',
        'slug',
        'price',
        'discount',
        'discount_type',
        'is_active',
        'is_featured',
        'is_new',
        'is_approved',
        'is_bookable',
    ];

    protected $translatable = ['name', 'description'];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_approved' => 'boolean',
        'is_bookable' => 'boolean',
    ];

    /**
     * Scope a query to only include active products
     */
    public function scopeActive(Builder $builder)
    {
        return $builder->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products
     */
    public function scopeFeatured(Builder $builder)
    {
        return $builder->where('is_featured', true);
    }

    /**
     * Scope a query to only include approved products
     */
    public function scopeApproved(Builder $builder)
    {
        return $builder->where('is_approved', true);
    }

    /**
     * Scope a query to only include new products
     */
    public function scopeNew(Builder $builder)
    {
        return $builder->where('is_new', true);
    }

    /**
     * Scope a query to only include bookable products
     */
    public function scopeBookable(Builder $builder)
    {
        return $builder->where('is_bookable', true);
    }

    /**
     * Scope a query to only include simple products
     */
    public function scopeSimple(Builder $builder)
    {
        return $builder->where('type', 'simple');
    }

    /**
     * Scope a query to only include variable products
     */
    public function scopeVariable(Builder $builder)
    {
        return $builder->where('type', 'variable');
    }

    /**
     * Scope a query to only include products with stock (based on branch stocks)
     */
    public function scopeInStock(Builder $builder)
    {
        return $builder->whereHas('branchProductStocks', function ($query) {
            $query->where('quantity', '>', 0);
        })->orWhereHas('variants.branchVariantStocks', function ($query) {
            $query->where('quantity', '>', 0);
        });
    }

    /**
     * Scope a query to only include products out of stock (based on branch stocks)
     */
    public function scopeOutOfStock(Builder $builder)
    {
        return $builder->whereDoesntHave('branchProductStocks', function ($query) {
            $query->where('quantity', '>', 0);
        })->whereDoesntHave('variants.branchVariantStocks', function ($query) {
            $query->where('quantity', '>', 0);
        });
    }

    /**
     * Scope a query to only include products by vendor
     */
    public function scopeByVendor(Builder $builder, $vendorId)
    {
        return $builder->where('vendor_id', $vendorId);
    }

    /**
     * Calculate the final price after discount
     */
    public function getFinalPriceAttribute(): float
    {
        if (! $this->discount || $this->discount <= 0) {
            return (float) $this->price;
        }

        if ($this->discount_type == 'percentage') {
            return (float) $this->price - (($this->price * $this->discount) / 100);
        }

        // Fixed discount
        return (float) max(0, $this->price - $this->discount);
    }

    /**
     * Check if product has discount
     */
    public function hasDiscount(): bool
    {
        return $this->discount > 0;
    }

    /**
     * Check if product is in stock (based on branch stocks)
     */
    public function isInStock(): bool
    {
        if ($this->type === 'simple') {
            if ($this->relationLoaded('branchProductStocks')) {
                return $this->branchProductStocks->contains(fn ($stock) => $stock->quantity > 0);
            }

            return $this->branchProductStocks()->where('quantity', '>', 0)->exists();
        }

        if ($this->relationLoaded('variants')) {
            return $this->variants->contains(function ($variant) {
                if ($variant->relationLoaded('branchVariantStocks')) {
                    return $variant->branchVariantStocks->contains(fn ($stock) => $stock->quantity > 0);
                }

                return $variant->branchVariantStocks()->where('quantity', '>', 0)->exists();
            });
        }

        return $this->variants()->whereHas('branchVariantStocks', function ($query) {
            $query->where('quantity', '>', 0);
        })->exists();
    }

    /**
     * Check if product is variable type
     */
    public function isVariable(): bool
    {
        return $this->type == 'variable';
    }

    /**
     * Check if product is simple type
     */
    public function isSimple(): bool
    {
        return $this->type == 'simple';
    }

    /**
     * Get the main image (first image or thumbnail)
     */
    public function getMainImageAttribute()
    {
        $firstImage = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        return $firstImage ? $firstImage->image_path : $this->thumbnail;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function images()
    {
        return $this->morphMany(ProductImage::class, 'imageable');
    }

    public function relations()
    {
        return $this->hasMany(ProductRelation::class, 'product_id');
    }

    public function relatedProducts()
    {
        return $this->relations()->where('type', 'related')->with('relatedProduct');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function variantValues()
    {
        return $this->hasManyThrough(ProductVariantValue::class, ProductVariant::class);
    }

    public function branchProductStocks()
    {
        return $this->hasMany(BranchProductStock::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_product_stocks');
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }

    public function reports()
    {
        return $this->hasMany(ProductReport::class);
    }

    public function manager()
    {
        return ProductPriceStockFactory::make($this);
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/'.$value)
                : asset('dashboard/images/product_image.jpg')
        );
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Relations needed by ProductResource / RelatedProductResource without N+1 queries.
     *
     * @return array<int|string, mixed>
     */
    public static function apiListRelations(): array
    {
        return [
            'vendor' => fn ($query) => Vendor::constrainVisibleRatingAggregates($query),
            'categories',
            'images',
            'branchProductStocks',
            'variants.branchVariantStocks',
            'variants.values.variantOption.variant',
            'ratings.user',
        ];
    }

    /**
     * Apply withExists for the authenticated user's favorite flag.
     */
    public static function constrainFavoriteExists(Builder|\Illuminate\Database\Eloquent\Relations\Relation $query, ?int $userId = null): Builder|\Illuminate\Database\Eloquent\Relations\Relation
    {
        $userId ??= auth()->id();

        if (! $userId) {
            return $query;
        }

        return $query->withExists([
            'favoritedBy as is_favorited' => fn ($favoriteQuery) => $favoriteQuery->where('favorites.user_id', $userId),
        ]);
    }

    /**
     * Limit product queries to active records for customer-facing API responses.
     */
    public static function constrainApiVisible(Builder|\Illuminate\Database\Eloquent\Relations\Relation $query, ?int $userId = null): Builder|\Illuminate\Database\Eloquent\Relations\Relation
    {
        return static::constrainFavoriteExists($query->where('is_active', true), $userId);
    }

    /**
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?static
    {
        if (request()->is('api/*')) {
            return static::query()
                ->where($field ?? $this->getRouteKeyName(), $value)
                ->where('is_active', true)
                ->first();
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
