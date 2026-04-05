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

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
