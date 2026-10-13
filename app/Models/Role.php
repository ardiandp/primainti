<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'description'])]
class Role extends Model
{
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @param  iterable<Permission>  $permissions
     */
    public function syncPermissions(iterable $permissions): static
    {
        $this->permissions()->sync(
            collect($permissions)->map(fn (Permission $permission) => $permission->getKey())->all()
        );

        return $this;
    }
}
