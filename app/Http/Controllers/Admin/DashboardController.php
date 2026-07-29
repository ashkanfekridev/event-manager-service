<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Hall;
use App\Models\Order;
use App\Models\Venue;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'venueCount' => Venue::query()->count(),
            'hallCount' => Hall::query()->count(),
            'eventCount' => Event::query()->count(),
            'publishedEventCount' => Event::query()->published()->count(),
            'scheduledEventCount' => Event::query()->where('published_at', '>', now())->count(),
            'paidOrderCount' => Order::query()->where('status', 'paid')->count(),
            'recentOrders' => Order::query()->latest()->limit(8)->get(),
            'recentEvents' => Event::query()->withCount('performances')->latest()->limit(5)->get(),
        ]);
    }
}
