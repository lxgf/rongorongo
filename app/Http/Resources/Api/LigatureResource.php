<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CompoundGlyph */
class LigatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'glyphs' => $this->parts->map(fn ($part) => $part->glyph->barthel_code)->values(),
            'description' => $this->description,
            'occurrences_count' => $this->whenCounted('tabletRenderings'),
            'api_url' => route('api.ligature', $this->code),
            'occurrences' => OccurrenceResource::collection($this->whenLoaded('tabletRenderings')),
        ];
    }
}
