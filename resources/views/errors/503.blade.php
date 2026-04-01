@extends('errors.layout')

@section('title', 'صيانة مؤقتة')

@section('content')
    <p class="code">503</p>
    <h1>الخدمة غير متاحة مؤقتاً</h1>
    <p>جارٍ إجراء صيانة أو تحديث. يرجى المحاولة بعد قليل.</p>
    <a class="btn" href="{{ url('/') }}">إعادة المحاولة</a>
@endsection
