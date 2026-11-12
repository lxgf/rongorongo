<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A single glyph occurrence on a tablet line (glyph rendering or ligature).
 * Location fields are included only when the line is loaded (not when nested in a line).
 *
 * @mixin \App\Models\TabletRendering
 */
class OccurrenceResource extends JsonResource
{
    private const MODIFIERS = ['inverted', 'mirrored', 'small', 'enlarged', 'truncated', 'distorted', 'uncertain', 'nonstandard'];

    public function toArray(Request $request): array
    {
        $hasLine = $this->relationLoaded('tabletLine');
        $ligature = $this->compound_glyph_id ? $this->compoundGlyph : null;

        return [
            'tablet' => $this->when($hasLine, fn () => $this->tabletLine->tablet->code),
            'side' => $this->when($hasLine, fn () => $this->tabletLine->side_letter),
            'line' => $this->when($hasLine, fn () => $this->tabletLine->line),
            'position' => $this->position,
            'type' => $ligature ? 'ligature' : 'glyph',
            'glyph' => $this->rendering?->glyph->barthel_code,
            'rendering' => $this->rendering?->code,
            'ligature' => $ligature?->code,
            'ligature_glyphs' => $ligature?->parts->map(fn ($part) => $part->glyph->barthel_code)->values(),
            'modifiers' => collect(self::MODIFIERS)
                ->filter(fn ($modifier) => $this->{"is_{$modifier}"})
                ->values(),
            'svg' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => asset($image->path))->values()),
            'line_url' => $this->when($hasLine, fn () => route('line', [
                $this->tabletLine->tablet->code, $this->tabletLine->side_letter, $this->tabletLine->line,
            ])),
        ];
    }
}
