<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $events = Event::query()
            ->published()
            ->whereHas('performances', fn ($query) => $query->where('starts_at', '>', now())->where('status', 'scheduled'))
            ->with(['performances' => fn ($query) => $query->where('starts_at', '>', now())->orderBy('starts_at')])
            ->latest('published_at')
            ->paginate();

        return EventResource::collection($events);
    }

    public function show(Event $event): EventResource
    {
        abort_unless($event->isPublished(), 404);

        return new EventResource($event->load(['performances' => fn ($query) => $query->where('starts_at', '>', now())->with('hall.venue')->orderBy('starts_at')]));
    }
}
