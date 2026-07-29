<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeatsRequest;
use App\Models\Hall;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SeatController extends Controller
{
    public function store(StoreSeatsRequest $request, Hall $hall): JsonResponse
    {
        $seats = DB::transaction(function () use ($request, $hall) {
            $createdSeats = collect($request->validated('seats'))
                ->map(fn (array $seat) => $hall->seats()->create($seat));

            $hall->update(['capacity' => $hall->seats()->where('is_active', true)->count()]);

            return $createdSeats;
        });

        return response()->json(['data' => $seats], 201);
    }
}
