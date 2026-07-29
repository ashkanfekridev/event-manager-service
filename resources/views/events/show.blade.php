@extends('layouts.app')
@section('title', $event->title)
@section('hero')<span class="badge">{{ $event->type === 'concert' ? 'کنسرت' : 'تئاتر' }}</span><h1>{{ $event->title }}</h1><p>{{ $event->description }}</p>@endsection
@section('content')
<div class="section-title"><div><h2>انتخاب سانس</h2><p class="page-subtitle">زمان مناسب را انتخاب کنید؛ در مرحله بعد صندلی‌ها را خواهید دید.</p></div><a class="btn secondary" href="{{ route('tickets.index') }}">مشاهده بلیت‌های من</a></div>
<div class="grid">
@forelse($event->performances as $performance)
<article class="card">
    <span class="badge">{{ $performance->starts_at->format('Y/m/d') }}</span>
    <h2 style="margin:12px 0 5px">ساعت {{ $performance->starts_at->format('H:i') }}</h2>
    <div class="event-meta"><span>⌖ {{ $performance->hall->venue->name }}</span><span>▤ {{ $performance->hall->name }}</span></div>
    <p class="muted">{{ $performance->hall->venue->city }}، {{ $performance->hall->venue->address }}</p>
    <div class="summary-line"><span>صندلی‌های آزاد</span><strong>{{ $performance->available_seats_count }}</strong></div>
    <a class="btn" style="width:100%;margin-top:16px" href="{{ route('checkout.show', $performance) }}">انتخاب صندلی و خرید</a>
</article>
@empty<div class="card empty-state"><h3>سانس فعالی وجود ندارد</h3><p class="muted">زمان سانس‌های جدید به‌زودی اعلام می‌شود.</p></div>@endforelse
</div>
@endsection
