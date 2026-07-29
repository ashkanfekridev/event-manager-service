<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'description' => $this->description,
            'poster_url' => $this->poster_url,
            'duration_minutes' => $this->duration_minutes,
            'age_limit' => $this->age_limit,
            'published_at' => $this->published_at,
            'performances' => PerformanceResource::collection($this->whenLoaded('performances')),
        ];
    }
}
