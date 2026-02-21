<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TabletRendering extends Model
{
    protected $fillable = [
        'tablet_line_id',
        'rendering_id',
        'compound_glyph_id',
        'position',
        'is_inverted',
        'is_mirrored',
        'is_small',
        'is_enlarged',
        'is_truncated',
        'is_distorted',
        'is_uncertain',
        'is_nonstandard',
    ];

    protected function casts(): array
    {
        return [
            'is_inverted' => 'boolean',
            'is_mirrored' => 'boolean',
            'is_small' => 'boolean',
            'is_enlarged' => 'boolean',
            'is_truncated' => 'boolean',
            'is_distorted' => 'boolean',
            'is_uncertain' => 'boolean',
            'is_nonstandard' => 'boolean',
        ];
    }

    public function tabletLine(): BelongsTo
    {
        return $this->belongsTo(TabletLine::class);
    }

    public function rendering(): BelongsTo
    {
        return $this->belongsTo(Rendering::class);
    }

    public function compoundGlyph(): BelongsTo
    {
        return $this->belongsTo(CompoundGlyph::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
