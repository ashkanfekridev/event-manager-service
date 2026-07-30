@extends('layouts.app')
@section('title', 'خرید بلیت '.$performance->event->title)
@section('content')
<div class="section-title"><div><h1>خرید بلیت {{ $performance->event->title }}</h1><p class="page-subtitle">{{ $performance->starts_at->format('Y/m/d H:i') }} · {{ $performance->hall->venue->name }} / {{ $performance->hall->name }}</p></div><a class="btn secondary" href="{{ route('events.show', $performance->event) }}">تغییر سانس</a></div>
<div class="purchase-steps"><div class="purchase-step active" data-step-indicator="1"><span>۱</span><strong>انتخاب صندلی</strong></div><div class="purchase-step" data-step-indicator="2"><span>۲</span><strong>اطلاعات خریدار</strong></div><div class="purchase-step" data-step-indicator="3"><span>۳</span><strong>پرداخت و بلیت</strong></div></div>
<div class="checkout-grid">
    <section class="card">
        <div id="step-1">
            <h2>صندلی‌های خود را انتخاب کنید</h2><p class="muted">ابتدا بخش سالن را انتخاب کنید. حداکثر ۱۰ صندلی در هر سفارش قابل انتخاب است.</p>
            @if($seatSections->isNotEmpty())
                <div class="section-picker" role="tablist" aria-label="بخش‌های سالن">
                    @foreach($seatSections as $section => $rows)
                        <button type="button" class="section-button {{ $loop->first ? 'active' : '' }}" data-section-target="{{ $section }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $section }}</button>
                    @endforeach
                </div>
                <div class="screen">صحنه</div>
                @foreach($seatSections as $section => $rows)
                    <div class="seat-section" data-seat-section="{{ $section }}" {{ $loop->first ? '' : 'hidden' }}>
                        <h3>{{ $section }}</h3>
                        @foreach($rows as $rowLabel => $rowSeats)
                            <div class="seat-row">
                                <span class="seat-row-label">ردیف {{ $rowLabel }}</span>
                                <div class="seat-row-seats">
                                    @foreach($rowSeats as $performanceSeat)
                                        @php($available = $performanceSeat->status === 'available' || ($performanceSeat->status === 'reserved' && $performanceSeat->reserved_until?->isPast()))
                                        <button type="button" class="seat {{ $performanceSeat->seat->aisle_after ? 'aisle-after' : '' }}" data-seat="{{ $performanceSeat->id }}" data-label="{{ $section }} / {{ $rowLabel }}-{{ $performanceSeat->seat->number }}" data-price="{{ $performanceSeat->price }}" {{ $available ? '' : 'disabled' }} aria-label="بخش {{ $section }}، ردیف {{ $rowLabel }}، صندلی {{ $performanceSeat->seat->number }}">{{ $performanceSeat->seat->number }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
                <div class="seat-legend"><span><i class="seat-sample available"></i> آزاد</span><span><i class="seat-sample selected"></i> انتخاب شما</span><span><i class="seat-sample unavailable"></i> فروخته‌شده</span></div>
            @else
                <div class="empty-state"><p>هنوز صندلی‌ای برای این سانس تعریف نشده است.</p></div>
            @endif
            <div class="button-row"><button id="to-customer" class="btn" disabled>ادامه و ثبت اطلاعات</button></div>
        </div>
        <form id="step-2" style="display:none">
            <h2>اطلاعات دریافت‌کننده بلیت</h2><p class="muted">کد سفارش برای بازیابی بلیت‌ها همراه این اطلاعات استفاده می‌شود.</p>
            <div class="form-grid"><label class="field"><span>نام و نام خانوادگی</span><input name="customer_name" autocomplete="name" required></label><label class="field"><span>شماره موبایل</span><input name="customer_phone" inputmode="tel" autocomplete="tel" required></label><label class="field full"><span>ایمیل</span><input name="customer_email" type="email" autocomplete="email" required><small class="muted">ایمیل را دقیق وارد کنید؛ برای بازیابی بلیت لازم است.</small></label></div>
            <div id="checkout-error" style="margin-top:15px"></div><div class="button-row" style="margin-top:20px"><button class="btn" type="submit">ثبت رزرو و ادامه پرداخت</button><button id="back-to-seats" class="btn secondary" type="button">بازگشت</button></div>
        </form>
        <div id="step-3" style="display:none" class="status-box"><h2>صندلی‌ها برای شما رزرو شد</h2><p>تا <strong id="reserved-until"></strong> فرصت دارید پرداخت را تکمیل کنید.</p><p class="muted">کد سفارش: <span id="order-reference" class="ticket-code"></span></p><div id="payment-error"></div><button id="confirm-payment" class="btn success">پرداخت آزمایشی و دریافت بلیت</button></div>
    </section>
    <aside class="card checkout-summary"><h3>خلاصه سفارش</h3><div class="summary-line"><span>رویداد</span><strong>{{ $performance->event->title }}</strong></div><div class="summary-line"><span>سانس</span><strong>{{ $performance->starts_at->format('Y/m/d H:i') }}</strong></div><div class="summary-line"><span>صندلی‌ها</span><strong id="selected-labels">انتخاب نشده</strong></div><div class="summary-line"><span>تعداد</span><strong id="selected-count">۰</strong></div><div class="summary-line"><span>مبلغ کل</span><strong><span id="selected-total">۰</span> تومان</strong></div><p class="muted" style="font-size:13px">صندلی رزروشده پس از ۱۰ دقیقه و در صورت تکمیل‌نشدن پرداخت آزاد می‌شود.</p></aside>
</div>
@endsection
@push('scripts')
<script>
const selected=new Map();const buttons=document.querySelectorAll('.seat:not(:disabled)');const nextButton=document.getElementById('to-customer');const labels=document.getElementById('selected-labels');const count=document.getElementById('selected-count');const total=document.getElementById('selected-total');
const sectionButtons=document.querySelectorAll('[data-section-target]');const seatSections=document.querySelectorAll('[data-seat-section]');sectionButtons.forEach(button=>button.addEventListener('click',()=>{sectionButtons.forEach(item=>{const isActive=item===button;item.classList.toggle('active',isActive);item.setAttribute('aria-selected',isActive?'true':'false')});seatSections.forEach(section=>{section.hidden=section.dataset.seatSection!==button.dataset.sectionTarget})}));
function syncSummary(){labels.textContent=selected.size?[...selected.values()].map(item=>item.label).join('، '):'انتخاب نشده';count.textContent=selected.size.toLocaleString('fa-IR');total.textContent=[...selected.values()].reduce((sum,item)=>sum+item.price,0).toLocaleString('fa-IR');nextButton.disabled=!selected.size;}
buttons.forEach(button=>button.addEventListener('click',()=>{const id=Number(button.dataset.seat);if(selected.has(id)){selected.delete(id);button.classList.remove('selected')}else if(selected.size<10){selected.set(id,{label:button.dataset.label,price:Number(button.dataset.price)});button.classList.add('selected')}syncSummary()}));
function showStep(step){[1,2,3].forEach(number=>{document.getElementById(`step-${number}`).style.display=number===step?(number===2?'block':'block'):'none';const indicator=document.querySelector(`[data-step-indicator="${number}"]`);indicator.classList.toggle('active',number===step);indicator.classList.toggle('done',number<step)})}
nextButton.addEventListener('click',()=>showStep(2));document.getElementById('back-to-seats').addEventListener('click',()=>showStep(1));
let currentOrder=null;document.getElementById('step-2').addEventListener('submit',async event=>{event.preventDefault();const submit=event.submitter;submit.disabled=true;submit.textContent='در حال ثبت رزرو…';const body=Object.fromEntries(new FormData(event.currentTarget));body.performance_seat_ids=[...selected.keys()];const response=await fetch('/api/v1/performances/{{ $performance->id }}/reservations',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(body)});const payload=await response.json();submit.disabled=false;submit.textContent='ثبت رزرو و ادامه پرداخت';if(!response.ok){const message=payload.errors?Object.values(payload.errors).flat()[0]:payload.message;document.getElementById('checkout-error').innerHTML=`<div class="alert error">${message||'رزرو انجام نشد.'}</div>`;return}currentOrder=payload.data;document.getElementById('order-reference').textContent=currentOrder.reference;document.getElementById('reserved-until').textContent=new Date(currentOrder.reserved_until).toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'});showStep(3)});
document.getElementById('confirm-payment').addEventListener('click',async event=>{event.currentTarget.disabled=true;event.currentTarget.textContent='در حال تأیید پرداخت…';const response=await fetch(`/api/v1/orders/${currentOrder.reference}/confirm`,{method:'POST',headers:{'Accept':'application/json'}});const payload=await response.json();if(response.ok){window.location.href=payload.data.ticket_url;return}document.getElementById('payment-error').innerHTML=`<div class="alert error">${payload.message||'پرداخت انجام نشد.'}</div>`;event.currentTarget.disabled=false;event.currentTarget.textContent='تلاش مجدد برای پرداخت'});
</script>
@endpush
