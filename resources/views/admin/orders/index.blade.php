@extends('layouts.app')
@section('title', 'مدیریت سفارش‌ها')
@section('content')
<div class="section-title"><div><h1>سفارش‌ها</h1><p class="page-subtitle">مشاهده خریداران، وضعیت پرداخت و بلیت‌های صادرشده</p></div></div>
<section class="card"><form method="get" class="filter-bar"><input name="q" value="{{ request('q') }}" placeholder="نام، ایمیل، موبایل یا کد سفارش…"><select name="status"><option value="">همه وضعیت‌ها</option><option value="paid" @selected(request('status')==='paid')>پرداخت‌شده</option><option value="pending" @selected(request('status')==='pending')>در انتظار پرداخت</option><option value="expired" @selected(request('status')==='expired')>منقضی‌شده</option><option value="cancelled" @selected(request('status')==='cancelled')>لغوشده</option></select><button class="btn secondary">اعمال فیلتر</button></form></section>
<section class="card" style="margin-top:16px;padding:0"><div class="table-wrap"><table class="table"><thead><tr><th>خریدار</th><th>رویداد</th><th>بلیت</th><th>مبلغ</th><th>وضعیت</th><th>زمان ثبت</th><th></th></tr></thead><tbody>
@forelse($orders as $order)
@php($eventTitle=$order->items->first()?->performanceSeat?->performance?->event?->title)
<tr><td><strong>{{ $order->customer_name }}</strong><small class="muted" style="display:block">{{ $order->customer_phone }} · {{ $order->customer_email }}</small></td><td>{{ $eventTitle ?? '—' }}</td><td>{{ $order->items_count }}</td><td>{{ number_format($order->total_amount) }} تومان</td><td><span class="badge {{ $order->status === 'paid' ? 'published' : ($order->status === 'pending' ? 'scheduled' : 'draft') }}">{{ ['paid'=>'پرداخت‌شده','pending'=>'در انتظار','expired'=>'منقضی','cancelled'=>'لغوشده'][$order->status] ?? $order->status }}</span></td><td>{{ $order->created_at->format('Y/m/d H:i') }}</td><td><a class="btn small secondary" href="{{ route('admin.orders.show', $order) }}">جزئیات</a></td></tr>
@empty<tr><td colspan="7" class="muted">سفارشی پیدا نشد.</td></tr>@endforelse
</tbody></table></div></section><div style="margin-top:18px">{{ $orders->links() }}</div>
@endsection
