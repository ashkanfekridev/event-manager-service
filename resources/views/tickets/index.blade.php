@extends('layouts.app')

@section('title', 'پیگیری خرید و دریافت بلیت | رویدادینو')

@section('hero')
    <div class="compact-hero">
        <span class="eyebrow eyebrow--light"><i></i> دسترسی سریع</span>
        <h1>بلیت‌های من</h1>
        <p>سفارش و بلیت‌هایتان را با کد پیگیری و ایمیل زمان خرید بازیابی کنید.</p>
    </div>
@endsection

@section('content')
    <div class="lookup-layout">
        <section class="lookup-card">
            <div class="lookup-card__icon">◎</div>
            <div><span class="section-kicker">پیگیری سفارش</span><h2>دریافت دوباره بلیت‌ها</h2><p>اطلاعات زیر باید دقیقاً با اطلاعات ثبت‌شده هنگام خرید یکسان باشد.</p></div>
            <form method="post" action="{{ route('tickets.lookup') }}" class="form-grid">
                @csrf
                <label class="field full"><span>کد پیگیری سفارش</span><input name="reference" value="{{ old('reference') }}" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" dir="ltr" required></label>
                <label class="field full"><span>ایمیل خریدار</span><input name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com" dir="ltr" required></label>
                <button class="btn btn--wide">مشاهده سفارش و بلیت‌ها</button>
            </form>
        </section>
        <aside class="lookup-help">
            <span>؟</span>
            <h3>کد پیگیری را کجا پیدا کنم؟</h3>
            <p>این کد بلافاصله پس از پرداخت در صفحه موفقیت نمایش داده می‌شود و همراه اطلاعات سفارش قابل نگهداری است.</p>
            <ul><li>ایمیل را با حروف انگلیسی وارد کنید.</li><li>کد پیگیری شامل خط تیره‌ها نیز هست.</li><li>لینک نمایش بلیت تا ۳۰ دقیقه معتبر است.</li></ul>
        </aside>
    </div>
@endsection
