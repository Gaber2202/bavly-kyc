@extends('errors.layout')

@section('title', 'الصفحة غير موجودة')

@section('content')
    <p class="code">404</p>
    <h1>الصفحة غير موجودة</h1>
    <p>الرابط غير صحيح أو أُزيل. ارجع إلى لوحة التحكم أو تحقق من العنوان.</p>
    <a class="btn" href="{{ url('/') }}">الصفحة الرئيسية</a>
@endsection
