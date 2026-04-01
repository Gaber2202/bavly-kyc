@extends('layouts.app')

@section('title', 'مستخدم جديد')

@section('heading', 'إضافة مستخدم')

@section('content')
    <div class="card max-w-3xl">
        <form method="post" action="{{ route('admin.users.store') }}" class="grid gap-5 md:grid-cols-2">
            @csrf

            <div class="md:col-span-2">
                <label class="label-dark">الاسم الكامل</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input-dark" required>
            </div>
            <div>
                <label class="label-dark">اسم المستخدم</label>
                <input type="text" name="username" value="{{ old('username') }}" class="input-dark" required autocomplete="off" dir="ltr">
            </div>
            <div>
                <label class="label-dark">البريد (اختياري)</label>
                <input type="email" name="email" value="{{ old('email') }}" class="input-dark" dir="ltr">
            </div>
            <div>
                <label class="label-dark">كلمة المرور</label>
                <input type="password" name="password" class="input-dark" required autocomplete="new-password">
            </div>
            <div>
                <label class="label-dark">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" class="input-dark" required autocomplete="new-password">
            </div>
            <div>
                <label class="label-dark">الدور</label>
                <select name="role" class="input-dark" required>
                    @foreach (\App\Enums\UserRole::cases() as $r)
                        <option value="{{ $r->value }}" @selected(old('role') === $r->value)>{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 grid gap-3 sm:grid-cols-3">
                <label class="flex items-center gap-2 text-sm text-zinc-300">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-zinc-600 bg-zinc-900 text-[#d4af37]" @checked(old('is_active', true))>
                    حساب مفعّل
                </label>
                <label class="flex items-center gap-2 text-sm text-zinc-300">
                    <input type="hidden" name="can_view_all_kyc" value="0">
                    <input type="checkbox" name="can_view_all_kyc" value="1" class="rounded border-zinc-600 bg-zinc-900 text-[#d4af37]" @checked(old('can_view_all_kyc'))>
                    رؤية كل سجلات KYC
                </label>
                <label class="flex items-center gap-2 text-sm text-zinc-300">
                    <input type="hidden" name="can_view_reports" value="0">
                    <input type="checkbox" name="can_view_reports" value="1" class="rounded border-zinc-600 bg-zinc-900 text-[#d4af37]" @checked(old('can_view_reports'))>
                    صلاحية التقارير
                </label>
            </div>

            <div class="md:col-span-2 flex gap-3">
                <button type="submit" class="btn-gold">حفظ</button>
                <a href="{{ route('admin.users.index') }}" class="btn-outline-gold">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
