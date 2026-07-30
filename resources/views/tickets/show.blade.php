@extends('layouts.app')

@section('title', 'سفارش '.$order->reference)

@section('hero')
    <div class="compact-hero compact-hero--success">
        <span class="success-check">✓</span>
        <span class="eyebrow eyebrow--light">{{ $order->status === 'paid' ? 'پرداخت موفق' : 'جزئیات سفارش' }}</span>
        <h1>{{ $order->status === 'paid' ? 'خرید شما با موفقیت انجام شد' : 'سفارش شما' }}</h1>
        <p>کد پیگیری: <b dir="ltr">{{ $order->reference }}</b></p>
    </div>
@endsection

@section('content')
    @php($firstItem = $order->items->first())
    <div class="order-overview">
        <section class="card">
            <div class="card-heading"><span class="section-kicker">خلاصه سفارش</span><h2>اطلاعات پرداخت</h2></div>
            <div class="summary-line"><span>خریدار</span><strong>{{ $order->customer_name }}</strong></div>
            <div class="summary-line"><span>وضعیت</span><span class="badge {{ $order->status === 'paid' ? 'published' : 'scheduled' }}">{{ ['paid' => 'پرداخت‌شده', 'pending' => 'در انتظار پرداخت', 'expired' => 'منقضی‌شده', 'cancelled' => 'لغوشده'][$order->status] ?? $order->status }}</span></div>
            <div class="summary-line"><span>مبلغ کل</span><strong>{{ number_format($order->total_amount) }} تومان</strong></div>
            <div class="summary-line"><span>تاریخ پرداخت</span><strong>{{ $order->paid_at?->format('Y/m/d H:i') ?? '—' }}</strong></div>
        </section>
        @if ($firstItem)
            <section class="order-event-card">
                <span>رویداد</span>
                <h2>{{ $firstItem->performanceSeat->performance->event->title }}</h2>
                <p>◷ {{ $firstItem->performanceSeat->performance->starts_at->format('Y/m/d H:i') }}</p>
                <p>⌖ {{ $firstItem->performanceSeat->performance->hall->venue->name }} / {{ $firstItem->performanceSeat->performance->hall->name }}</p>
            </section>
        @endif
    </div>

    <div class="ticket-list">
        @if ($firstItem)
            @php($performance = $firstItem->performanceSeat->performance)
            @php($event = $performance->event)
            @php($hall = $performance->hall)
            @php($venue = $hall->venue)
            <article class="ticket-document">
                <header class="ticket-document__hero">
                    @if ($event->poster_url)
                        <img src="{{ $event->poster_url }}" alt="پوستر {{ $event->title }}">
                    @endif
                    <div class="ticket-document__hero-shade"></div>
                    <div class="ticket-document__brand"><span class="ticket-document__brand-mark">ر</span><strong>رویدادینو</strong><small>سامانه فروش بلیت رویداد</small></div>
                    <div class="ticket-document__event"><span>بلیت رسمی ورود</span><h2>{{ $event->title }}</h2><small>{{ $event->type === 'concert' ? 'کنسرت' : 'نمایش' }}</small></div>
                </header>

                <section class="ticket-document__intro">
                    <div><span>تاریخ اجرا</span><strong>{{ $performance->starts_at->format('Y/m/d') }}</strong></div>
                    <div><span>ساعت</span><strong>{{ $performance->starts_at->format('H:i') }}</strong></div>
                    <div><span>مبلغ کل</span><strong>{{ number_format((float) $order->total_amount) }} تومان</strong></div>
                </section>

                <div class="ticket-document__code-strip">
                    <div><span>کد سفارش</span><strong dir="ltr">{{ $order->reference }}</strong></div>
                    <div><span>تعداد صندلی</span><strong>{{ $order->items->count() }} صندلی</strong></div>
                </div>

                <section class="ticket-document__details">
                    <div class="ticket-document__venue">
                        <span>محل برگزاری</span>
                        <h3>{{ $venue->name }} — {{ $hall->name }}</h3>
                        <p>{{ $venue->city }}، {{ $venue->address }}</p>
                        <dl>
                            <div><dt>دارنده بلیت</dt><dd>{{ $order->customer_name }}</dd></div>
                            <div><dt>شماره تماس</dt><dd dir="ltr">{{ $order->customer_phone }}</dd></div>
                        </dl>
                    </div>
                    <div class="ticket-document__validation">
                        <div class="ticket-document__barcode" aria-hidden="true"></div>
                        <span>کد کنترل سفارش</span>
                        <strong dir="ltr">{{ $order->reference }}</strong>
                        <small>کد هر صندلی در جدول پایین درج شده است</small>
                    </div>
                </section>

                <section class="ticket-document__seats">
                    <div class="ticket-document__seats-heading"><h3>صندلی‌های خریداری‌شده</h3><span>{{ $order->items->count() }} جایگاه</span></div>
                    <div class="ticket-document__seat-row ticket-document__seat-row--heading"><span>بخش</span><span>ردیف</span><span>صندلی</span><span>کد بلیت</span></div>
                    @foreach ($order->items as $item)
                        <div class="ticket-document__seat-row">
                            <strong>{{ $item->performanceSeat->seat->section }}</strong>
                            <strong>{{ $item->performanceSeat->seat->row_label }}</strong>
                            <div class="ticket-document__seat-position"><strong>{{ $item->performanceSeat->seat->number }}</strong><small dir="ltr">{{ $item->performanceSeat->seat->code }}</small></div>
                            <code dir="ltr">{{ $item->ticket?->code ?? 'صادر نشده' }}</code>
                        </div>
                    @endforeach
                </section>

                <section class="ticket-document__rules">
                    <h3>نکات مهم ورود</h3>
                    <ol>
                        <li>حداقل ۳۰ دقیقه پیش از شروع اجرا در محل برگزاری حضور داشته باشید.</li>
                        <li>پس از آغاز برنامه، امکان ورود به سالن یا جابه‌جایی صندلی تضمین نمی‌شود.</li>
                        <li>هر بلیت فقط برای یک‌بار ورود معتبر است؛ کد بلیت را در اختیار دیگران قرار ندهید.</li>
                        <li>همراه داشتن نسخه چاپی یا فایل این بلیت هنگام ورود الزامی است.</li>
                        <li>عکاسی و فیلم‌برداری تابع قوانین محل برگزاری است.</li>
                    </ol>
                </section>

                <footer class="ticket-document__footer"><span>این بلیت برای تمام صندلی‌های درج‌شده معتبر است.</span><strong dir="ltr">{{ $order->reference }}</strong></footer>
            </article>
        @else
            <div class="empty-state">این سفارش بلیتی ندارد.</div>
        @endif
    </div>

    <div class="page-actions"><button class="btn" type="button" data-print-page>چاپ / ذخیره PDF</button><a class="btn secondary" href="{{ route('tickets.index') }}">بازیابی سفارش دیگر</a></div>
@endsection
