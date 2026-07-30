<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePerformanceRequest;
use App\Http\Resources\PerformanceResource;
use App\Models\Event;
use App\Models\Hall;
use App\Models\Performance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformanceController extends Controller
{
    public function store(StorePerformanceRequest $request, Event $event): PerformanceResource
    {
        $validated = $request->validated();
        $hall = Hall::query()->with(['seats' => fn ($query) => $query->where('is_active', true)])->findOrFail($validated['hall_id']);

        throw_if($hall->seats->isEmpty(), ValidationException::withMessages(['hall_id' => 'The selected hall has no active seats.']));

        $performance = DB::transaction(function () use ($event, $hall, $validated): Performance {
            $performance = $event->performances()->create([
                'hall_id' => $hall->id,
                'starts_at' => $validated['starts_at'],
                'sales_start_at' => $validated['sales_start_at'] ?? now(),
                'sales_end_at' => $validated['sales_end_at'] ?? $validated['starts_at'],
            ]);

            $performance->seats()->createMany(
                $hall->seats->map(fn ($seat): array => [
                    'seat_id' => $seat->id,
                    'price' => $seat->default_price ?? $validated['default_price'],
                ])->all(),
            );

            return $performance;
        });

        return new PerformanceResource($performance->load('hall.venue'));
    }
}
