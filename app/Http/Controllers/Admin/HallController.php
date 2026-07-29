<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHallRequest;
use App\Models\Hall;
use App\Models\Venue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HallController extends Controller
{
    public function store(StoreHallRequest $request, Venue $venue): RedirectResponse
    {
        $hall = $venue->halls()->create($request->validated());

        return redirect()->route('admin.halls.show', $hall)->with('success', 'سالن ساخته شد؛ حالا صندلی‌ها را تعریف کنید.');
    }

    public function show(Hall $hall): View
    {
        return view('admin.halls.show', ['hall' => $hall->load('venue', 'seats')]);
    }
}
