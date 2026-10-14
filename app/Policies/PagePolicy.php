<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function view(User $user, Page $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function update(User $user, Page $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function delete(User $user, Page $model): bool
    {
        return $user->hasPermission('content.manage');
    }

    /**
     * Hak publish terpisah: Editor (interim D-15) boleh membuat/mengubah
     * draft, tetapi hanya content_admin/super_admin yang boleh publish.
     */
    public function publish(User $user, ?Page $model = null): bool
    {
        return $user->hasPermission('content.publish');
    }
}
