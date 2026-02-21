<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompoundGlyphPart extends Model
{
    public $timestamps = false;

    protected $fillable = ['compound_glyph_id', 'glyph_id', 'order'];

    public function compoundGlyph(): BelongsTo
    {
        return $this->belongsTo(CompoundGlyph::class);
    }

    public function glyph(): BelongsTo
    {
        return $this->belongsTo(Glyph::class);
    }
}
