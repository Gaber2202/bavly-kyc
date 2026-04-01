<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\AnalyticsService;
use App\Support\KycOptions;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics
    ) {}

    public function dashboard(ReportFilterRequest $request): View
    {
        $filters = $request->analyticsFilters();

        if ($request->user() !== null && ! $request->user()->isAdmin()) {
            $filters['employee_id'] = $request->user()->id;
        }

        $data = $this->analytics->dashboard($filters);

        $employees = User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::Employee])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.reports.dashboard', [
            'data' => $data,
            'employees' => $employees,
            'filters' => $filters,
            'serviceTypes' => KycOptions::SERVICE_TYPES,
        ]);
    }
}
