<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Performance;
use App\Models\PerformanceSeat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request, Performance $performance): OrderResource
    {
        $this->ensureSalesAreOpen($performance);

        $order = DB::transaction(function () use ($request, $performance): Order {
            $seatIds = $request->validated('performance_seat_ids');
            $seats = PerformanceSeat::query()
                ->where('performance_id', $performance->id)
                ->whereIn('id', $seatIds)
                ->lockForUpdate()
                ->get();

            if ($seats->count() !== count($seatIds)) {
                throw ValidationException::withMessages(['performance_seat_ids' => 'One or more seats do not belong to this performance.']);
            }

            $this->releaseExpiredSeats($seats);

            if ($seats->contains(fn (PerformanceSeat $seat): bool => $seat->status !== 'available')) {
                throw ValidationException::withMessages(['performance_seat_ids' => 'One or more selected seats are no longer available.']);
            }

            $reservedUntil = now()->addMinutes(10);
            $order = Order::query()->create([
                'reference' => (string) Str::uuid(),
                'customer_name' => $request->validated('customer_name'),
                'customer_email' => $request->validated('customer_email'),
                'customer_phone' => $request->validated('customer_phone'),
                'status' => 'pending',
                'total_amount' => $seats->sum('price'),
                'reserved_until' => $reservedUntil,
            ]);

            foreach ($seats as $seat) {
                $seat->update(['order_id' => $order->id, 'status' => 'reserved', 'reserved_until' => $reservedUntil]);
                $order->items()->create(['performance_seat_id' => $seat->id, 'unit_price' => $seat->price]);
            }

            return $order;
        });

        return new OrderResource($order->load('items.performanceSeat.seat', 'items.performanceSeat.performance.event'));
    }

    private function ensureSalesAreOpen(Performance $performance): void
    {
        if ($performance->status !== 'scheduled') {
            throw ValidationException::withMessages(['performance' => 'This performance is not active.']);
        }

        if ($performance->starts_at->isPast()) {
            throw ValidationException::withMessages(['performance' => 'This performance has already started.']);
        }

        if ($performance->sales_start_at?->isFuture()) {
            throw ValidationException::withMessages([
                'performance' => 'Ticket sales open at '.$performance->sales_start_at->format('Y/m/d H:i').'.',
            ]);
        }

        if ($performance->sales_end_at?->isPast()) {
            throw ValidationException::withMessages(['performance' => 'Ticket sales for this performance have ended.']);
        }
    }

    /** @param Collection<int, PerformanceSeat> $seats */
    private function releaseExpiredSeats(Collection $seats): void
    {
        foreach ($seats as $seat) {
            if ($seat->status === 'reserved' && $seat->reserved_until?->isPast()) {
                $seat->update(['order_id' => null, 'status' => 'available', 'reserved_until' => null]);
            }
        }
    }
}
