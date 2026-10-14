<?php

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;

class FacilityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function view(User $user, Facility $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function update(User $user, Facility $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function delete(User $user, Facility $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function publish(User $user, ?Facility $model = null): bool
    {
        return $user->hasPermission('content.publish');
    }
}
