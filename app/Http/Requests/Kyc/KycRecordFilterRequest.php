<?php

namespace App\Http\Requests\Kyc;

use App\Models\KycRecord;
use App\Support\KycOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KycRecordFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        if ($this->routeIs('kyc.export')) {
            return $user->can('export', KycRecord::class);
        }

        return $user->can('viewAny', KycRecord::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(KycOptions::STATUSES)],
            'service_type' => ['nullable', 'string', Rule::in(KycOptions::SERVICE_TYPES)],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForQuery(): array
    {
        $validated = $this->validated();
        $user = $this->user();

        $createdBy = $validated['created_by'] ?? null;
        if ($createdBy !== null && $user !== null && ! $user->isAdmin()) {
            $createdBy = null;
        }

        return [
            'q' => $validated['q'] ?? null,
            'status' => $validated['status'] ?? null,
            'service_type' => $validated['service_type'] ?? null,
            'created_by' => $createdBy,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
        ];
    }
}
