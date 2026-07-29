@php
    $editing = isset($event);
    $currentMode = old('publication_mode', $editing ? ($event->isPublished() ? 'now' : ($event->isScheduled() ? 'scheduled' : 'draft')) : 'draft');
@endphp
<form method="post" action="{{ $editing ? route('admin.events.update', $event) : route('admin.events.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="form-grid">
        <label class="field"><span>عنوان رویداد</span><input name="title" value="{{ old('title', $event->title ?? '') }}" required></label>
        <label class="field"><span>شناسه انگلیسی URL</span><input name="slug" value="{{ old('slug', $event->slug ?? '') }}" placeholder="hamlet-1405" required></label>
        <label class="field"><span>نوع رویداد</span><select name="type"><option value="concert" @selected(old('type', $event->type ?? '') === 'concert')>کنسرت</option><option value="theater" @selected(old('type', $event->type ?? '') === 'theater')>تئاتر</option></select></label>
        <label class="field"><span>لینک پوستر</span><input name="poster_url" type="url" value="{{ old('poster_url', $event->poster_url ?? '') }}"></label>
        <label class="field"><span>مدت اجرا (دقیقه)</span><input name="duration_minutes" type="number" min="1" value="{{ old('duration_minutes', $event->duration_minutes ?? '') }}"></label>
        <label class="field"><span>محدودیت سنی</span><input name="age_limit" type="number" min="0" max="99" value="{{ old('age_limit', $event->age_limit ?? '') }}"></label>
        <label class="field full"><span>توضیحات رویداد</span><textarea name="description">{{ old('description', $event->description ?? '') }}</textarea></label>
        <div class="field full"><span>نحوه انتشار</span><div class="publication-options">
            <label class="publication-option"><input type="radio" name="publication_mode" value="draft" @checked($currentMode === 'draft')> <strong>پیش‌نویس</strong><small class="muted" style="display:block">برای کاربران نمایش داده نشود</small></label>
            <label class="publication-option"><input type="radio" name="publication_mode" value="now" @checked($currentMode === 'now')> <strong>انتشار فوری</strong><small class="muted" style="display:block">همین حالا فعال شود</small></label>
            <label class="publication-option"><input type="radio" name="publication_mode" value="scheduled" @checked($currentMode === 'scheduled')> <strong>زمان‌بندی</strong><small class="muted" style="display:block">در تاریخ مشخص فعال شود</small></label>
        </div></div>
        <label id="schedule-field" class="field full" style="display:none"><span>زمان انتشار خودکار</span><input name="scheduled_publish_at" type="datetime-local" value="{{ old('scheduled_publish_at', isset($event) && $event->isScheduled() ? $event->published_at->format('Y-m-d\TH:i') : '') }}"></label>
    </div>
    <div class="button-row" style="margin-top:20px"><button class="btn">{{ $editing ? 'ذخیره تغییرات' : 'ساخت رویداد' }}</button><a class="btn secondary" href="{{ route('admin.events.index') }}">انصراف</a></div>
</form>
@push('scripts')<script>const publicationInputs=document.querySelectorAll('[name="publication_mode"]');const scheduleField=document.getElementById('schedule-field');const syncSchedule=()=>scheduleField.style.display=document.querySelector('[name="publication_mode"]:checked')?.value==='scheduled'?'flex':'none';publicationInputs.forEach(input=>input.addEventListener('change',syncSchedule));syncSchedule();</script>@endpush
