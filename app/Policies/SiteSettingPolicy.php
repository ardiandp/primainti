<?php

namespace App\Policies;

use App\Models\SiteSetting;
use App\Models\User;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('settings.update');
    }

    public function view(User $user, SiteSetting $model): bool
    {
        return $user->hasPermission('settings.update');
    }

    public function update(User $user, ?SiteSetting $model = null): bool
    {
        return $user->hasPermission('settings.update');
    }
}
