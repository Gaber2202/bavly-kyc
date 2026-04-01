@extends('layouts.app')

@section('title', 'تغيير كلمة المرور')

@section('heading', 'تعيين كلمة مرور جديدة')

@section('content')
    <div class="card max-w-lg">
        <p class="text-sm text-zinc-400">لأسباب أمنية يجب تعيين كلمة مرور جديدة قبل متابعة استخدام النظام.</p>

        <form method="post" action="{{ route('password.force.update') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label class="label-dark" for="password">كلمة المرور الجديدة</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="input-dark">
                <p class="mt-1 text-xs text-zinc-500">12 أحرف على الأقل مع أحرف كبيرة وصغيرة وأرقام ورموز.</p>
                @error('password')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label-dark" for="password_confirmation">تأكيد كلمة المرور</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input-dark">
            </div>

            <button type="submit" class="btn-gold w-full">حفظ ومتابعة</button>
        </form>
    </div>
@endsection
