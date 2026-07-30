<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111827">
    <title>@yield('title', 'رویدادینو')</title>
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    @stack('head')
</head>
<body @class(['admin-body' => request()->routeIs('admin.*'), 'site-body' => ! request()->routeIs('admin.*')])>
@if (request()->routeIs('admin.*'))
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="brand" href="{{ route('admin.dashboard') }}">رویدادینو <span class="admin-brand-caption">مدیریت</span></a>
            <small>منوی اصلی</small>
            <nav class="admin-menu">
                <a class="{{ request()->routeIs('filament.admin.pages.dashboard') ? 'active' : '' }}" href="{{ route('filament.admin.pages.dashboard') }}">▦ داشبورد</a>
                <a class="{{ request()->routeIs('admin.events.*') || request()->routeIs('admin.performances.*') ? 'active' : '' }}" href="{{ route('admin.events.index') }}">◈ رویدادها</a>
                <a class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">◎ سفارش‌ها</a>
                <a class="{{ request()->routeIs('admin.venues.*') || request()->routeIs('admin.halls.*') || request()->routeIs('admin.seats.*') ? 'active' : '' }}" href="{{ route('admin.venues.index') }}">▤ سالن‌ها</a>
                <a href="{{ route('events.index') }}" target="_blank">↗ مشاهده سایت</a>
            </nav>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar">
                <div class="muted">@yield('title', 'پنل مدیریت')</div>
                <div class="admin-profile"><div><strong>مدیر سیستم</strong><small class="muted">Administrator</small></div><span class="avatar">م</span></div>
            </header>
            <main class="admin-content">
                @if (session('success'))
                    <div class="alert">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
@else
    <header class="site-header">
        <div class="site-header__bar">
            <div class="container site-header__inner">
                <a class="site-logo" href="{{ route('events.index') }}" aria-label="رویدادینو، صفحه اصلی">
                    <span class="site-logo__mark"><i></i><i></i><i></i></span>
                    <span><strong>رویدادینو</strong><small>تجربه زنده هنر</small></span>
                </a>

                <button class="site-menu-toggle" type="button" aria-label="باز کردن منو" aria-expanded="false" data-menu-toggle>
                    <span></span><span></span><span></span>
                </button>

                <nav class="site-nav" data-site-nav>
                    <a @class(['active' => request()->routeIs('events.*')]) href="{{ route('events.index') }}">برنامه‌ها</a>
                    <a @class(['active' => request()->routeIs('tickets.*')]) href="{{ route('tickets.index') }}">پیگیری خرید</a>
                    <a href="{{ route('events.index', ['type' => 'concert']) }}">کنسرت‌ها</a>
                    <a href="{{ route('events.index', ['type' => 'theater']) }}">نمایش‌ها</a>
                </nav>

                <div class="site-header__actions">
                    <a class="header-track-link" href="{{ route('tickets.index') }}"><span>◎</span> بلیت‌های من</a>
                    <a class="header-admin-link" href="{{ route('filament.admin.pages.dashboard') }}">ورود مدیر</a>
                </div>
            </div>
        </div>
    </header>

    @hasSection('hero')
        <section class="site-hero"><div class="container">@yield('hero')</div></section>
    @endif

    <main class="site-main">
        <div class="container">
            @if (session('success'))
                <div class="alert">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </div>
    </main>

    <footer class="site-footer">
        <div class="container site-footer__grid">
            <div class="site-footer__about">
                <a class="site-logo site-logo--footer" href="{{ route('events.index') }}">
                    <span class="site-logo__mark"><i></i><i></i><i></i></span>
                    <span><strong>رویدادینو</strong><small>تجربه زنده هنر</small></span>
                </a>
                <p>مرجع ساده و امن برای پیدا کردن رویدادهای هنری، انتخاب صندلی و دریافت آنلاین بلیت.</p>
            </div>
            <div>
                <h3>رویدادها</h3>
                <a href="{{ route('events.index') }}">همه برنامه‌ها</a>
                <a href="{{ route('events.index', ['type' => 'concert']) }}">کنسرت‌ها</a>
                <a href="{{ route('events.index', ['type' => 'theater']) }}">نمایش‌ها</a>
            </div>
            <div>
                <h3>راهنمای خرید</h3>
                <a href="{{ route('tickets.index') }}">پیگیری سفارش</a>
                <a href="{{ route('tickets.index') }}">دریافت دوباره بلیت</a>
                <a href="{{ route('filament.admin.pages.dashboard') }}">پنل برگزارکنندگان</a>
            </div>
            <div>
                <h3>پشتیبانی</h3>
                <p>هر روز از ساعت ۹ تا ۲۱</p>
                <a class="site-footer__contact" href="mailto:support@example.com">support@example.com</a>
            </div>
        </div>
        <div class="container site-footer__bottom"><span>© {{ now()->year }} رویدادینو</span><span>خرید امن، انتخاب صندلی، دریافت آنی بلیت</span></div>
    </footer>
@endif

<script src="{{ asset('js/site.js') }}" defer></script>
@stack('scripts')
</body>
</html>
