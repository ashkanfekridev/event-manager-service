@extends('layouts.app')

@section('title', 'خرید بلیت رویدادهای هنری | رویدادینو')

@section('hero')
    <div class="home-hero">
        <div class="home-hero__content">
            <span class="eyebrow"><i></i> برنامه‌های تازه و محبوب</span>
            <h1>هنر را از نزدیک<br><em>تجربه کن</em></h1>
            <p>کنسرت‌ها و نمایش‌های پیش رو را پیدا کن، بهترین صندلی را انتخاب کن و بلیتت را همان لحظه تحویل بگیر.</p>

            <form class="event-search" method="get" action="{{ route('events.index') }}">
                @if ($filters['type'] ?? null)
                    <input type="hidden" name="type" value="{{ $filters['type'] }}">
                @endif
                <span class="event-search__icon">⌕</span>
                <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="جست‌وجوی نام کنسرت، نمایش یا هنرمند..." aria-label="جست‌وجوی رویداد">
                <button type="submit">جست‌وجو</button>
            </form>

            <div class="home-hero__trust">
                <span><b>✓</b> انتخاب صندلی</span>
                <span><b>✓</b> پرداخت امن</span>
                <span><b>✓</b> تحویل آنی بلیت</span>
            </div>
        </div>
        <div class="home-hero__visual" aria-hidden="true">
            <div class="hero-ticket hero-ticket--back"><span>LIVE</span><strong>رویدادینو</strong></div>
            <div class="hero-ticket hero-ticket--front"><span>ADMIT ONE</span><strong>یک شب به‌یادماندنی</strong><small>صحنه نزدیک‌تر از همیشه</small></div>
            <div class="hero-orbit hero-orbit--one"></div>
            <div class="hero-orbit hero-orbit--two"></div>
        </div>
    </div>
@endsection

@section('content')
    <section class="event-browser">
        <div class="event-browser__heading">
            <div>
                <span class="section-kicker">روی صحنه</span>
                <h2>برنامه‌های پیش رو</h2>
            </div>
            <span class="event-count">{{ $events->total() }} برنامه</span>
        </div>

        <nav class="category-tabs" aria-label="دسته‌بندی رویدادها">
            <a @class(['active' => blank($filters['type'] ?? null)]) href="{{ route('events.index', ['q' => $filters['q'] ?? null]) }}"><span>✦</span> همه برنامه‌ها</a>
            <a @class(['active' => ($filters['type'] ?? null) === 'concert']) href="{{ route('events.index', ['type' => 'concert', 'q' => $filters['q'] ?? null]) }}"><span>♫</span> کنسرت‌ها</a>
            <a @class(['active' => ($filters['type'] ?? null) === 'theater']) href="{{ route('events.index', ['type' => 'theater', 'q' => $filters['q'] ?? null]) }}"><span>◐</span> نمایش‌ها</a>
            <a href="{{ route('events.index') }}"><span>◷</span> به‌زودی</a>
        </nav>

        @if (filled($filters['q'] ?? null))
            <div class="search-result-note">
                نتایج جست‌وجو برای «{{ $filters['q'] }}»
                <a href="{{ route('events.index', ['type' => $filters['type'] ?? null]) }}">پاک کردن جست‌وجو</a>
            </div>
        @endif

        <div class="event-grid">
            @forelse ($events as $event)
                @php($nextPerformance = $event->performances->first())
                @php($typeLabel = $event->type === 'concert' ? 'کنسرت' : ($event->type === 'theater' ? 'نمایش' : 'رویداد'))
                <article class="event-card">
                    <a class="event-card__poster" href="{{ route('events.show', $event) }}">
                        @if ($event->poster_url)
                            <img src="{{ $event->poster_url }}" alt="پوستر {{ $event->title }}" loading="lazy">
                        @else
                            <span class="event-card__placeholder"><i>ر</i><strong>{{ $event->title }}</strong></span>
                        @endif
                        <span class="event-card__type">{{ $typeLabel }}</span>
                        <span class="event-card__quick-action">مشاهده و خرید <b>←</b></span>
                    </a>
                    <div class="event-card__body">
                        <h3><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h3>
                        @if ($nextPerformance)
                            <p><span>⌖</span> {{ $nextPerformance->hall->venue->city }}، {{ $nextPerformance->hall->venue->name }}</p>
                            <p><span>◷</span> {{ $nextPerformance->starts_at->format('Y/m/d') }} · ساعت {{ $nextPerformance->starts_at->format('H:i') }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty-state empty-state--wide">
                    <span>⌕</span>
                    <h3>برنامه‌ای پیدا نشد</h3>
                    <p>عبارت دیگری را جست‌وجو کنید یا دسته‌بندی را تغییر دهید.</p>
                    <a class="btn" href="{{ route('events.index') }}">مشاهده همه برنامه‌ها</a>
                </div>
            @endforelse
        </div>

        <div class="pagination-wrap">{{ $events->links() }}</div>
    </section>

    <section class="purchase-promise">
        <div><span>۱</span><strong>برنامه را پیدا کن</strong><small>بین رویدادهای منتشرشده جست‌وجو کن</small></div>
        <i></i>
        <div><span>۲</span><strong>سانس و صندلی را انتخاب کن</strong><small>جای دقیق خودت را روی نقشه ببین</small></div>
        <i></i>
        <div><span>۳</span><strong>بلیت را تحویل بگیر</strong><small>بعد از پرداخت، بلیت فوراً صادر می‌شود</small></div>
    </section>
@endsection
