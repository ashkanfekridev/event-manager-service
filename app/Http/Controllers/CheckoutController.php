<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use App\Models\PerformanceSeat;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class CheckoutController extends Controller
{
    public function show(Performance $performance): View
    {
        $performance->load(['event', 'hall.venue', 'seats.seat']);
        abort_unless($performance->event->isPublished(), 404);

        $seatSections = $performance->seats
            ->sortBy(fn (PerformanceSeat $performanceSeat): array => [
                $performanceSeat->seat->section,
                $performanceSeat->seat->row_label,
                (int) $performanceSeat->seat->number,
            ])
            ->groupBy(fn (PerformanceSeat $performanceSeat): string => $performanceSeat->seat->section)
            ->map(fn (Collection $sectionSeats): Collection => $sectionSeats
                ->groupBy(fn (PerformanceSeat $performanceSeat): string => $performanceSeat->seat->row_label));

        return view('checkout.show', compact('performance', 'seatSections'));
    }
}
