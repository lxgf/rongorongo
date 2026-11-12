<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TabletLine extends Model
{
    protected $fillable = ['tablet_id', 'side', 'line', 'direction'];

    public function tablet(): BelongsTo
    {
        return $this->belongsTo(Tablet::class);
    }

    public function tabletRenderings(): HasMany
    {
        return $this->hasMany(TabletRendering::class)->orderBy('position');
    }

    private const SIDE_LABELS = [
        0 => 'recto',
        1 => 'verso',
    ];

    public function getSideLabelAttribute(): string
    {
        return self::SIDE_LABELS[$this->side] ?? 'side ' . chr(ord('a') + $this->side);
    }

    /** Side from URL: letter a–h (r/v = recto/verso) or index 0–7 */
    public static function sideFromParam(string $side): int
    {
        return match (true) {
            ctype_digit($side) => (int) $side,
            $side === 'r' => 0,
            $side === 'v' => 1,
            default => ord($side) - ord('a'),
        };
    }

    /** Side letter used in URLs: 0 → a, 1 → b, … */
    public function getSideLetterAttribute(): string
    {
        return chr(ord('a') + $this->side);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
