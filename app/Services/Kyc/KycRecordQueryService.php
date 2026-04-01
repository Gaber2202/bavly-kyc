<?php

namespace App\Services\Kyc;

use App\Models\KycRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class KycRecordQueryService
{
    /**
     * @param  array{q?: string|null, status?: string|null, service_type?: string|null, created_by?: int|null, date_from?: string|null, date_to?: string|null}  $filters
     */
    public function applyFilters(Builder $query, User $viewer, array $filters): Builder
    {
        $query->search($filters['q'] ?? null);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        if (! empty($filters['created_by']) && $viewer->isAdmin()) {
            $query->where('created_by', (int) $filters['created_by']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function baseVisibleQuery(User $viewer, array $filters, bool $withCreator = true): Builder
    {
        $q = KycRecord::query()->visibleToUser($viewer);

        if ($withCreator) {
            $q->with(['creator']);
        }

        return $this->applyFilters($q, $viewer, $filters);
    }
}
