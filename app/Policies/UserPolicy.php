<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('user.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('user.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('user.update');
    }

    public function deactivate(User $user, User $model): bool
    {
        return $user->hasPermission('user.deactivate');
    }

    public function activate(User $user, User $model): bool
    {
        return $user->hasPermission('user.deactivate');
    }

    public function resetAccess(User $user, User $model): bool
    {
        return $user->hasPermission('user.reset_access');
    }
}
