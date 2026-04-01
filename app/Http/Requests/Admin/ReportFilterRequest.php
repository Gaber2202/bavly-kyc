<?php

namespace App\Http\Requests\Admin;

use App\Support\KycOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->isAdmin() || $user->can_view_reports);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
            'service_type' => ['nullable', 'string', Rule::in(KycOptions::SERVICE_TYPES)],
        ];
    }

    /**
     * @return array{from?: string|null, to?: string|null, employee_id?: int|null, service_type?: string|null}
     */
    public function analyticsFilters(): array
    {
        $v = $this->validated();

        return [
            'from' => $v['from'] ?? null,
            'to' => $v['to'] ?? null,
            'employee_id' => isset($v['employee_id']) ? (int) $v['employee_id'] : null,
            'service_type' => $v['service_type'] ?? null,
        ];
    }
}
