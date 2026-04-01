<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResetPasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $users,
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(): View
    {
        $users = User::query()->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->users->createUser($request);

        return redirect()
            ->route('admin.users.index')
            ->with('toast', ['type' => 'success', 'message' => 'تم إنشاء المستخدم.']);
    }

    public function show(User $user): View
    {
        $user->loadCount(['createdKycRecords', 'updatedKycRecords']);

        $summary = [
            'kyc_created' => $user->created_kyc_records_count,
            'kyc_updated' => $user->updated_kyc_records_count,
            'last_login' => $user->last_login_at,
        ];

        $recentActivity = ActivityLog::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.users.show', compact('user', 'summary', 'recentActivity'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->users->updateUser($request, $user);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('toast', ['type' => 'success', 'message' => 'تم حفظ التغييرات.']);
    }

    public function resetPassword(AdminResetPasswordRequest $request, User $user): RedirectResponse
    {
        $this->users->resetPassword($request, $user);

        return redirect()
            ->back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'تم إعادة تعيين كلمة المرور. يجب على المستخدم تعيين كلمة مرور جديدة عند أول دخول.',
            ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->users->deleteUser($request->user(), $user);

        return redirect()
            ->route('admin.users.index')
            ->with('toast', ['type' => 'success', 'message' => 'تم حذف المستخدم.']);
    }
}
