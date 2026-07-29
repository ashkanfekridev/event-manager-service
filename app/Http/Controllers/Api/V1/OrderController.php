<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load('items.performanceSeat.seat', 'items.ticket'));
    }

    public function confirm(Order $order): OrderResource|JsonResponse
    {
        $order = DB::transaction(function () use ($order): ?Order {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === 'paid') {
                return $order;
            }

            if ($order->status !== 'pending' || $order->reserved_until->isPast()) {
                $this->expire($order);

                return null;
            }

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_reference' => 'manual-'.Str::lower(Str::random(16)),
            ]);
            $order->items()->with('performanceSeat')->get()->each(function ($item): void {
                $item->performanceSeat->update(['status' => 'sold', 'reserved_until' => null]);
                $item->ticket()->firstOrCreate([], ['code' => (string) Str::uuid()]);
            });

            return $order;
        });

        if ($order === null) {
            return response()->json(['message' => 'The reservation has expired or cannot be paid.'], 409);
        }

        return new OrderResource($order->load('items.performanceSeat.seat', 'items.ticket'));
    }

    public function cancel(Order $order): JsonResponse
    {
        DB::transaction(function () use ($order): void {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === 'pending') {
                $order->update(['status' => 'cancelled']);
                $order->items()->with('performanceSeat')->get()->each(
                    fn ($item) => $item->performanceSeat->update(['order_id' => null, 'status' => 'available', 'reserved_until' => null]),
                );
            }
        });

        return response()->json(status: 204);
    }

    private function expire(Order $order): void
    {
        if ($order->status === 'pending') {
            $order->update(['status' => 'expired']);
            $order->items()->with('performanceSeat')->get()->each(
                fn ($item) => $item->performanceSeat->update(['order_id' => null, 'status' => 'available', 'reserved_until' => null]),
            );
        }
    }
}
