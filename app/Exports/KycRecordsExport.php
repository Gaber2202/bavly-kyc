<?php

namespace App\Exports;

use App\Models\KycRecord;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KycRecordsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Builder $query
    ) {}

    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * @param  KycRecord  $row
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->employee_name,
            $row->client_full_name,
            $row->age,
            $row->service_type,
            $row->assigned_to,
            $row->status,
            $row->phone_number,
            $row->created_at?->format('Y-m-d H:i'),
            $row->creator?->name,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID',
            'employee_name',
            'client_full_name',
            'age',
            'service_type',
            'assigned_to',
            'status',
            'phone_number',
            'created_at',
            'created_by',
        ];
    }
}
