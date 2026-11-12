<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Rendering */
class RenderingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $glyph = $this->glyph->barthel_code;

        return [
            'code' => $this->code,
            'glyph' => $glyph,
            'description' => $this->description,
            'occurrences_count' => $this->whenCounted('tabletRenderings'),
            'url' => route('rendering', $glyph),
            'api_url' => route('api.rendering', $this->code),
            'occurrences' => OccurrenceResource::collection($this->whenLoaded('tabletRenderings')),
        ];
    }
}
