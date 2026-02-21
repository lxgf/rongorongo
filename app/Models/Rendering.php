<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Rendering extends Model
{
    protected $fillable = ['glyph_id', 'code', 'description'];

    public function glyph(): BelongsTo
    {
        return $this->belongsTo(Glyph::class);
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
