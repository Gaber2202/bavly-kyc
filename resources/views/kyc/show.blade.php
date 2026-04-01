@extends('layouts.app')

@php
    /** @var \App\Models\KycRecord $record */
@endphp

@section('title', $record->client_full_name)

@section('heading', 'تفاصيل ملف KYC')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-zinc-500">تم الإنشاء {{ $record->created_at?->timezone(config('app.timezone'))->translatedFormat('d F Y، H:i') }}</p>
            @if ($record->updated_at !== null && ! $record->updated_at->eq($record->created_at))
                <p class="text-xs text-zinc-600">آخر تحديث {{ $record->updated_at->timezone(config('app.timezone'))->translatedFormat('d F Y، H:i') }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $record)
                <a href="{{ route('kyc.edit', $record) }}" class="btn-outline-gold">تعديل</a>
            @endcan
            @can('delete', $record)
                <button type="button" class="btn-outline-gold border-red-500/40 text-red-300 hover:border-red-400" onclick="document.getElementById('delete-kyc-modal').showModal()">
                    حذف (أرشفة)
                </button>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card space-y-3">
            <h3 class="text-sm font-semibold text-[#d4af37]">بيانات أساسية</h3>
            <dl class="grid gap-2 text-sm">
                @include('kyc._detail', ['label' => 'اسم الموظف', 'value' => $record->employee_name])
                @include('kyc._detail', ['label' => 'اسم العميل', 'value' => $record->client_full_name])
                @include('kyc._detail', ['label' => 'العمر', 'value' => $record->age])
                @include('kyc._detail', ['label' => 'المسمى في الجواز', 'value' => $record->passport_job_title])
                @include('kyc._detail', ['label' => 'مسمى آخر', 'value' => $record->other_job_title])
                @include('kyc._detail', ['label' => 'نوع الخدمة', 'value' => $record->service_type])
                @include('kyc._detail', ['label' => 'المكلّف', 'value' => $record->assigned_to])
            </dl>
        </div>

        <div class="card space-y-3">
            <h3 class="text-sm font-semibold text-[#d4af37]">مالية واجتماعية</h3>
            <dl class="grid gap-2 text-sm">
                @include('kyc._detail', ['label' => 'كشف حساب', 'value' => $record->has_bank_statement])
                @include('kyc._detail', ['label' => 'رصيد متاح', 'value' => $record->available_balance])
                @include('kyc._detail', ['label' => 'رصيد متوقع', 'value' => $record->expected_balance])
                @include('kyc._detail', ['label' => 'الحالة الاجتماعية', 'value' => $record->marital_status])
                @include('kyc._detail', ['label' => 'عدد الأطفال', 'value' => $record->children_count])
                @include('kyc._detail', ['label' => 'أقارب بالخارج', 'value' => $record->has_relatives_abroad])
                @include('kyc._detail', ['label' => 'نوع الجنسية', 'value' => $record->nationality_type])
                @include('kyc._detail', ['label' => 'الجنسية', 'value' => $record->nationality])
                @include('kyc._detail', ['label' => 'حالة الإقامة', 'value' => $record->residency_status])
                @include('kyc._detail', ['label' => 'المحافظة', 'value' => $record->governorate])
            </dl>
        </div>

        <div class="card space-y-3">
            <h3 class="text-sm font-semibold text-[#d4af37]">تواصل</h3>
            <dl class="grid gap-2 text-sm">
                @include('kyc._detail', ['label' => 'طريقة الاستشارة', 'value' => $record->consultation_method])
                @include('kyc._detail', ['label' => 'البريد', 'value' => $record->email])
                @include('kyc._detail', ['label' => 'الهاتف', 'value' => $record->phone_number])
                @include('kyc._detail', ['label' => 'واتساب', 'value' => $record->whatsapp_number])
            </dl>
        </div>

        <div class="card space-y-3">
            <h3 class="text-sm font-semibold text-[#d4af37]">تأشيرات ورفض</h3>
            <dl class="grid gap-2 text-sm">
                @include('kyc._detail', ['label' => 'رفض سابق', 'value' => $record->previous_rejected])
                @include('kyc._detail', ['label' => 'تفاصيل الرفض', 'value' => $record->rejection_numbers])
                @include('kyc._detail', ['label' => 'سبب الرفض', 'value' => $record->rejection_reason])
                @include('kyc._detail', ['label' => 'بلد الرفض', 'value' => $record->rejection_country])
                @include('kyc._detail', ['label' => 'تأشيرات سابقة', 'value' => $record->has_previous_visas])
                @include('kyc._detail', ['label' => 'دول التأشيرات', 'value' => $record->previous_visa_countries])
            </dl>
        </div>

        <div class="card space-y-3 lg:col-span-2">
            <h3 class="text-sm font-semibold text-[#d4af37]">التوصية والحالة</h3>
            <dl class="grid gap-2 text-sm">
                @include('kyc._detail', ['label' => 'التوصية', 'value' => $record->recommendation])
                @include('kyc._detail', ['label' => 'الحالة', 'value' => $record->status])
                @include('kyc._detail', ['label' => 'أنشأه', 'value' => $record->creator?->name])
                @include('kyc._detail', ['label' => 'آخر من عدّل', 'value' => $record->editor?->name])
            </dl>
        </div>
    </div>

    @can('delete', $record)
        <dialog id="delete-kyc-modal" class="w-full max-w-md rounded-xl border border-zinc-700 bg-zinc-900 p-6 text-zinc-100 shadow-2xl backdrop:bg-black/70">
            <form method="post" action="{{ route('kyc.destroy', $record) }}">
                @csrf
                @method('DELETE')
                <h4 class="text-lg font-semibold text-white">تأكيد الأرشفة</h4>
                <p class="mt-2 text-sm text-zinc-400">سيتم إخفاء السجل من القوائم مع إمكانية استعادته لاحقًا من قاعدة البيانات بواسطة المشرف.</p>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="btn-outline-gold" onclick="document.getElementById('delete-kyc-modal').close()">إلغاء</button>
                    <button type="submit" class="btn-gold bg-red-700 hover:bg-red-600">تأكيد الحذف</button>
                </div>
            </form>
        </dialog>
    @endcan
@endsection
