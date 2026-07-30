<?php

namespace App\Filament\Resources\Performances\Pages;

use App\Filament\Resources\Performances\PerformanceResource;
use App\Models\Performance;
use App\Models\PerformanceSeat;
use App\Models\Seat;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreatePerformance extends CreateRecord
{
    protected static string $resource = PerformanceResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Performance {
            $defaultPrice = $data['default_price'];
            unset($data['default_price']);

            $data['sales_start_at'] ??= now();
            $data['sales_end_at'] ??= $data['starts_at'];

            $performance = Performance::query()->create($data);
            $timestamp = now();

            Seat::query()
                ->where('hall_id', $performance->hall_id)
                ->where('is_active', true)
                ->select(['id', 'default_price'])
                ->chunkById(500, function ($seats) use ($defaultPrice, $performance, $timestamp): void {
                    PerformanceSeat::query()->insert($seats->map(fn (Seat $seat): array => [
                        'performance_id' => $performance->id,
                        'seat_id' => $seat->id,
                        'price' => $seat->default_price ?? $defaultPrice,
                        'status' => 'available',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ])->all());
                });

            return $performance;
        });
    }
}
