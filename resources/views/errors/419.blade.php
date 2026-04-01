@extends('errors.layout')

@section('title', 'انتهت الجلسة')

@section('content')
    <p class="code">419</p>
    <h1>انتهت صلاحية النموذج</h1>
    <p>لأسباب أمنية انتهت صلاحية الطلب. ارجع وحدّث الصفحة ثم أرسل النموذج مجدداً.</p>
    <a class="btn" href="{{ url()->previous() ?: url('/') }}">العودة</a>
@endsection
