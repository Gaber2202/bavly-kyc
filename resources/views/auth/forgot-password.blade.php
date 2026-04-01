@extends('layouts.guest')

@section('title', 'استعادة الوصول')

@section('content')
    <h2 class="text-xl font-bold text-white">استعادة الوصول</h2>
    <p class="mt-3 text-sm leading-relaxed text-zinc-400">
        يتم إدارة كلمات المرور داخليًا لأسباب أمنية. إذا نسيت كلمة المرور، تواصل مع مشرف النظام لإعادة التعيين.
        سيتم إجبارك على اختيار كلمة مرور جديدة عند دخولك لأول مرة بعد إعادة التعيين.
    </p>

    <div class="mt-8 space-y-3">
        <a href="{{ route('login') }}" class="btn-outline-gold block w-full text-center">العودة لتسجيل الدخول</a>
    </div>
@endsection
