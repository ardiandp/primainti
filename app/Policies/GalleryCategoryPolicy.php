<?php

namespace App\Policies;

use App\Models\GalleryCategory;
use App\Models\User;

class GalleryCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function view(User $user, GalleryCategory $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function update(User $user, GalleryCategory $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function delete(User $user, GalleryCategory $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function publish(User $user, ?GalleryCategory $model = null): bool
    {
        return $user->hasPermission('content.publish');
    }
}
