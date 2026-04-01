@extends('layouts.app')

@section('title', 'سجلات KYC')

@section('heading', 'سجلات اعرف عميلك')

@section('content')
    <div class="card mb-6">
        <form method="get" class="grid gap-3 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
            <div>
                <label class="label-dark">بحث</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="input-dark" placeholder="اسم / هاتف">
            </div>
            <div>
                <label class="label-dark">الحالة</label>
                <select name="status" class="input-dark">
                    <option value="">الكل</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-dark">نوع الخدمة</label>
                <select name="service_type" class="input-dark">
                    <option value="">الكل</option>
                    @foreach ($serviceTypes as $sv)
                        <option value="{{ $sv }}" @selected(($filters['service_type'] ?? '') === $sv)>{{ $sv }}</option>
                    @endforeach
                </select>
            </div>
            @if (auth()->user()?->isAdmin())
                <div>
                    <label class="label-dark">الموظف</label>
                    <select name="created_by" class="input-dark">
                        <option value="">الكل</option>
                        @foreach ($employees as $em)
                            <option value="{{ $em->id }}" @selected(($filters['created_by'] ?? '') == $em->id)>{{ $em->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="label-dark">من تاريخ</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="input-dark">
            </div>
            <div>
                <label class="label-dark">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="input-dark">
            </div>
            <div class="flex items-end gap-2 md:col-span-2">
                <button type="submit" class="btn-gold">تصفية</button>
                <a href="{{ route('kyc.index') }}" class="btn-outline-gold">مسح</a>
                @can('export', \App\Models\KycRecord::class)
                    <a href="{{ route('kyc.export', request()->query()) }}" class="btn-outline-gold">تصدير Excel</a>
                @endcan
            </div>
        </form>
    </div>

    <div class="card overflow-x-auto p-0">
        @if ($records->isEmpty())
            <div class="p-10 text-center text-zinc-400">
                لا توجد سجلات مطابقة. ابدأ بإنشاء أول ملف KYC.
            </div>
        @else
            <table class="min-w-full divide-y divide-zinc-800 text-sm">
                <thead class="bg-zinc-900/60 text-xs uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th class="px-4 py-3 text-right">العميل</th>
                        <th class="px-4 py-3 text-right">الخدمة</th>
                        <th class="px-4 py-3 text-right">المكلّف</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">الهاتف</th>
                        <th class="px-4 py-3 text-right">أنشأه</th>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @foreach ($records as $row)
                        <tr class="hover:bg-zinc-900/40">
                            <td class="px-4 py-3 font-medium text-white">{{ $row->client_full_name }}</td>
                            <td class="px-4 py-3">{{ $row->service_type }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $row->assigned_to ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-[#d4af37]">{{ $row->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-zinc-400" dir="ltr">{{ $row->phone_number }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $row->creator?->name }}</td>
                            <td class="px-4 py-3 text-zinc-500">{{ $row->created_at?->timezone(config('app.timezone'))->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-left">
                                <a href="{{ route('kyc.show', $row) }}" class="text-[#d4af37] hover:underline">عرض</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-zinc-800 px-4 py-3">
                {{ $records->links() }}
            </div>
        @endif
    </div>
@endsection
