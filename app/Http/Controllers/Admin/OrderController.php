<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with(['items.performanceSeat.performance.event'])
            ->withCount('items')
            ->when($request->input('status'), fn ($query, string $status) => $query->where('status', $status))
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request): void {
                $search = '%'.$request->string('q').'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('customer_name', 'like', $search)
                        ->orWhere('customer_email', 'like', $search)
                        ->orWhere('customer_phone', 'like', $search)
                        ->orWhere('reference', 'like', $search);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load([
            'items.ticket',
            'items.performanceSeat.seat',
            'items.performanceSeat.performance.event',
            'items.performanceSeat.performance.hall.venue',
        ]);

        return view('admin.orders.show', compact('order'));
    }
}
