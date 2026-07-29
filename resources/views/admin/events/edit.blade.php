@extends('layouts.app')
@section('title', 'ویرایش '.$event->title)
@section('content')
<div class="section-title"><div><h1>ویرایش رویداد</h1><p class="page-subtitle">{{ $event->title }}</p></div><a class="btn secondary" href="{{ route('admin.events.show', $event) }}">بازگشت به رویداد</a></div>
<section class="card">@include('admin.events._form')</section>
@endsection
