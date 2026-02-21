<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Glyph extends Model
{
    protected $fillable = ['barthel_code', 'description'];

    public function renderings(): HasMany
    {
        return $this->hasMany(Rendering::class);
    }

    public function compoundGlyphParts(): HasMany
    {
        return $this->hasMany(CompoundGlyphPart::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
