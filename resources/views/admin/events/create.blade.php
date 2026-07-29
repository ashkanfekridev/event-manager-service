@extends('layouts.app')
@section('title', 'ساخت رویداد جدید')
@section('content')
<div class="section-title"><div><h1>رویداد جدید</h1><p class="page-subtitle">اطلاعات رویداد و زمان انتشار را مشخص کنید.</p></div></div>
<section class="card">@include('admin.events._form')</section>
@endsection
