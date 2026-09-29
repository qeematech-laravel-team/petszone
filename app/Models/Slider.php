<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = ['image'];

    public function getImagePathAttribute()
    {
        return asset('storage/'.$this->image);
    }
}
