<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHallRequest;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;

class HallController extends Controller
{
    public function store(StoreHallRequest $request, Venue $venue): JsonResponse
    {
        $hall = $venue->halls()->create($request->validated());

        return response()->json(['data' => $hall], 201);
    }
}
