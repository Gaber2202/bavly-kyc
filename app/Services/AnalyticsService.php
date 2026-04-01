<?php

namespace App\Services;

use App\Models\KycRecord;
use App\Models\User;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * @param  array{from?: string|null, to?: string|null, employee_id?: int|null, service_type?: string|null}  $filters
     * @return array<string, mixed>
     */
    public function dashboard(array $filters): array
    {
        $ttl = (int) config('analytics.cache_ttl', 0);

        if ($ttl > 0) {
            $key = 'kyc_analytics:'.hash('sha256', json_encode($filters, JSON_THROW_ON_ERROR));

            return Cache::remember($key, $ttl, fn (): array => $this->computeDashboard($filters));
        }

        return $this->computeDashboard($filters);
    }

    /**
     * @param  array{from?: string|null, to?: string|null, employee_id?: int|null, service_type?: string|null}  $filters
     * @return array<string, mixed>
     */
    private function computeDashboard(array $filters): array
    {
        $base = $this->filteredQuery($filters);

        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        $kpiRow = $this->kpiCounts($base, $todayStart, $weekStart, $monthStart);

        $activeEmployees = User::query()
            ->where('role', UserRole::Employee)
            ->where('is_active', true)
            ->count();

        $byService = $this->aggregateCount($base, 'service_type');
        $byStatus = $this->aggregateCount($base, 'status');
        $nationalitySplit = $this->aggregateCount($base, 'nationality_type');

        $rejectionRate = $this->ratio($base, 'previous_rejected', 'نعم');

        $avgAge = (clone $base)->whereNotNull('age')->avg('age');

        $marriedVsSingle = $this->marriedCounts($base);

        $prevVisaYes = (clone $base)->where('has_previous_visas', 'نعم')->count();
        $prevVisaNo = (clone $base)->where('has_previous_visas', 'لا')->count();
        $prevRejectYes = (clone $base)->where('previous_rejected', 'نعم')->count();
        $prevRejectNo = (clone $base)->where('previous_rejected', 'لا')->count();

        $topEmployees = $this->topCreators($base);
        $employeePerformance = $this->employeePerformance($base);

        $funnel = $this->aggregateCount($base, 'status');

        $trendDaily = $this->trend($base, 'day');
        $trendWeekly = $this->trend($base, 'week');
        $trendMonthly = $this->trend($base, 'month');

        return [
            'kpis' => [
                'total' => $kpiRow['total'],
                'today' => $kpiRow['today'],
                'week' => $kpiRow['week'],
                'month' => $kpiRow['month'],
                'active_employees' => $activeEmployees,
                'rejection_rate' => $rejectionRate,
                'avg_age' => $avgAge !== null ? round((float) $avgAge, 1) : null,
            ],
            'by_service' => $byService,
            'by_status' => $byStatus,
            'nationality_split' => $nationalitySplit,
            'married_vs_single' => $marriedVsSingle,
            'previous_visa' => ['نعم' => $prevVisaYes, 'لا' => $prevVisaNo],
            'previous_rejection' => ['نعم' => $prevRejectYes, 'لا' => $prevRejectNo],
            'top_employees' => $topEmployees,
            'employee_performance' => $employeePerformance,
            'funnel' => $funnel,
            'trend_daily' => $trendDaily,
            'trend_weekly' => $trendWeekly,
            'trend_monthly' => $trendMonthly,
        ];
    }

    /**
     * @return array{total: int, today: int, week: int, month: int}
     */
    private function kpiCounts(Builder $base, Carbon $todayStart, Carbon $weekStart, Carbon $monthStart): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $row = (clone $base)
                ->reorder()
                ->selectRaw(
                    'count(*) as total,
                    sum(case when kyc_records.created_at >= ? then 1 else 0 end) as today,
                    sum(case when kyc_records.created_at >= ? then 1 else 0 end) as week,
                    sum(case when kyc_records.created_at >= ? then 1 else 0 end) as month',
                    [$todayStart, $weekStart, $monthStart]
                )
                ->first();

            return [
                'total' => (int) ($row->total ?? 0),
                'today' => (int) ($row->today ?? 0),
                'week' => (int) ($row->week ?? 0),
                'month' => (int) ($row->month ?? 0),
            ];
        }

        return [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->where('created_at', '>=', $todayStart)->count(),
            'week' => (clone $base)->where('created_at', '>=', $weekStart)->count(),
            'month' => (clone $base)->where('created_at', '>=', $monthStart)->count(),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, int>
     */
    private function aggregateCount(Builder $base, string $column)
    {
        return (clone $base)
            ->reorder()
            ->select($column, DB::raw('count(*) as c'))
            ->groupBy($column)
            ->pluck('c', $column)
            ->map(fn ($v) => (int) $v);
    }

    /**
     * @return array{متزوج: int, غير متزوج: int}
     */
    private function marriedCounts(Builder $base): array
    {
        return [
            'متزوج' => (clone $base)->where('marital_status', 'متزوج')->count(),
            'غير متزوج' => (clone $base)->where('marital_status', '!=', 'متزوج')->count(),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, int>
     */
    private function topCreators(Builder $base)
    {
        return (clone $base)
            ->reorder()
            ->select('users.name', DB::raw('count(*) as c'))
            ->join('users', 'users.id', '=', 'kyc_records.created_by')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('c')
            ->limit(10)
            ->pluck('c', 'name')
            ->map(fn ($v) => (int) $v);
    }

    /**
     * Export-oriented performance rows (employee-level aggregates).
     *
     * @return list<array{name: string, user_id: int, submissions: int, avg_age: float|null}>
     */
    private function employeePerformance(Builder $base): array
    {
        $rows = (clone $base)
            ->reorder()
            ->select([
                'kyc_records.created_by as user_id',
                'users.name as name',
                DB::raw('count(*) as submissions'),
                DB::raw('avg(kyc_records.age) as avg_age'),
            ])
            ->join('users', 'users.id', '=', 'kyc_records.created_by')
            ->groupBy('kyc_records.created_by', 'users.name')
            ->orderByDesc('submissions')
            ->limit(50)
            ->get();

        return $rows->map(static function ($row): array {
            return [
                'user_id' => (int) $row->user_id,
                'name' => (string) $row->name,
                'submissions' => (int) $row->submissions,
                'avg_age' => $row->avg_age !== null ? round((float) $row->avg_age, 1) : null,
            ];
        })->all();
    }

    /**
     * @param  array{from?: string|null, to?: string|null, employee_id?: int|null, service_type?: string|null}  $filters
     */
    private function filteredQuery(array $filters): Builder
    {
        $q = KycRecord::query();

        if (! empty($filters['from'])) {
            $q->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $q->whereDate('created_at', '<=', $filters['to']);
        }
        if (! empty($filters['employee_id'])) {
            $q->where('created_by', (int) $filters['employee_id']);
        }
        if (! empty($filters['service_type'])) {
            $q->where('service_type', $filters['service_type']);
        }

        return $q;
    }

    private function ratio(Builder $base, string $column, string $yesValue): ?float
    {
        $total = (clone $base)->count();
        if ($total === 0) {
            return null;
        }
        $yes = (clone $base)->where($column, $yesValue)->count();

        return round(($yes / $total) * 100, 1);
    }

    /**
     * @return array<string, int>
     */
    private function trend(Builder $base, string $granularity): array
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            $format = match ($granularity) {
                'day' => '%Y-%m-%d',
                'week' => '%x-%v',
                'month' => '%Y-%m',
                default => '%Y-%m-%d',
            };

            $rows = (clone $base)
                ->reorder()
                ->select(DB::raw("DATE_FORMAT(kyc_records.created_at, '{$format}') as period"), DB::raw('count(*) as c'))
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            return $rows->pluck('c', 'period')->map(fn ($v) => (int) $v)->all();
        }

        $records = (clone $base)->select('created_at')->orderBy('created_at')->get();
        $out = [];
        foreach ($records as $r) {
            $d = Carbon::parse($r->created_at);
            $key = match ($granularity) {
                'day' => $d->format('Y-m-d'),
                'week' => $d->format('o-\WW'),
                'month' => $d->format('Y-m'),
                default => $d->format('Y-m-d'),
            };
            $out[$key] = ($out[$key] ?? 0) + 1;
        }
        ksort($out);

        return $out;
    }
}
