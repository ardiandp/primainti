<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('contact.view');
    }

    public function view(User $user, ContactMessage $model): bool
    {
        return $user->hasPermission('contact.view');
    }

    public function update(User $user, ContactMessage $model): bool
    {
        return $user->hasPermission('contact.update');
    }
}
