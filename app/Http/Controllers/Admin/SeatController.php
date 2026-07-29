<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatController extends Controller
{
    public function store(Request $request, Hall $hall): RedirectResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string', 'max:100'],
            'rows' => ['required', 'integer', 'min:1', 'max:26'],
            'seats_per_row' => ['required', 'integer', 'min:1', 'max:100'],
            'type' => ['required', 'in:standard,vip,wheelchair'],
        ]);

        DB::transaction(function () use ($hall, $validated): void {
            for ($rowIndex = 0; $rowIndex < $validated['rows']; $rowIndex++) {
                $rowLabel = chr(65 + $rowIndex);

                for ($number = 1; $number <= $validated['seats_per_row']; $number++) {
                    $hall->seats()->firstOrCreate(
                        ['code' => $validated['section'].'-'.$rowLabel.'-'.$number],
                        ['section' => $validated['section'], 'row_label' => $rowLabel, 'number' => (string) $number, 'type' => $validated['type']],
                    );
                }
            }

            $hall->update(['capacity' => $hall->seats()->where('is_active', true)->count()]);
        });

        return back()->with('success', 'چیدمان صندلی‌ها ثبت شد.');
    }
}
