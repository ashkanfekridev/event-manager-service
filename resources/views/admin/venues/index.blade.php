@extends('layouts.app')
@section('title', 'مدیریت سالن‌ها')
@section('content')
<div class="section-title"><h1>مجموعه‌ها و سالن‌ها</h1><a class="btn secondary" href="{{ route('admin.dashboard') }}">داشبورد</a></div>
<section class="card"><h2>ساخت مجموعه</h2><form method="post" action="{{ route('admin.venues.store') }}" class="form-grid">@csrf<label class="field"><span>نام مجموعه</span><input name="name" required></label><label class="field"><span>شهر</span><input name="city" required></label><label class="field full"><span>آدرس</span><textarea name="address" required></textarea></label><div><button class="btn">ثبت مجموعه</button></div></form></section>
<div class="grid" style="margin-top:18px">@foreach($venues as $venue)<section class="card"><h2>{{ $venue->name }}</h2><p class="muted">{{ $venue->city }} · {{ $venue->address }}</p>@foreach($venue->halls as $hall)<p><a href="{{ route('admin.halls.show', $hall) }}">{{ $hall->name }} ({{ $hall->capacity }} صندلی)</a></p>@endforeach<form method="post" action="{{ route('admin.halls.store', $venue) }}" class="form-grid">@csrf<label class="field"><span>نام سالن جدید</span><input name="name" required></label><div style="align-self:end"><button class="btn secondary">افزودن سالن</button></div></form></section>@endforeach</div>
@endsection
