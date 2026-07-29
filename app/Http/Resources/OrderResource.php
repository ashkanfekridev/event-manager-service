<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->reference,
            'customer_name' => $this->customer_name,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'reserved_until' => $this->reserved_until,
            'paid_at' => $this->paid_at,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => [
                'price' => $item->unit_price,
                'seat' => $item->relationLoaded('performanceSeat') && $item->performanceSeat->relationLoaded('seat') ? [
                    'code' => $item->performanceSeat->seat->code,
                    'row' => $item->performanceSeat->seat->row_label,
                    'number' => $item->performanceSeat->seat->number,
                ] : null,
                'ticket_code' => $item->relationLoaded('ticket') ? $item->ticket?->code : null,
            ])),
        ];
    }
}
