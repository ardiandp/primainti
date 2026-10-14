<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;

class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function view(User $user, Menu $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function update(User $user, Menu $model): bool
    {
        return $user->hasPermission('content.manage');
    }
}
