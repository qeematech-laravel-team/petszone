<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['imageable_id', 'imageable_type', 'path'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function imageable()
    {
        return $this->morphTo();
    }
    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getImagePathAttribute(){
        return asset('storage/'.$this->path);
    }

    /**
     * Scope a query by imageable type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('imageable_type', $type);
    }

    /**
     * Scope a query by imageable ID
     */
    public function scopeByImageable($query, $imageableId)
    {
        return $query->where('imageable_id', $imageableId);
    }

    /**
     * Check if image exists
     */
    public function exists(): bool
    {
        return $this->path && file_exists(storage_path('app/public/' . $this->path));
    }
}
