<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'email_verified_at',
        'phone_verified_at',
        'role',
        'is_active',
        'is_verified',
        'password',
        'image',
        'referral_code',
        'referred_by_id',
        'wallet',
        'points',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/'.$value)
                : asset('dashboard/images/user_avatar.png')
        );
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (! $user->email && ! $user->phone) {
                throw new \Exception(__('You must enter at least one email or phone'));
            }
            if (! $user->referral_code) {
                $user->referral_code = Str::random(8);
            }
        });
    }

    public function ownedVendor()
    {
        return $this->hasOne(Vendor::class, 'owner_id');
    }

    public function vendorUsers()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_users')
            ->withPivot(['is_active', 'branch_id', 'user_type']);
    }

    public function vendor()
    {
        // Return cached vendor if available (set by View Composer or previous call)
        if (isset($this->cachedVendor)) {
            return $this->cachedVendor;
        }

        // Check if ownedVendor is already loaded (to avoid query)
        if ($this->relationLoaded('ownedVendor')) {
            $this->cachedVendor = $this->getRelation('ownedVendor');
            if ($this->cachedVendor) {
                return $this->cachedVendor;
            }
        }

        // Check if vendorUserRelation is already loaded
        if ($this->relationLoaded('vendorUserRelation')) {
            $vendorUser = $this->getRelation('vendorUserRelation');
            if ($vendorUser) {
                $this->cachedVendor = $vendorUser->vendor;

                return $this->cachedVendor;
            }
        }

        // Only query if not already loaded
        if ($this->ownedVendor) {
            $this->cachedVendor = $this->ownedVendor;
        } else {
            // Use VendorUser relationship for better performance (avoids join query)
            $vendorUser = $this->vendorUserRelation()->with('vendor')->first();
            $this->cachedVendor = $vendorUser?->vendor;
        }

        return $this->cachedVendor;
    }

    /**
     * Get the vendor user relationship (direct, not through belongsToMany)
     * This avoids the expensive join query from vendorUsers()
     */
    public function vendorUserRelation()
    {
        return $this->hasOne(\App\Models\VendorUser::class, 'user_id')
            ->where('is_active', true);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'favorites')->withTimestamps();
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
