<?php

use App\Models\Event;
use App\Models\Hall;
use App\Models\Performance;
use App\Models\PerformanceSeat;
use App\Models\Seat;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an admin can define a hall layout and create an event performance', function () {
    $venue = Venue::factory()->create();

    $this->post(route('admin.halls.store', $venue), ['name' => 'Main Hall'])
        ->assertRedirect();

    $hall = Hall::query()->firstOrFail();
    $this->post(route('admin.seats.store', $hall), [
        'section' => 'main',
        'rows' => 2,
        'seats_per_row' => 3,
        'type' => 'standard',
    ])->assertRedirect();

    expect($hall->fresh()->capacity)->toBe(6);

    $event = Event::factory()->create();
    $this->post(route('admin.performances.store', $event), [
        'hall_id' => $hall->id,
        'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
        'default_price' => 500000,
    ])->assertRedirect();

    expect($event->performances()->firstOrFail()->seats()->count())->toBe(6);
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

    $this->get(route('admin.orders.index'))->assertOk()->assertSee('Ali Ahmadi')->assertSee('09120000000');
    $this->get(route('admin.orders.show', $reference))->assertOk()->assertSee('ali@example.com');

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

test('an admin can edit and immediately toggle event publication', function () {
    $event = Event::factory()->create(['published_at' => null]);
    Performance::factory()->for($event)->create();

    $this->get(route('events.show', $event))->assertNotFound();
    $this->get(route('admin.events.edit', $event))->assertOk()->assertSee('نحوه انتشار');

    $this->put(route('admin.events.update', $event), [
        'title' => 'Edited Concert',
        'slug' => $event->slug,
        'type' => 'concert',
        'publication_mode' => 'draft',
    ])->assertRedirect(route('admin.events.show', $event));

    $this->patch(route('admin.events.publication.toggle', $event))->assertRedirect();

    expect($event->fresh()->isPublished())->toBeTrue();
    $this->get(route('events.show', $event))->assertOk()->assertSee('Edited Concert');

    $this->patch(route('admin.events.publication.toggle', $event))->assertRedirect();
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
