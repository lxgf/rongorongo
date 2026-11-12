<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Glyph */
class GlyphResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $image = $this->preferredImagePath();

        return [
            'code' => $this->barthel_code,
            'description' => $this->description,
            'meaning' => $this->meaning ? [
                'en' => $this->meaning,
                'ru' => $this->meaning_ru,
                'status' => $this->meaning_status,
                'source_en' => $this->meaning_source,
                'source_ru' => $this->meaning_source_ru,
            ] : null,
            'image' => $image ? asset($image) : null,
            'renderings_count' => $this->whenCounted('renderings'),
            'occurrences_count' => $this->whenCounted('occurrences'),
            'url' => route('glyph', $this->barthel_code),
            'api_url' => route('api.glyph', $this->barthel_code),
            'renderings' => RenderingResource::collection($this->whenLoaded('renderings')),
            'ligatures' => LigatureResource::collection($this->whenLoaded('ligatures')),
            'occurrences' => OccurrenceResource::collection($this->whenLoaded('occurrences')),
        ];
    }
}
