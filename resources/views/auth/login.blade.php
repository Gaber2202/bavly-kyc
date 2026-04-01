@extends('layouts.guest')

@section('title', 'تسجيل الدخول')

@section('content')
    <h2 class="text-xl font-bold text-white">تسجيل الدخول</h2>
    <p class="mt-1 text-sm text-zinc-400">استخدم اسم المستخدم وكلمة المرور الصادرة من الإدارة.</p>

    <form method="post" action="{{ route('login.store') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label class="label-dark" for="username">اسم المستخدم</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus autocomplete="username" class="input-dark">
            @error('username')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="label-dark" for="password">كلمة المرور</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="input-dark">
            @error('password')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-zinc-300">
            <input type="checkbox" name="remember" value="1" class="rounded border-zinc-600 bg-zinc-900 text-[#d4af37] focus:ring-[#d4af37]">
            تذكرني على هذا الجهاز
        </label>

        <button type="submit" class="btn-gold w-full">دخول</button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-400">
        <a href="{{ route('password.request') }}" class="text-[#d4af37] hover:underline">نسيت كلمة المرور؟</a>
    </p>
@endsection
