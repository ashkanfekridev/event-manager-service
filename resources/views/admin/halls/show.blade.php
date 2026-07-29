@extends('layouts.app')
@section('title', $hall->name)
@section('content')
<div class="section-title"><div><h1>{{ $hall->name }}</h1><p class="muted">{{ $hall->venue->name }} · ظرفیت {{ $hall->capacity }}</p></div><a class="btn secondary" href="{{ route('admin.venues.index') }}">بازگشت</a></div>
<section class="card"><h2>افزودن چیدمان صندلی</h2><form method="post" action="{{ route('admin.seats.store', $hall) }}" class="form-grid">@csrf<label class="field"><span>نام بخش</span><input name="section" value="main" required></label><label class="field"><span>تعداد ردیف</span><input name="rows" type="number" min="1" max="26" required></label><label class="field"><span>صندلی در هر ردیف</span><input name="seats_per_row" type="number" min="1" max="100" required></label><label class="field"><span>نوع</span><select name="type"><option value="standard">عادی</option><option value="vip">VIP</option><option value="wheelchair">ویلچر</option></select></label><div><button class="btn">ساخت صندلی‌ها</button></div></form></section>
<section class="card" style="margin-top:18px"><h2>چیدمان فعلی</h2><div class="screen">صحنه</div><div class="seat-map">@forelse($hall->seats as $seat)<span class="seat">{{ $seat->code }}</span>@empty<span class="muted">هنوز صندلی تعریف نشده است.</span>@endforelse</div></section>
@endsection
