<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceResource extends JsonResource
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
            'starts_at' => $this->starts_at,
            'sales_start_at' => $this->sales_start_at,
            'sales_end_at' => $this->sales_end_at,
            'status' => $this->status,
            'event' => new EventResource($this->whenLoaded('event')),
            'hall' => $this->whenLoaded('hall', fn (): array => [
                'id' => $this->hall->id,
                'name' => $this->hall->name,
                'venue' => $this->hall->relationLoaded('venue') ? [
                    'id' => $this->hall->venue->id,
                    'name' => $this->hall->venue->name,
                    'city' => $this->hall->venue->city,
                    'address' => $this->hall->venue->address,
                ] : null,
            ]),
            'seats' => $this->whenLoaded('seats', fn () => $this->seats->map(fn ($performanceSeat): array => [
                'id' => $performanceSeat->id,
                'price' => $performanceSeat->price,
                'status' => $performanceSeat->status === 'reserved' && $performanceSeat->reserved_until?->isPast()
                    ? 'available'
                    : $performanceSeat->status,
                'seat' => [
                    'id' => $performanceSeat->seat->id,
                    'section' => $performanceSeat->seat->section,
                    'row' => $performanceSeat->seat->row_label,
                    'number' => $performanceSeat->seat->number,
                    'code' => $performanceSeat->seat->code,
                    'type' => $performanceSeat->seat->type,
                ],
            ])),
        ];
    }
}
