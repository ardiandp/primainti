<?php

namespace App\Policies;

use App\Models\GalleryItem;
use App\Models\User;

class GalleryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function view(User $user, GalleryItem $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function update(User $user, GalleryItem $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function delete(User $user, GalleryItem $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function publish(User $user, ?GalleryItem $model = null): bool
    {
        return $user->hasPermission('content.publish');
    }
}
