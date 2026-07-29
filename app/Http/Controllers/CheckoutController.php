<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use Illuminate\Contracts\View\View;

class CheckoutController extends Controller
{
    public function show(Performance $performance): View
    {
        $performance->load(['event', 'hall.venue', 'seats.seat']);
        abort_unless($performance->event->isPublished(), 404);

        return view('checkout.show', compact('performance'));
    }
}
