@extends('layouts.app')
@section('title', 'داشبورد')
@section('content')
<div class="section-title"><div><h1>داشبورد مدیریت</h1><p class="page-subtitle">خلاصه وضعیت رویدادها و فروش بلیت</p></div><a class="btn" href="{{ route('admin.events.create') }}">＋ ساخت رویداد</a></div>
<div class="stats">
    <div class="card stat"><span class="muted">کل رویدادها</span><strong>{{ $eventCount }}</strong><small>{{ $publishedEventCount }} رویداد فعال</small></div>
    <div class="card stat"><span class="muted">زمان‌بندی انتشار</span><strong>{{ $scheduledEventCount }}</strong><small>در انتظار انتشار خودکار</small></div>
    <div class="card stat"><span class="muted">سالن‌ها</span><strong>{{ $hallCount }}</strong><small>در {{ $venueCount }} مجموعه</small></div>
    <div class="card stat"><span class="muted">فروش موفق</span><strong>{{ $paidOrderCount }}</strong><small>سفارش پرداخت‌شده</small></div>
</div>
<div class="grid" style="margin-top:18px">
    <section class="card"><div class="section-title"><h2>رویدادهای اخیر</h2><a class="btn small secondary" href="{{ route('admin.events.index') }}">مشاهده همه</a></div><div class="table-wrap"><table class="table"><tr><th>عنوان</th><th>وضعیت</th><th>سانس</th></tr>@forelse($recentEvents as $event)@php($status=$event->publicationStatus())<tr><td><a href="{{ route('admin.events.show', $event) }}"><strong>{{ $event->title }}</strong></a></td><td><span class="badge {{ $status }}">{{ ['published'=>'فعال','scheduled'=>'زمان‌بندی','draft'=>'پیش‌نویس'][$status] }}</span></td><td>{{ $event->performances_count }}</td></tr>@empty<tr><td colspan="3">هنوز رویدادی ساخته نشده است.</td></tr>@endforelse</table></div></section>
    <section class="card"><div class="section-title"><h2>آخرین سفارش‌ها</h2><a class="btn small secondary" href="{{ route('admin.orders.index') }}">مشاهده همه</a></div><div class="table-wrap"><table class="table"><tr><th>خریدار</th><th>مبلغ</th><th>وضعیت</th></tr>@forelse($recentOrders as $order)<tr><td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->customer_name }}</a><small class="muted" style="display:block">{{ $order->customer_phone }}</small></td><td>{{ number_format($order->total_amount) }}</td><td><span class="badge {{ $order->status === 'paid' ? 'published' : 'scheduled' }}">{{ ['paid'=>'پرداخت‌شده','pending'=>'در انتظار'][$order->status] ?? $order->status }}</span></td></tr>@empty<tr><td colspan="3">هنوز سفارشی ثبت نشده است.</td></tr>@endforelse</table></div></section>
</div>
@endsection
