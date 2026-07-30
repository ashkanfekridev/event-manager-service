<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Hall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'hall_id' => ['required', 'exists:halls,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'sales_start_at' => ['nullable', 'date', 'before_or_equal:starts_at'],
            'sales_end_at' => ['nullable', 'date', 'before_or_equal:starts_at'],
            'default_price' => ['required', 'numeric', 'min:0'],
        ]);
        $hall = Hall::query()->with(['seats' => fn ($query) => $query->where('is_active', true)])->findOrFail($validated['hall_id']);

        DB::transaction(function () use ($event, $hall, $validated): void {
            $performance = $event->performances()->create([
                'hall_id' => $hall->id,
                'starts_at' => $validated['starts_at'],
                'sales_start_at' => $validated['sales_start_at'] ?? now(),
                'sales_end_at' => $validated['sales_end_at'] ?? $validated['starts_at'],
            ]);
            $performance->seats()->createMany($hall->seats->map(fn ($seat): array => [
                'seat_id' => $seat->id,
                'price' => $seat->default_price ?? $validated['default_price'],
            ])->all());
        });

        return back()->with('success', 'سانس و موجودی صندلی‌های آن ساخته شد.');
    }
}
