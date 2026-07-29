<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class TicketPageController extends Controller
{
    public function index(): View
    {
        return view('tickets.index');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => ['required', 'uuid'],
            'email' => ['required', 'email'],
        ]);
        $order = Order::query()
            ->where('reference', $validated['reference'])
            ->where('customer_email', $validated['email'])
            ->first();

        if ($order === null) {
            return back()->withErrors(['reference' => 'سفارشی با این کد و ایمیل پیدا نشد.'])->withInput();
        }

        return redirect()->to(URL::temporarySignedRoute('tickets.show', now()->addMinutes(30), $order));
    }

    public function show(Order $order): View
    {
        $order->load([
            'items.ticket',
            'items.performanceSeat.seat',
            'items.performanceSeat.performance.event',
            'items.performanceSeat.performance.hall.venue',
        ]);

        return view('tickets.show', compact('order'));
    }
}
