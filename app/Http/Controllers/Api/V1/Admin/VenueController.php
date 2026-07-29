<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;

class VenueController extends Controller
{
    public function store(StoreVenueRequest $request): JsonResponse
    {
        $venue = Venue::query()->create($request->validated());

        return response()->json(['data' => $venue], 201);
    }
}
