<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Glyph extends Model
{
    protected $fillable = ['barthel_code', 'description', 'meaning', 'meaning_status', 'meaning_source'];

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

    /**
     * SVG first, then GIF from DB images.
     */
    public function preferredImagePath(): ?string
    {
        $svgPath = "glyphs/{$this->barthel_code}.svg";
        if (file_exists(public_path($svgPath))) {
            return $svgPath;
        }

        return $this->images->first()?->path;
    }
}
