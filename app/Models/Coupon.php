<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'discount_value',
        'min_cart_amount',
        'usage_limit_per_user',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function isUsableByUser(User $user)
    {
        return $this->usage_limit_per_user > $this->orders()->where('user_id', $user->id)->count();
    }

    public function isUsable(User $user)
    {
        return $this->isValid() && $this->isUsableByUser($user);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function isValid()
    {
        $now = now();

        return $this->is_active
            && (is_null($this->start_date) || $this->start_date <= $now)
            && (is_null($this->end_date) || $this->end_date >= $now);
    }
}
