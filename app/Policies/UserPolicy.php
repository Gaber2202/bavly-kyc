<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function view(User $actor, User $model): bool
    {
        return $actor->isAdmin();
    }

    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function update(User $actor, User $model): bool
    {
        return $actor->isAdmin();
    }

    public function delete(User $actor, User $model): bool
    {
        if (! $actor->isAdmin()) {
            return false;
        }

        return $actor->id !== $model->id;
    }

    public function resetPassword(User $actor, User $model): bool
    {
        return $actor->isAdmin() && $actor->id !== $model->id;
    }
}
