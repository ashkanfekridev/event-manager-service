<?php

use App\Filament\Resources\Halls\Pages\EditHall;
use App\Filament\Resources\Performances\Pages\CreatePerformance;
use App\Livewire\HallSeatLayoutEditor;
use App\Models\Event;
use App\Models\Hall;
use App\Models\Performance;
use App\Models\Seat;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('an administrator sees the visual seat layout on the hall edit page', function () {
    $hall = Hall::factory()->create();
    Seat::factory()->for($hall)->create([
        'section' => 'همکف',
        'row_label' => 'A',
        'number' => '1',
        'code' => 'main-A-1',
    ]);

    $component = Livewire::test(EditHall::class, ['record' => $hall->getRouteKey()])
        ->assertSee('جدول چیدمان، قیمت و راهروها')
        ->assertSee('صحنه')
        ->assertSee('ردیف A')
        ->assertSee('ستون 1')
        ->assertSee('main-A-1');

    expect($component->html())
        ->toContain('wire:click="toggleSeats(['.$hall->seats()->firstOrFail()->id.'])"');
});

test('an administrator selects a row and changes its price in bulk', function () {
    $hall = Hall::factory()->create();
    $firstRowSeats = collect([
        Seat::factory()->for($hall)->create(['section' => 'همکف', 'row_label' => 'A', 'number' => '1', 'code' => 'main-A-1']),
        Seat::factory()->for($hall)->create(['section' => 'همکف', 'row_label' => 'A', 'number' => '2', 'code' => 'main-A-2']),
    ]);
    $secondRowSeat = Seat::factory()->for($hall)->create([
        'section' => 'همکف',
        'row_label' => 'B',
        'number' => '1',
        'code' => 'main-B-1',
    ]);

    Livewire::test(HallSeatLayoutEditor::class, ['record' => $hall])
        ->call('toggleSeats', $firstRowSeats->pluck('id')->all())
        ->set('bulkPrice', '850000')
        ->call('applyBulkPrice')
        ->assertHasNoErrors();

    expect($firstRowSeats->map(fn (Seat $seat): ?string => $seat->fresh()->default_price)->all())
        ->toBe(['850000.00', '850000.00'])
        ->and($secondRowSeat->fresh()->default_price)->toBeNull();
});

test('an administrator creates vertical and horizontal aisles with column and row selection', function () {
    $hall = Hall::factory()->create();
    $firstColumnSeats = collect([
        Seat::factory()->for($hall)->create(['section' => 'همکف', 'row_label' => 'A', 'number' => '1', 'code' => 'main-A-1']),
        Seat::factory()->for($hall)->create(['section' => 'همکف', 'row_label' => 'B', 'number' => '1', 'code' => 'main-B-1']),
    ]);
    $secondColumnSeats = collect([
        Seat::factory()->for($hall)->create(['section' => 'همکف', 'row_label' => 'A', 'number' => '2', 'code' => 'main-A-2']),
        Seat::factory()->for($hall)->create(['section' => 'همکف', 'row_label' => 'B', 'number' => '2', 'code' => 'main-B-2']),
    ]);

    Livewire::test(HallSeatLayoutEditor::class, ['record' => $hall])
        ->call('toggleSeats', $firstColumnSeats->pluck('id')->all())
        ->set('bulkAisleAction', 'vertical_add')
        ->call('applyBulkAisle')
        ->call('clearSelection')
        ->call('toggleSeats', [
            $firstColumnSeats->first()->id,
            $secondColumnSeats->first()->id,
        ])
        ->set('bulkAisleAction', 'horizontal_add')
        ->call('applyBulkAisle')
        ->assertHasNoErrors();

    expect($firstColumnSeats->map(fn (Seat $seat): bool => $seat->fresh()->aisle_after)->all())
        ->toBe([true, true])
        ->and($secondColumnSeats->map(fn (Seat $seat): bool => $seat->fresh()->aisle_after)->all())
        ->toBe([false, false])
        ->and($firstColumnSeats->first()->fresh()->aisle_after_row)->toBeTrue()
        ->and($secondColumnSeats->first()->fresh()->aisle_after_row)->toBeTrue()
        ->and($firstColumnSeats->last()->fresh()->aisle_after_row)->toBeFalse()
        ->and($secondColumnSeats->last()->fresh()->aisle_after_row)->toBeFalse();
});

test('an administrator sets seat prices and aisles from the visual layout', function () {
    $hall = Hall::factory()->create();
    $firstSeat = Seat::factory()->for($hall)->create([
        'section' => 'همکف',
        'row_label' => 'A',
        'number' => '1',
        'code' => 'main-A-1',
    ]);
    $secondSeat = Seat::factory()->for($hall)->create([
        'section' => 'همکف',
        'row_label' => 'A',
        'number' => '2',
        'code' => 'main-A-2',
    ]);

    Livewire::test(HallSeatLayoutEditor::class, ['record' => $hall])
        ->set('prices.'.$firstSeat->id, 750000)
        ->set('prices.'.$secondSeat->id, null)
        ->set('aisles.'.$firstSeat->id, true)
        ->call('save')
        ->assertHasNoErrors();

    expect($firstSeat->fresh())
        ->default_price->toBe('750000.00')
        ->aisle_after->toBeTrue()
        ->and($secondSeat->fresh()->default_price)->toBeNull();
});

test('a new performance uses seat prices and falls back to its base price', function () {
    $hall = Hall::factory()->create();
    $pricedSeat = Seat::factory()->for($hall)->create([
        'code' => 'main-A-1',
        'default_price' => 900000,
    ]);
    $fallbackSeat = Seat::factory()->for($hall)->create([
        'code' => 'main-A-2',
        'default_price' => null,
    ]);
    $event = Event::factory()->create();

    Livewire::test(CreatePerformance::class)
        ->fillForm([
            'event_id' => $event->id,
            'hall_id' => $hall->id,
            'starts_at' => now()->addWeek(),
            'sales_start_at' => now(),
            'sales_end_at' => now()->addDays(6),
            'status' => 'scheduled',
            'default_price' => 500000,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $performance = Performance::query()->firstOrFail();

    expect($performance->seats()->whereBelongsTo($pricedSeat)->value('price'))->toBe('900000.00')
        ->and($performance->seats()->whereBelongsTo($fallbackSeat)->value('price'))->toBe('500000.00');
});
