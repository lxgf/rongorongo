<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Tablet */
class TabletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'name_en' => $this->name_en,
            'name_ru' => $this->name_ru,
            'type' => $this->type,
            'location' => $this->location,
            'description' => $this->description,
            'lines_count' => $this->whenCounted('lines'),
            'occurrences_count' => $this->whenCounted('occurrences'),
            'photos' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => asset($image->path))->values()),
            'url' => route('tablet', $this->code),
            'api_url' => route('api.tablet', $this->code),
            'lines' => LineResource::collection($this->whenLoaded('lines')),
        ];
    }
}
