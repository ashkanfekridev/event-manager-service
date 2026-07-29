@extends('layouts.app')
@section('title', $event->title)
@section('hero')<span class="badge">{{ $event->type === 'concert' ? 'کنسرت' : 'تئاتر' }}</span><h1>{{ $event->title }}</h1><p>{{ $event->description }}</p>@endsection
@section('content')
<div class="grid">
@forelse($event->performances as $performance)
<section class="card">
    <h2>سانس {{ $performance->starts_at->format('Y/m/d - H:i') }}</h2>
    <p class="muted">{{ $performance->hall->venue->name }}، {{ $performance->hall->name }} · {{ $performance->hall->venue->city }}</p>
    <div class="screen">صحنه</div>
    <form class="reservation-form" data-performance="{{ $performance->id }}">
        <div class="seat-map">
        @foreach($performance->seats->sortBy(fn($item) => [$item->seat->row_label, (int) $item->seat->number]) as $performanceSeat)
            @php($available = $performanceSeat->status === 'available' || ($performanceSeat->status === 'reserved' && $performanceSeat->reserved_until?->isPast()))
            <button type="button" class="seat" data-seat="{{ $performanceSeat->id }}" {{ $available ? '' : 'disabled' }} title="{{ number_format($performanceSeat->price) }} تومان">{{ $performanceSeat->seat->row_label }}-{{ $performanceSeat->seat->number }}</button>
        @endforeach
        </div>
        <div class="form-grid">
            <label class="field"><span>نام خریدار</span><input name="customer_name" required></label>
            <label class="field"><span>ایمیل</span><input name="customer_email" type="email" required></label>
            <label class="field"><span>موبایل</span><input name="customer_phone" required></label>
        </div>
        <p class="muted">رزرو تا ۱۰ دقیقه نگه داشته می‌شود.</p>
        <button class="btn reserve-button" type="submit">رزرو صندلی‌های انتخابی</button>
        <div class="reservation-result" style="margin-top:14px"></div>
    </form>
</section>
@empty<div class="card">سانس فعالی برای این رویداد وجود ندارد.</div>@endforelse
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.reservation-form').forEach((form) => {
    const selected = new Set();
    form.querySelectorAll('.seat:not(:disabled)').forEach((button) => button.addEventListener('click', () => {
        const id = Number(button.dataset.seat);
        selected.has(id) ? selected.delete(id) : selected.add(id);
        button.classList.toggle('selected');
    }));
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const result = form.querySelector('.reservation-result');
        if (!selected.size) { result.innerHTML = '<div class="alert error">حداقل یک صندلی انتخاب کنید.</div>'; return; }
        const body = Object.fromEntries(new FormData(form));
        body.performance_seat_ids = [...selected];
        const response = await fetch(`/api/v1/performances/${form.dataset.performance}/reservations`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify(body)});
        const payload = await response.json();
        if (!response.ok) { result.innerHTML = `<div class="alert error">${payload.message || 'رزرو انجام نشد.'}</div>`; return; }
        const order = payload.data;
        result.innerHTML = `<div class="alert">رزرو ثبت شد. کد: <b>${order.reference}</b><br><button type="button" class="btn confirm-payment">تأیید آزمایشی پرداخت</button></div>`;
        result.querySelector('.confirm-payment').addEventListener('click', async (buttonEvent) => {
            buttonEvent.target.disabled = true;
            const confirmation = await fetch(`/api/v1/orders/${order.reference}/confirm`, {method:'POST', headers:{'Accept':'application/json'}});
            const confirmed = await confirmation.json();
            result.innerHTML = confirmation.ok ? `<div class="alert">پرداخت تأیید شد. وضعیت سفارش: ${confirmed.data.status}</div>` : `<div class="alert error">${confirmed.message}</div>`;
        });
    });
});
</script>
@endpush
