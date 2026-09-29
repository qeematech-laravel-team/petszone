<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class VariantOption extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'variant_id',
        'name',
        'code',
    ];

    protected $translatable = ['name'];

    protected $casts = [
        'name' => 'array',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
