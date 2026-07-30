@extends('layouts.app')

@section('title', 'خرید بلیت '.$performance->event->title)

@section('content')
    <div
        class="checkout-page"
        data-checkout-root
        data-reservation-url="{{ url('/api/v1/performances/'.$performance->id.'/reservations') }}"
        data-confirm-url-template="{{ url('/api/v1/orders/__REFERENCE__/confirm') }}"
    >
        <div class="checkout-heading">
            <div><div class="breadcrumb"><a href="{{ route('events.index') }}">برنامه‌ها</a><span>›</span><a href="{{ route('events.show', $performance->event) }}">{{ $performance->event->title }}</a><span>›</span><span>انتخاب صندلی</span></div><h1>انتخاب صندلی</h1><p>{{ $performance->starts_at->format('Y/m/d H:i') }} · {{ $performance->hall->venue->name }} / {{ $performance->hall->name }}</p></div>
            <a class="btn secondary" href="{{ route('events.show', $performance->event) }}">تغییر سانس</a>
        </div>

        <div class="purchase-steps">
            <div class="purchase-step active" data-step-indicator="1"><span>۱</span><strong>انتخاب صندلی</strong></div>
            <div class="purchase-step" data-step-indicator="2"><span>۲</span><strong>اطلاعات خریدار</strong></div>
            <div class="purchase-step" data-step-indicator="3"><span>۳</span><strong>پرداخت و بلیت</strong></div>
        </div>

        <div class="checkout-grid">
            <section class="card seat-picker-card">
                <div id="step-1">
                    <div class="card-heading"><span class="section-kicker">نقشه سالن</span><h2>صندلی‌های خود را انتخاب کنید</h2><p>ابتدا بخش سالن را انتخاب کنید. حداکثر ۱۰ صندلی در هر سفارش قابل انتخاب است.</p></div>
                    @if ($seatSections->isNotEmpty())
                        <div class="section-picker" role="tablist" aria-label="بخش‌های سالن">
                            @foreach ($seatSections as $section => $rows)
                                <button type="button" class="section-button {{ $loop->first ? 'active' : '' }}" data-section-target="{{ $section }}" role="tab" aria-controls="seat-section-{{ $loop->index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}">{{ $section }}</button>
                            @endforeach
                        </div>
                        <div class="screen"><span>صحنه</span></div>
                        @foreach ($seatSections as $section => $rows)
                            <div id="seat-section-{{ $loop->index }}" class="seat-section" data-seat-section="{{ $section }}" role="tabpanel" {{ $loop->first ? '' : 'hidden' }}>
                                <div class="seat-section__heading"><h3>{{ $section }}</h3><span>برای دیدن همه صندلی‌ها، نقشه را به طرفین بکشید</span></div>
                                <div class="seat-map-scroll" tabindex="0" aria-label="نقشه صندلی‌های بخش {{ $section }}">
                                    <div class="seat-map">
                                        @foreach ($rows as $rowLabel => $rowSeats)
                                            <div @class(['seat-row', 'aisle-after-row' => $rowSeats->contains(fn ($performanceSeat) => $performanceSeat->seat->aisle_after_row)])>
                                                <span class="seat-row-label">{{ $rowLabel }}</span>
                                                <div class="seat-row-seats">
                                                    @foreach ($rowSeats as $performanceSeat)
                                                        @php($available = $performanceSeat->status === 'available' || ($performanceSeat->status === 'reserved' && $performanceSeat->reserved_until?->isPast()))
                                                        <button type="button" class="seat {{ $performanceSeat->seat->aisle_after ? 'aisle-after' : '' }}" data-seat="{{ $performanceSeat->id }}" data-label="{{ $section }} / {{ $rowLabel }}-{{ $performanceSeat->seat->number }}" data-price="{{ $performanceSeat->price }}" {{ $available ? '' : 'disabled' }} aria-label="بخش {{ $section }}، ردیف {{ $rowLabel }}، صندلی {{ $performanceSeat->seat->number }}، {{ number_format((float) $performanceSeat->price) }} تومان" aria-pressed="false" title="صندلی {{ $performanceSeat->seat->number }} · {{ number_format((float) $performanceSeat->price) }} تومان">{{ $performanceSeat->seat->number }}</button>
                                                    @endforeach
                                                </div>
                                                <span class="seat-row-label seat-row-label--end">{{ $rowLabel }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="seat-legend"><span><i class="seat-sample available"></i> آزاد</span><span><i class="seat-sample selected"></i> انتخاب شما</span><span><i class="seat-sample unavailable"></i> فروخته‌شده</span><span><i class="aisle-sample"></i> راهرو</span></div>
                        <div class="seat-selection-bar">
                            <div><span>انتخاب شما</span><strong id="seat-selection-status">هنوز صندلی انتخاب نکرده‌اید</strong></div>
                            <button id="to-customer" class="btn" type="button" disabled>ادامه و ثبت اطلاعات</button>
                        </div>
                    @else
                        <div class="empty-state"><p>هنوز صندلی‌ای برای این سانس تعریف نشده است.</p></div>
                    @endif
                </div>

                <form id="step-2" hidden>
                    <div class="card-heading"><span class="section-kicker">مرحله دوم</span><h2>اطلاعات خریدار</h2><p>کد سفارش برای بازیابی بلیت‌ها همراه این اطلاعات استفاده می‌شود.</p></div>
                    <div class="form-grid">
                        <label class="field"><span>نام و نام خانوادگی</span><input name="customer_name" autocomplete="name" required></label>
                        <label class="field"><span>شماره موبایل</span><input name="customer_phone" inputmode="tel" autocomplete="tel" required></label>
                        <label class="field full"><span>ایمیل</span><input name="customer_email" type="email" autocomplete="email" required><small class="muted">ایمیل را دقیق وارد کنید؛ برای بازیابی بلیت لازم است.</small></label>
                    </div>
                    <div id="checkout-error" class="checkout-message"></div>
                    <div class="page-actions"><button class="btn" type="submit">ثبت رزرو و ادامه پرداخت</button><button id="back-to-seats" class="btn secondary" type="button">بازگشت</button></div>
                </form>

                <div id="step-3" class="status-box" hidden><span class="success-check">✓</span><h2>صندلی‌ها برای شما رزرو شد</h2><p>تا <strong id="reserved-until"></strong> فرصت دارید پرداخت را تکمیل کنید.</p><p class="muted">کد سفارش: <span id="order-reference" class="ticket-code"></span></p><div id="payment-error"></div><button id="confirm-payment" class="btn success">پرداخت آزمایشی و دریافت بلیت</button></div>
            </section>

            <aside class="card checkout-summary">
                <span class="section-kicker">سبد خرید</span><h3>خلاصه سفارش</h3>
                <div class="checkout-event-mini"><span>{{ $performance->event->type === 'concert' ? '♫' : '◐' }}</span><div><strong>{{ $performance->event->title }}</strong><small>{{ $performance->starts_at->format('Y/m/d H:i') }}</small></div></div>
                <div class="summary-line"><span>سالن</span><strong>{{ $performance->hall->name }}</strong></div>
                <div class="summary-line"><span>صندلی‌ها</span><strong id="selected-labels">انتخاب نشده</strong></div>
                <div class="summary-line"><span>تعداد</span><strong id="selected-count">۰</strong></div>
                <div class="checkout-total"><span>مبلغ قابل پرداخت</span><strong><span id="selected-total">۰</span> تومان</strong></div>
                <p class="checkout-note">صندلی رزروشده پس از ۱۰ دقیقه و در صورت تکمیل‌نشدن پرداخت آزاد می‌شود.</p>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/checkout.js') }}" defer></script>
@endpush
