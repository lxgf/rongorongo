<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Tablet extends Model
{
    protected $fillable = ['code', 'name', 'location', 'description'];

    public function lines(): HasMany
    {
        return $this->hasMany(TabletLine::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
