<?php

namespace App\Policies;

use App\Models\KycRecord;
use App\Models\User;

class KycRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, KycRecord $kycRecord): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->can_view_all_kyc) {
            return true;
        }

        return (int) $kycRecord->created_by === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, KycRecord $kycRecord): bool
    {
        return $this->view($user, $kycRecord);
    }

    public function delete(User $user, KycRecord $kycRecord): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        return true;
    }

    public function restore(User $user, KycRecord $kycRecord): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, KycRecord $kycRecord): bool
    {
        return $user->isAdmin();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}
