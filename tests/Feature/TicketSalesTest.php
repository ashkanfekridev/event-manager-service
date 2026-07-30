<?php

use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Halls\Pages\EditHall;
use App\Filament\Resources\Halls\RelationManagers\SeatsRelationManager;
use App\Filament\Resources\Performances\Pages\CreatePerformance;
use App\Models\Event;
use App\Models\Hall;
use App\Models\Performance;
use App\Models\PerformanceSeat;
use App\Models\Seat;
use App\Models\User;
use App\Models\Venue;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('an admin can define a hall layout and create an event performance', function () {
    $this->actingAs(User::factory()->admin()->create());

    $venue = Venue::factory()->create();
    $hall = Hall::factory()->for($venue)->create(['name' => 'Main Hall']);

    Livewire::test(SeatsRelationManager::class, [
        'ownerRecord' => $hall,
        'pageClass' => EditHall::class,
    ])->callAction(TestAction::make('generateLayout')->table(), [
        'section' => 'main',
        'rows' => 2,
        'seats_per_row' => 3,
        'aisles_after' => '2',
        'type' => 'standard',
    ])->assertHasNoActionErrors();

    expect($hall->fresh()->capacity)->toBe(6);

    expect($hall->seats()->where('row_label', 'A')->where('number', '2')->firstOrFail()->aisle_after)->toBeTrue();

    Livewire::test(SeatsRelationManager::class, [
        'ownerRecord' => $hall,
        'pageClass' => EditHall::class,
    ])->callAction(TestAction::make('configureAisles')->table(), [
        'section' => 'main',
        'aisles_after' => '1',
    ])->assertHasNoActionErrors();

    expect($hall->seats()->where('row_label', 'A')->where('number', '1')->firstOrFail()->aisle_after)->toBeTrue()
        ->and($hall->seats()->where('row_label', 'A')->where('number', '2')->firstOrFail()->aisle_after)->toBeFalse();

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
        ->assertHasNoFormErrors()
        ->assertRedirect();

    expect($event->performances()->firstOrFail()->seats()->count())->toBe(6)
        ->and($event->performances()->firstOrFail()->seats()->firstOrFail()->price)->toBe('500000.00');
});

test('only administrators can access the Filament panel', function () {
    $this->get(route('filament.admin.pages.dashboard'))
        ->assertRedirect(route('filament.admin.auth.login'));

    $this->actingAs(User::factory()->create())
        ->get(route('filament.admin.pages.dashboard'))
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('filament.admin.pages.dashboard'))
        ->assertOk();
});

test('an administrator can edit their Filament profile', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('filament.admin.auth.profile'))
        ->assertOk();
});

test('an administrator can open every Filament management section', function () {
    $this->actingAs(User::factory()->admin()->create());

    foreach ([
        'filament.admin.resources.events.index',
        'filament.admin.resources.performances.index',
        'filament.admin.resources.venues.index',
        'filament.admin.resources.halls.index',
        'filament.admin.resources.orders.index',
    ] as $routeName) {
        $this->get(route($routeName))->assertOk();
    }
});

test('a customer can reserve available seats and confirm the order', function () {
    $venue = Venue::factory()->create();
    $hall = Hall::factory()->for($venue)->create(['capacity' => 1]);
    $seat = Seat::factory()->for($hall)->create(['code' => 'main-A-1']);
    $event = Event::factory()->create();
    $performance = Performance::factory()->for($event)->for($hall)->create();
    $performanceSeat = PerformanceSeat::factory()->for($performance)->for($seat)->create(['price' => 750000]);

    $reservation = $this->postJson("/api/v1/performances/{$performance->id}/reservations", [
        'performance_seat_ids' => [$performanceSeat->id],
        'customer_name' => 'Ali Ahmadi',
        'customer_email' => 'ali@example.com',
        'customer_phone' => '09120000000',
    ])->assertCreated()->assertJsonPath('data.status', 'pending');

    $reference = $reservation->json('data.reference');

    $this->postJson("/api/v1/performances/{$performance->id}/reservations", [
        'performance_seat_ids' => [$performanceSeat->id],
        'customer_name' => 'Sara Ahmadi',
        'customer_email' => 'sara@example.com',
        'customer_phone' => '09121111111',
    ])->assertUnprocessable();

    $confirmation = $this->postJson("/api/v1/orders/{$reference}/confirm")
        ->assertOk()
        ->assertJsonPath('data.status', 'paid')
        ->assertJsonPath('data.items.0.seat.code', 'main-A-1');

    $this->assertDatabaseHas('performance_seats', ['id' => $performanceSeat->id, 'status' => 'sold']);
    $this->assertDatabaseCount('tickets', 1);

    $this->get($confirmation->json('data.ticket_url'))
        ->assertOk()
        ->assertSee('خرید شما با موفقیت انجام شد')
        ->assertSee('main-A-1');

    $this->actingAs(User::factory()->admin()->create());
    $this->get(route('filament.admin.resources.orders.index'))->assertOk()->assertSee('Ali Ahmadi')->assertSee('09120000000');
    $this->get(route('filament.admin.resources.orders.view', $reference))->assertOk()->assertSee('ali@example.com');

    $lookup = $this->post(route('tickets.lookup'), ['reference' => $reference, 'email' => 'ali@example.com']);
    $lookup->assertRedirect();
    $this->get($lookup->headers->get('Location'))->assertOk()->assertSee('main-A-1');
});

test('reservation response explains when ticket sales will open', function () {
    $performance = Performance::factory()->create(['sales_start_at' => now()->addHour()]);
    $performanceSeat = PerformanceSeat::factory()->for($performance)->create();

    $this->postJson("/api/v1/performances/{$performance->id}/reservations", [
        'performance_seat_ids' => [$performanceSeat->id],
        'customer_name' => 'Ali Ahmadi',
        'customer_email' => 'ali@example.com',
        'customer_phone' => '09120000000',
    ])->assertUnprocessable()->assertJsonValidationErrorFor('performance');
});

test('customers can browse published events', function () {
    $event = Event::factory()->create(['title' => 'Hamlet']);
    $performance = Performance::factory()->for($event)->create();

    $this->get(route('events.index'))->assertOk()->assertSee('Hamlet');
    $this->get(route('events.show', $event))->assertOk()->assertSee($performance->starts_at->format('Y/m/d'));
    $this->get(route('checkout.show', $performance))->assertOk()->assertSee('اطلاعات خریدار');
});

test('customers can switch hall sections and see seats arranged by row with aisles', function () {
    $hall = Hall::factory()->create();
    $event = Event::factory()->create();
    $performance = Performance::factory()->for($event)->for($hall)->create();

    $mainSeat = Seat::factory()->for($hall)->create([
        'section' => 'همکف',
        'row_label' => 'A',
        'number' => '1',
        'code' => 'main-A-1',
        'aisle_after' => true,
    ]);
    $vipSeat = Seat::factory()->for($hall)->create([
        'section' => 'VIP',
        'row_label' => 'B',
        'number' => '1',
        'code' => 'vip-B-1',
    ]);

    PerformanceSeat::factory()->for($performance)->for($mainSeat)->create();
    PerformanceSeat::factory()->for($performance)->for($vipSeat)->create();

    $this->get(route('checkout.show', $performance))
        ->assertOk()
        ->assertSee('data-section-target="همکف"', false)
        ->assertSee('data-section-target="VIP"', false)
        ->assertSee('ردیف A')
        ->assertSee('ردیف B')
        ->assertSee('aisle-after', false);
});

test('an admin can edit and immediately toggle event publication', function () {
    $this->actingAs(User::factory()->admin()->create());

    $event = Event::factory()->create(['published_at' => null]);
    Performance::factory()->for($event)->create();

    $this->get(route('events.show', $event))->assertNotFound();

    Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
        ->fillForm(['title' => 'Edited Concert', 'published_at' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    Livewire::test(ListEvents::class)
        ->callAction(TestAction::make('togglePublication')->table($event));

    expect($event->fresh()->isPublished())->toBeTrue();
    $this->get(route('events.show', $event))->assertOk()->assertSee('Edited Concert');

    Livewire::test(ListEvents::class)
        ->callAction(TestAction::make('togglePublication')->table($event));

    expect($event->fresh()->published_at)->toBeNull();
});

test('a scheduled event becomes public at its publication time', function () {
    $publicationTime = now()->addDay();
    $event = Event::factory()->create(['published_at' => $publicationTime]);
    Performance::factory()->for($event)->create(['starts_at' => now()->addWeek()]);

    expect($event->isScheduled())->toBeTrue();
    $this->get(route('events.show', $event))->assertNotFound();

    $this->travelTo($publicationTime->addSecond());

    $this->get(route('events.show', $event))->assertOk();
    $this->getJson('/api/v1/events/'.$event->slug)->assertOk();
});
