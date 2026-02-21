<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CompoundGlyph extends Model
{
    protected $fillable = ['code', 'description'];

    public function parts(): HasMany
    {
        return $this->hasMany(CompoundGlyphPart::class)->orderBy('order');
    }

    public function tabletRenderings(): HasMany
    {
        return $this->hasMany(TabletRendering::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
