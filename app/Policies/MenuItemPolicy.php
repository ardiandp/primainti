<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\User;

class MenuItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function update(User $user, MenuItem $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function delete(User $user, MenuItem $model): bool
    {
        return $user->hasPermission('content.manage');
    }
}
