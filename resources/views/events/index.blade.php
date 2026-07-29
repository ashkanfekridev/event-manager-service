@extends('layouts.app')
@section('title', 'رویدادها')
@section('hero')<h1>لحظه‌هایی که باید تجربه‌شان کرد</h1><p>کنسرت‌ها و نمایش‌های پیش رو را ببینید و صندلی خودتان را انتخاب کنید.</p>@endsection
@section('content')
<div class="section-title"><h2>رویدادهای پیش رو</h2><span class="muted">{{ $events->total() }} رویداد</span></div>
<div class="grid">
@forelse($events as $event)
    <article class="card"><span class="badge">{{ $event->type === 'concert' ? 'کنسرت' : 'تئاتر' }}</span><h2>{{ $event->title }}</h2><p class="muted">{{ Str::limit($event->description, 130) }}</p><p>نزدیک‌ترین سانس: <strong>{{ \Illuminate\Support\Carbon::parse($event->next_performance_at)->format('Y/m/d H:i') }}</strong></p><a class="btn" href="{{ route('events.show', $event) }}">مشاهده و انتخاب صندلی</a></article>
@empty
    <div class="card"><h3>هنوز رویدادی منتشر نشده است</h3><p class="muted">از پنل مدیریت اولین رویداد و سانس را بسازید.</p></div>
@endforelse
</div>
<div style="margin-top:24px">{{ $events->links() }}</div>
@endsection
