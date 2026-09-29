<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Vendor extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'owner_id',
        'phone',
        'address',
        'image',
        'is_active',
        'is_featured',
        'balance',
        'commission_rate',
        'plan_id',
        'subscription_start',
        'subscription_end',
    ];

    protected $translatable = ['name'];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/'.$value)
                : asset('dashboard/images/vendor_image.jpg')
        );
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(VendorSubscription::class);
    }

    public function activeSubscription()
    {
        $today = now()->startOfDay();

        return $this->subscriptions()
            ->where('status', 'active')
            ->whereDate('end_date', '>=', $today)
            ->whereDate('start_date', '<=', $today)
            ->first();
    }

    /**
     * Scope a query to only include active vendors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured vendors
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'vendor_users')
            ->withPivot(['is_active', 'branch_id', 'user_type']);
    }

    /**
     * Get the category requests for this vendor
     */
    public function categoryRequests()
    {
        return $this->hasMany(CategoryRequest::class);
    }

    public function variantRequests()
    {
        return $this->hasMany(VariantRequest::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function settings()
    {
        return $this->hasMany(VendorSetting::class);
    }

    public function ratings()
    {
        return $this->hasMany(VendorRating::class);
    }

    /**
     * Attach visible rating aggregates used by VendorResource.
     */
    public static function constrainVisibleRatingAggregates($query)
    {
        return $query
            ->withAvg(['ratings as visible_ratings_avg' => fn ($ratingQuery) => $ratingQuery->where('is_visible', true)], 'rating')
            ->withCount(['ratings as visible_ratings_count' => fn ($ratingQuery) => $ratingQuery->where('is_visible', true)]);
    }

    public function reports()
    {
        return $this->hasMany(VendorReport::class);
    }

    public function balanceTransactions()
    {
        return $this->hasMany(VendorBalanceTransaction::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(VendorWithdrawal::class);
    }
}
