@extends('layouts.app')
@section('title', 'مدیریت رویدادها')
@section('content')
<div class="section-title"><div><h1>رویدادها</h1><p class="page-subtitle">ایجاد، ویرایش و کنترل انتشار رویدادها</p></div><a class="btn" href="{{ route('admin.events.create') }}">＋ رویداد جدید</a></div>
<section class="card">
    <form method="get" class="filter-bar">
        <input name="q" value="{{ request('q') }}" placeholder="جست‌وجوی عنوان رویداد…">
        <select name="type"><option value="">همه نوع‌ها</option><option value="concert" @selected(request('type') === 'concert')>کنسرت</option><option value="theater" @selected(request('type') === 'theater')>تئاتر</option></select>
        <select name="status"><option value="">همه وضعیت‌ها</option><option value="published" @selected(request('status') === 'published')>فعال</option><option value="scheduled" @selected(request('status') === 'scheduled')>زمان‌بندی‌شده</option><option value="draft" @selected(request('status') === 'draft')>پیش‌نویس</option></select>
        <button class="btn secondary">اعمال فیلتر</button>
    </form>
</section>
<section class="card" style="margin-top:16px;padding:0"><div class="table-wrap"><table class="table"><thead><tr><th>رویداد</th><th>نوع</th><th>سانس</th><th>وضعیت انتشار</th><th>تاریخ انتشار</th><th>عملیات</th></tr></thead><tbody>
@forelse($events as $event)
@php($status = $event->publicationStatus())
<tr><td><strong>{{ $event->title }}</strong><small class="muted" style="display:block">/{{ $event->slug }}</small></td><td>{{ $event->type === 'concert' ? 'کنسرت' : 'تئاتر' }}</td><td>{{ $event->performances_count }}</td><td><span class="badge {{ $status }}">{{ ['published'=>'فعال','scheduled'=>'زمان‌بندی‌شده','draft'=>'پیش‌نویس'][$status] }}</span></td><td>{{ $event->published_at?->format('Y/m/d H:i') ?? '—' }}</td><td><div class="button-row"><a class="btn small secondary" href="{{ route('admin.events.show', $event) }}">مدیریت</a><a class="btn small secondary" href="{{ route('admin.events.edit', $event) }}">ویرایش</a><form method="post" action="{{ route('admin.events.publication.toggle', $event) }}">@csrf @method('PATCH')<button class="btn small {{ $event->isPublished() ? 'danger' : 'success' }}">{{ $event->isPublished() ? 'غیرفعال' : 'فعال‌سازی فوری' }}</button></form></div></td></tr>
@empty<tr><td colspan="6" class="muted">رویدادی با این مشخصات پیدا نشد.</td></tr>@endforelse
</tbody></table></div></section>
<div style="margin-top:18px">{{ $events->links() }}</div>
@endsection
