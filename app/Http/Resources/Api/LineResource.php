<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\TabletLine */
class LineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tablet = $this->tablet->code;

        return [
            'tablet' => $tablet,
            'side' => $this->side_letter,
            'side_index' => $this->side,
            'side_label' => $this->side_label,
            'line' => $this->line,
            'direction' => $this->direction,
            'occurrences_count' => $this->whenCounted('tabletRenderings'),
            'url' => route('line', [$tablet, $this->side_letter, $this->line]),
            'api_url' => route('api.line', [$tablet, $this->side_letter, $this->line]),
            'occurrences' => OccurrenceResource::collection($this->whenLoaded('tabletRenderings')),
        ];
    }
}
