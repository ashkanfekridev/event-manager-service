<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\View\View;

class EventPageController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->published()
            ->whereHas('performances', fn ($query) => $query->where('starts_at', '>', now())->where('status', 'scheduled'))
            ->withMin(['performances as next_performance_at' => fn ($query) => $query->where('starts_at', '>', now())], 'starts_at')
            ->orderBy('next_performance_at')
            ->paginate(12);

        return view('events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        abort_unless($event->isPublished(), 404);
        $event->load(['performances' => fn ($query) => $query
            ->where('starts_at', '>', now())
            ->with('hall.venue')
            ->withCount(['seats as available_seats_count' => fn ($query) => $query->where('status', 'available')])
            ->orderBy('starts_at')]);

        return view('events.show', compact('event'));
    }
}
