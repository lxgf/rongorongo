<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Tablet extends Model
{
    protected $fillable = ['code', 'name', 'name_en', 'name_ru', 'type', 'location', 'description'];

    /** Corpus totals shown on the About page and in the API */
    public static function corpusStats(): array
    {
        return [
            'tablets' => self::count(),
            'lines' => TabletLine::count(),
            'glyphs' => Glyph::count(),
            'renderings' => Rendering::count(),
            'ligatures' => CompoundGlyph::count(),
            'occurrences' => TabletRendering::count(),
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(TabletLine::class);
    }

    public function occurrences(): HasManyThrough
    {
        return $this->hasManyThrough(TabletRendering::class, TabletLine::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
