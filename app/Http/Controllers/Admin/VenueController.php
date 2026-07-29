<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Models\Venue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VenueController extends Controller
{
    public function index(): View
    {
        return view('admin.venues.index', ['venues' => Venue::query()->with('halls')->latest()->get()]);
    }

    public function store(StoreVenueRequest $request): RedirectResponse
    {
        Venue::query()->create($request->validated());

        return back()->with('success', 'مجموعه با موفقیت ساخته شد.');
    }
}
