<?php

namespace App\Http\Requests\Kyc;

use App\Http\Requests\Kyc\Concerns\ValidatesKycRecord;
use App\Support\KycOptions;
use Illuminate\Foundation\Http\FormRequest;

class StoreKycRecordRequest extends FormRequest
{
    use ValidatesKycRecord;

    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\KycRecord::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $service = $this->input('service_type');
        $this->merge([
            'assigned_to' => is_string($service) ? (KycOptions::assignedToForService($service) ?? '') : '',
            'employee_name' => $this->filled('employee_name')
                ? $this->input('employee_name')
                : $this->user()?->name,
        ]);
    }

    public function rules(): array
    {
        return $this->kycRecordRules();
    }

    public function attributes(): array
    {
        return $this->kycRecordAttributes();
    }
}
