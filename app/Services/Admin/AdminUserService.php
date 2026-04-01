<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Http\Requests\Admin\AdminResetPasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\PasswordResetLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminUserService
{
    public function createUser(StoreUserRequest $request): User
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data) {
            $user = new User;
            $user->name = $data['name'];
            $user->username = $data['username'];
            $user->email = $data['email'] ?? null;
            $user->password = $data['password'];
            $user->role = $data['role'];
            $user->is_active = $request->boolean('is_active', true);
            $user->can_view_all_kyc = $request->boolean('can_view_all_kyc');
            $user->can_view_reports = $request->boolean('can_view_reports');
            $user->must_change_password = false;
            $user->save();

            ActivityLogger::log($request->user(), 'admin.user_created', $user, [
                'role' => $user->role->value,
                'username' => $user->username,
            ]);

            return $user;
        });
    }

    public function updateUser(UpdateUserRequest $request, User $user): User
    {
        $actor = $request->user();
        $incomingRole = $request->enum('role', UserRole::class);

        if ($user->id === Auth::id() && $incomingRole !== UserRole::Admin) {
            abort(403, 'Cannot demote own admin account.');
        }

        $previousRole = $user->role;

        return DB::transaction(function () use ($request, $user, $actor, $previousRole) {
            $data = $request->safe()->except('password')->toArray();

            $user->fill($data);

            if ($request->filled('password')) {
                $user->password = $request->validated('password');
            }

            $user->is_active = $request->boolean('is_active');
            $user->can_view_all_kyc = $request->boolean('can_view_all_kyc');
            $user->can_view_reports = $request->boolean('can_view_reports');
            $user->save();

            $properties = [
                'is_active' => $user->is_active,
                'can_view_all_kyc' => $user->can_view_all_kyc,
                'can_view_reports' => $user->can_view_reports,
            ];

            if ($previousRole !== $user->role) {
                $properties['role_before'] = $previousRole->value;
                $properties['role_after'] = $user->role->value;
            }

            ActivityLogger::log($actor, 'admin.user_updated', $user, $properties);

            return $user->fresh();
        });
    }

    public function resetPassword(AdminResetPasswordRequest $request, User $target): void
    {
        DB::transaction(function () use ($request, $target) {
            $target->password = $request->string('new_password')->toString();
            $target->must_change_password = true;
            $target->save();

            PasswordResetLog::query()->create([
                'target_user_id' => $target->id,
                'reset_by_user_id' => $request->user()->id,
                'temporary_password_issued' => true,
            ]);

            ActivityLogger::log($request->user(), 'admin.password_reset', $target, [
                'target_username' => $target->username,
            ]);
        });
    }

    public function deleteUser(User $actor, User $target): void
    {
        if ($target->id === $actor->id) {
            abort(403, 'Cannot delete self.');
        }

        DB::transaction(function () use ($actor, $target) {
            ActivityLogger::log($actor, 'admin.user_deleted', $target, [
                'username' => $target->username,
            ]);

            $target->delete();
        });
    }
}
