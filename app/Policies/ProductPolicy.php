<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function view(User $user, Product $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function update(User $user, Product $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function delete(User $user, Product $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function publish(User $user, ?Product $model = null): bool
    {
        return $user->hasPermission('content.publish');
    }
}
