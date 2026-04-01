<?php

namespace App\Http\Requests\Kyc\Concerns;

use App\Support\KycOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

trait ValidatesKycRecord
{
    /**
     * @return array<string, mixed>
     */
    protected function kycRecordRules(): array
    {
        /** @var FormRequest $this */
        return [
            'employee_name' => ['required', 'string', 'max:255'],
            'client_full_name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:16', 'max:120'],
            'passport_job_title' => ['nullable', 'string', 'max:255'],
            'other_job_title' => ['nullable', 'string', 'max:255'],
            'service_type' => ['required', Rule::in(KycOptions::SERVICE_TYPES)],
            'assigned_to' => ['nullable', 'string', 'max:255'],

            'has_bank_statement' => ['required', Rule::in(KycOptions::YES_NO)],
            'available_balance' => [
                Rule::requiredIf(fn () => $this->input('has_bank_statement') === 'نعم'),
                Rule::prohibitedIf(fn () => $this->input('has_bank_statement') === 'لا'),
                'nullable',
                'numeric',
                'min:0',
            ],
            'expected_balance' => [
                Rule::requiredIf(fn () => $this->input('has_bank_statement') === 'لا'),
                Rule::prohibitedIf(fn () => $this->input('has_bank_statement') === 'نعم'),
                'nullable',
                'numeric',
                'min:0',
            ],

            'marital_status' => ['required', Rule::in(KycOptions::MARITAL_STATUSES)],
            'children_count' => [
                Rule::requiredIf(fn () => $this->input('marital_status') === 'متزوج'),
                Rule::prohibitedIf(fn () => $this->input('marital_status') !== 'متزوج'),
                'nullable',
                'integer',
                'min:0',
                'max:50',
            ],

            'has_relatives_abroad' => ['required', Rule::in(KycOptions::YES_NO)],
            'nationality_type' => ['required', Rule::in(KycOptions::NATIONALITY_TYPES)],
            'nationality' => [
                Rule::requiredIf(fn () => $this->input('nationality_type') === 'غير مصري'),
                Rule::prohibitedIf(fn () => $this->input('nationality_type') !== 'غير مصري'),
                'nullable',
                'string',
                'max:255',
            ],
            'residency_status' => [
                Rule::requiredIf(fn () => $this->input('nationality_type') === 'غير مصري'),
                Rule::prohibitedIf(fn () => $this->input('nationality_type') !== 'غير مصري'),
                'nullable',
                'string',
                'max:255',
            ],
            'governorate' => ['nullable', 'string', 'max:255'],

            'consultation_method' => ['required', Rule::in(KycOptions::CONSULTATION_METHODS)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:32'],
            'whatsapp_number' => ['nullable', 'string', 'max:32'],

            'previous_rejected' => ['required', Rule::in(KycOptions::YES_NO)],
            'rejection_numbers' => [
                Rule::requiredIf(fn () => $this->input('previous_rejected') === 'نعم'),
                Rule::prohibitedIf(fn () => $this->input('previous_rejected') !== 'نعم'),
                'nullable',
                'string',
                'max:255',
            ],
            'rejection_reason' => [
                Rule::requiredIf(fn () => $this->input('previous_rejected') === 'نعم'),
                Rule::prohibitedIf(fn () => $this->input('previous_rejected') !== 'نعم'),
                'nullable',
                'string',
                'max:2000',
            ],
            'rejection_country' => [
                Rule::requiredIf(fn () => $this->input('previous_rejected') === 'نعم'),
                Rule::prohibitedIf(fn () => $this->input('previous_rejected') !== 'نعم'),
                'nullable',
                'string',
                'max:255',
            ],

            'has_previous_visas' => ['required', Rule::in(KycOptions::YES_NO)],
            'previous_visa_countries' => [
                Rule::requiredIf(fn () => $this->input('has_previous_visas') === 'نعم'),
                Rule::prohibitedIf(fn () => $this->input('has_previous_visas') !== 'نعم'),
                'nullable',
                'string',
                'max:2000',
            ],

            'recommendation' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(KycOptions::STATUSES)],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function kycRecordAttributes(): array
    {
        return [
            'employee_name' => 'اسم الموظف',
            'client_full_name' => 'اسم العميل',
            'age' => 'العمر',
            'passport_job_title' => 'المسمى في جواز السفر',
            'other_job_title' => 'مسمى وظيفي آخر',
            'service_type' => 'نوع الخدمة',
            'assigned_to' => 'مكلّف بمتابعة',
            'has_bank_statement' => 'كشف حساب بنكي',
            'available_balance' => 'الرصيد المتاح',
            'expected_balance' => 'الرصيد المتوقع',
            'marital_status' => 'الحالة الاجتماعية',
            'children_count' => 'عدد الأطفال',
            'has_relatives_abroad' => 'أقارب بالخارج',
            'nationality_type' => 'نوع الجنسية',
            'nationality' => 'الجنسية',
            'residency_status' => 'حالة الإقامة',
            'governorate' => 'المحافظة',
            'consultation_method' => 'طريقة الاستشارة',
            'email' => 'البريد الإلكتروني',
            'phone_number' => 'رقم الهاتف',
            'whatsapp_number' => 'واتساب',
            'previous_rejected' => 'رفض سابق',
            'rejection_numbers' => 'عدد مرات الرفض',
            'rejection_reason' => 'سبب الرفض',
            'rejection_country' => 'بلد الرفض',
            'has_previous_visas' => 'تأشيرات سابقة',
            'previous_visa_countries' => 'دول التأشيرات السابقة',
            'recommendation' => 'التوصية',
            'status' => 'الحالة',
        ];
    }
}
