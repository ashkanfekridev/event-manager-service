@extends('layouts.app')
@section('title', 'بلیت‌های من')
@section('hero')<h1>بلیت‌های من</h1><p>با کد سفارش و ایمیلی که هنگام خرید وارد کرده‌اید، بلیت‌ها را بازیابی کنید.</p>@endsection
@section('content')
<section class="card" style="max-width:620px;margin:auto"><h2>بازیابی سفارش و بلیت‌ها</h2><form method="post" action="{{ route('tickets.lookup') }}" class="form-grid">@csrf<label class="field full"><span>کد سفارش</span><input name="reference" value="{{ old('reference') }}" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required></label><label class="field full"><span>ایمیل خریدار</span><input name="email" type="email" value="{{ old('email') }}" required></label><div><button class="btn">مشاهده بلیت‌ها</button></div></form></section>
@endsection
