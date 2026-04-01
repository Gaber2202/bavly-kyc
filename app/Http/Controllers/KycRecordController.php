<?php

namespace App\Http\Controllers;

use App\Exports\KycRecordsExport;
use App\Http\Requests\Kyc\KycRecordFilterRequest;
use App\Http\Requests\Kyc\StoreKycRecordRequest;
use App\Http\Requests\Kyc\UpdateKycRecordRequest;
use App\Models\KycRecord;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\Kyc\KycRecordQueryService;
use App\Services\Kyc\KycRecordService;
use App\Support\KycOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KycRecordController extends Controller
{
    public function __construct(
        private readonly KycRecordQueryService $kycQueries,
        private readonly KycRecordService $kycRecords,
    ) {}

    public function index(KycRecordFilterRequest $request): View
    {
        $user = $request->user();
        $filters = $request->filtersForQuery();

        $records = $this->kycQueries
            ->baseVisibleQuery($user, $filters, true)
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $employees = $user?->isAdmin()
            ? User::query()->whereIn('role', [UserRole::Admin, UserRole::Employee])->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('kyc.index', [
            'records' => $records,
            'filters' => $request->only(['q', 'status', 'service_type', 'created_by', 'date_from', 'date_to']),
            'statuses' => KycOptions::STATUSES,
            'serviceTypes' => KycOptions::SERVICE_TYPES,
            'employees' => $employees,
        ]);
    }

    public function export(KycRecordFilterRequest $request): BinaryFileResponse
    {
        $this->authorize('export', KycRecord::class);

        $user = $request->user();
        $filters = $request->filtersForQuery();

        $q = $this->kycQueries->baseVisibleQuery($user, $filters, true);

        $name = 'kyc-export-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new KycRecordsExport($q), $name);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', KycRecord::class);

        return view('kyc.create', [
            'record' => new KycRecord([
                'employee_name' => $request->user()?->name,
                'has_bank_statement' => 'لا',
                'marital_status' => 'أعزب',
                'has_relatives_abroad' => 'لا',
                'nationality_type' => 'مصري',
                'previous_rejected' => 'لا',
                'has_previous_visas' => 'لا',
                'status' => 'جديد',
            ]),
        ]);
    }

    public function store(StoreKycRecordRequest $request): RedirectResponse
    {
        $record = $this->kycRecords->create($request);

        return redirect()
            ->route('kyc.show', $record)
            ->with('toast', ['type' => 'success', 'message' => 'تم إنشاء السجل.']);
    }

    public function show(KycRecord $kyc): View
    {
        $this->authorize('view', $kyc);
        $kyc->load(['creator', 'editor']);

        return view('kyc.show', ['record' => $kyc]);
    }

    public function edit(KycRecord $kyc): View
    {
        $this->authorize('update', $kyc);

        return view('kyc.edit', ['record' => $kyc]);
    }

    public function update(UpdateKycRecordRequest $request, KycRecord $kyc): RedirectResponse
    {
        $this->kycRecords->update($kyc, $request);

        return redirect()
            ->route('kyc.show', $kyc)
            ->with('toast', ['type' => 'success', 'message' => 'تم حفظ التعديلات.']);
    }

    public function destroy(KycRecord $kyc): RedirectResponse
    {
        $this->authorize('delete', $kyc);

        $this->kycRecords->softDelete($kyc, request()->user());

        return redirect()
            ->route('kyc.index')
            ->with('toast', ['type' => 'success', 'message' => 'تم أرشفة السجل.']);
    }
}
