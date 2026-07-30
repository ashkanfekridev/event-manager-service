<?php

namespace App\Models;

use Database\Factories\SeatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['hall_id', 'section', 'row_label', 'number', 'code', 'type', 'is_active', 'aisle_after', 'aisle_after_row', 'default_price'])]
class Seat extends Model
{
    /** @use HasFactory<SeatFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn (Seat $seat) => $seat->refreshHallCapacity());
        static::deleted(fn (Seat $seat) => $seat->refreshHallCapacity());
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'aisle_after' => 'boolean',
            'aisle_after_row' => 'boolean',
            'default_price' => 'decimal:2',
        ];
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function performanceSeats(): HasMany
    {
        return $this->hasMany(PerformanceSeat::class);
    }

    private function refreshHallCapacity(): void
    {
        Hall::query()->whereKey($this->hall_id)->update([
            'capacity' => self::query()
                ->where('hall_id', $this->hall_id)
                ->where('is_active', true)
                ->count(),
        ]);
    }
}
