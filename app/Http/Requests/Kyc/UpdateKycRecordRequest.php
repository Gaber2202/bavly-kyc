<?php

namespace App\Http\Requests\Kyc;

use App\Http\Requests\Kyc\Concerns\ValidatesKycRecord;
use App\Models\KycRecord;
use App\Support\KycOptions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKycRecordRequest extends FormRequest
{
    use ValidatesKycRecord;

    public function authorize(): bool
    {
        /** @var KycRecord $kyc */
        $kyc = $this->route('kyc');

        return $this->user()?->can('update', $kyc) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $service = $this->input('service_type');
        $this->merge([
            'assigned_to' => is_string($service) ? (KycOptions::assignedToForService($service) ?? '') : '',
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
