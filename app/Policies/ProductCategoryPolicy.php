<?php

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\User;

class ProductCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function view(User $user, ProductCategory $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function update(User $user, ProductCategory $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function delete(User $user, ProductCategory $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function publish(User $user, ?ProductCategory $model = null): bool
    {
        return $user->hasPermission('content.publish');
    }
}
