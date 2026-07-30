@extends('layouts.app')

@section('title', $event->title.' | رویدادینو')

@section('hero')
    <div class="event-detail-hero">
        <div class="event-detail-hero__poster">
            @if ($event->poster_url)
                <img src="{{ $event->poster_url }}" alt="پوستر {{ $event->title }}">
            @else
                <span class="event-card__placeholder"><i>ر</i><strong>{{ $event->title }}</strong></span>
            @endif
        </div>
        <div class="event-detail-hero__content">
            <div class="breadcrumb"><a href="{{ route('events.index') }}">برنامه‌ها</a><span>›</span><span>{{ $event->title }}</span></div>
            <span class="eyebrow eyebrow--light"><i></i> {{ $event->type === 'concert' ? 'کنسرت' : 'نمایش' }}</span>
            <h1>{{ $event->title }}</h1>
            <p>{{ $event->description }}</p>
            <div class="event-facts">
                @if ($event->duration_minutes)<span><b>◷</b> {{ $event->duration_minutes }} دقیقه</span>@endif
                @if ($event->age_limit)<span><b>＋</b> مناسب بالای {{ $event->age_limit }} سال</span>@endif
                <span><b>▣</b> {{ $event->performances->count() }} سانس فعال</span>
            </div>
            <a class="btn btn--light" href="#performances">انتخاب سانس و خرید بلیت</a>
        </div>
    </div>
@endsection

@section('content')
    <section id="performances" class="performance-section">
        <div class="event-browser__heading">
            <div><span class="section-kicker">زمان اجرا</span><h2>انتخاب سانس</h2><p>سانس مناسب را انتخاب کنید؛ در مرحله بعد نقشه صندلی‌ها نمایش داده می‌شود.</p></div>
            <a class="subtle-link" href="{{ route('tickets.index') }}">پیگیری بلیت‌های من ←</a>
        </div>

        <div class="performance-list">
            @forelse ($event->performances as $performance)
                <article class="performance-row">
                    <div class="performance-row__date">
                        <strong>{{ $performance->starts_at->format('d') }}</strong>
                        <span>{{ $performance->starts_at->format('Y/m/d') }}</span>
                    </div>
                    <div class="performance-row__time"><span>ساعت اجرا</span><strong>{{ $performance->starts_at->format('H:i') }}</strong></div>
                    <div class="performance-row__venue"><span>⌖</span><div><strong>{{ $performance->hall->venue->name }}</strong><small>{{ $performance->hall->venue->city }}، {{ $performance->hall->name }}</small></div></div>
                    <div class="performance-row__availability"><span>صندلی آزاد</span><strong>{{ $performance->available_seats_count }}</strong></div>
                    <a class="btn" href="{{ route('checkout.show', $performance) }}">انتخاب صندلی</a>
                </article>
            @empty
                <div class="empty-state empty-state--wide"><span>◷</span><h3>سانس فعالی وجود ندارد</h3><p>زمان اجراهای جدید به‌زودی اعلام می‌شود.</p></div>
            @endforelse
        </div>
    </section>
@endsection
