<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerformanceResource;
use App\Models\Performance;

class PerformanceController extends Controller
{
    public function show(Performance $performance): PerformanceResource
    {
        return new PerformanceResource($performance->load(['event', 'hall.venue', 'seats.seat']));
    }
}
