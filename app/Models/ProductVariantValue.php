<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
class ProductVariantValue extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'product_variant_id',
        'variant_option_id',
        'value',
    ];

    public $translatable = ['value'];

    protected $casts = [
        'value' => 'array',
    ];

    public function productVariant() {
        return $this->belongsTo(ProductVariant::class);
    }

    public function variantOption() {
        return $this->belongsTo(VariantOption::class);
    }

    public function getVariantOptionNameAttribute() {
        return $this->variantOption->getTranslation('name', app()->getLocale());
    }

    /**
     * Get the variant option name in a specific locale
     */
    public function getVariantOptionName(string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->variantOption->getTranslation('name', $locale);
    }

    /**
     * Get the value in a specific locale
     */
    public function getValue(string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->getTranslation('value', $locale);
    }
}
