<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;

class EventController extends Controller
{
    public function store(StoreEventRequest $request): EventResource
    {
        return new EventResource(Event::query()->create($request->validated()));
    }
}
