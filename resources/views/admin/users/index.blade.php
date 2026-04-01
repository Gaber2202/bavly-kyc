@extends('layouts.app')

@section('title', 'المستخدمون')

@section('heading', 'إدارة المستخدمين')

@section('content')
    <div class="mb-6 flex justify-between gap-3">
        <p class="text-sm text-zinc-400">إنشاء حسابات الموظفين والمديرين وضبط صلاحيات الرؤية.</p>
        <a href="{{ route('admin.users.create') }}" class="btn-gold">مستخدم جديد</a>
    </div>

    <div class="card overflow-x-auto p-0">
        @if ($users->isEmpty())
            <div class="p-10 text-center text-zinc-500">لا يوجد مستخدمون بعد.</div>
        @else
            <table class="min-w-full divide-y divide-zinc-800 text-sm">
                <thead class="bg-zinc-900/60 text-xs uppercase text-zinc-500">
                    <tr>
                        <th class="px-4 py-3 text-right">الاسم</th>
                        <th class="px-4 py-3 text-right">اسم المستخدم</th>
                        <th class="px-4 py-3 text-right">الدور</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">رؤية KYC</th>
                        <th class="px-4 py-3 text-right">تقارير</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @foreach ($users as $u)
                        <tr class="hover:bg-zinc-900/50">
                            <td class="px-4 py-3 font-medium text-white">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-zinc-400" dir="ltr">{{ $u->username }}</td>
                            <td class="px-4 py-3">{{ $u->role->label() }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-emerald-500/15 text-emerald-300' => $u->is_active,
                                    'bg-red-500/15 text-red-300' => ! $u->is_active,
                                ])>{{ $u->is_active ? 'مفعّل' : 'موقوف' }}</span>
                            </td>
                            <td class="px-4 py-3 text-zinc-400">{{ $u->can_view_all_kyc ? 'الكل' : 'سجلاته فقط' }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $u->can_view_reports ? 'نعم' : 'لا' }}</td>
                            <td class="px-4 py-3 text-left">
                                <a href="{{ route('admin.users.show', $u) }}" class="text-[#d4af37] hover:underline">عرض</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-zinc-800 px-4 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
