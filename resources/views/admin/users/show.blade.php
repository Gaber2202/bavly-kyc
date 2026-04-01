@extends('layouts.app')

@section('title', $user->name)

@section('heading', 'ملف مستخدم')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-lg font-semibold text-white">{{ $user->name }}</p>
            <p class="text-sm text-zinc-500" dir="ltr">{{ $user->username }} · {{ $user->role->label() }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-outline-gold">تعديل</a>
            @if ($user->id !== auth()->id())
                <form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('حذف هذا المستخدم؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-outline-gold border-red-500/40 text-red-300">حذف</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card space-y-3">
            <h3 class="text-sm font-semibold text-[#d4af37]">ملخص النشاط</h3>
            <dl class="grid gap-2 text-sm">
                <div class="flex justify-between border-b border-zinc-800 py-1">
                    <dt class="text-zinc-500">سجلات أنشأها</dt>
                    <dd class="font-semibold text-white">{{ $summary['kyc_created'] }}</dd>
                </div>
                <div class="flex justify-between border-b border-zinc-800 py-1">
                    <dt class="text-zinc-500">سجلات حدّثها</dt>
                    <dd class="font-semibold text-white">{{ $summary['kyc_updated'] }}</dd>
                </div>
                <div class="flex justify-between py-1">
                    <dt class="text-zinc-500">آخر دخول</dt>
                    <dd class="text-zinc-200">{{ $summary['last_login']?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="card space-y-4">
            <h3 class="text-sm font-semibold text-[#d4af37]">إعادة تعيين كلمة المرور</h3>
            <p class="text-xs text-zinc-500">يتم إجبار المستخدم على تغييرها عند أول تسجيل دخول بعد الحفظ.</p>
            <form method="post" action="{{ route('admin.users.reset-password', $user) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="label-dark">كلمة المرور الجديدة</label>
                    <input type="password" name="new_password" class="input-dark" required autocomplete="new-password">
                </div>
                <div>
                    <label class="label-dark">تأكيد</label>
                    <input type="password" name="new_password_confirmation" class="input-dark" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-gold w-full sm:w-auto">تطبيق</button>
            </form>
        </div>

        <div class="card lg:col-span-2">
            <h3 class="mb-3 text-sm font-semibold text-[#d4af37]">آخر الأحداث</h3>
            @if ($recentActivity->isEmpty())
                <p class="text-sm text-zinc-500">لا سجلات نشاط بعد.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($recentActivity as $log)
                        <li class="flex flex-wrap justify-between gap-2 border-b border-zinc-800/80 py-2">
                            <span class="text-zinc-300">{{ $log->action }}</span>
                            <span class="text-xs text-zinc-600">{{ $log->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
