<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',        
        'target',      
        'code',        
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /* ================= Relationships ================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ================= Helpers ================= */

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return ! is_null($this->verified_at);
    }

    public function markAsVerified(): void
    {
        $this->update([
            'verified_at' => now(),
        ]);
    }

    /* ================= Scopes ================= */

    public function scopeValid($query)
    {
        return $query
            ->whereNull('verified_at')
            ->where('expires_at', '>', now());
    }
}
