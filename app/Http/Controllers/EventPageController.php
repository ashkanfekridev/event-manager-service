<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventPageController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['concert', 'theater'])],
        ]);

        $events = Event::query()
            ->published()
            ->whereHas('performances', fn ($query) => $query->where('starts_at', '>', now())->where('status', 'scheduled'))
            ->when($filters['q'] ?? null, fn ($query, string $search) => $query->where('title', 'like', '%'.$search.'%'))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->with(['performances' => fn ($query) => $query
                ->where('starts_at', '>', now())
                ->where('status', 'scheduled')
                ->with('hall.venue')
                ->orderBy('starts_at')])
            ->withMin(['performances as next_performance_at' => fn ($query) => $query
                ->where('starts_at', '>', now())
                ->where('status', 'scheduled')], 'starts_at')
            ->orderBy('next_performance_at')
            ->paginate(12)
            ->withQueryString();

        return view('events.index', compact('events', 'filters'));
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
