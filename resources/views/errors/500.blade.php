@extends('errors.layout')

@section('title', 'خطأ في الخادم')

@section('content')
    <p class="code">500</p>
    <h1>حدث خطأ غير متوقع</h1>
    <p>نعمل على حل المشكلة. حاول مرة أخرى لاحقاً. إذا تكرّر الأمر، أبلِغ الدعم الفني.</p>
    <a class="btn" href="{{ url('/') }}">الصفحة الرئيسية</a>
@endsection
