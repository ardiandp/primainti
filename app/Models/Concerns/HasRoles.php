<?php

namespace App\Models\Concerns;

use App\Models\Role;
use Illuminate\Support\Collection;

/**
 * Trait RBAC sederhana berbasis tabel roles/permissions.
 * Pengecekan lewat Gate otomatis di- bypass untuk role super_admin
 * (lihat AppServiceProvider::boot).
 */
trait HasRoles
{
    public function assignRole(Role ...$roles): static
    {
        $this->roles()->syncWithoutDetaching($roles->pluck('id'));

        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->roles()->detach($role);

        return $this;
    }

    /**
     * @param  iterable<Role>  $roles
     */
    public function syncRoles(iterable $roles): static
    {
        $this->roles()->sync(
            collect($roles)->map(fn (Role $role) => $role->getKey())->all()
        );

        return $this;
    }

    /**
     * @param  string|array<int, string>  $roles  Nama role (OR logic bila array)
     */
    public function hasRole(string|array $roles): bool
    {
        $names = is_array($roles) ? $roles : [$roles];

        return $this->roles->pluck('name')->intersect($names)->isNotEmpty();
    }

    /** @return Collection<int, string> */
    public function roleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->roles
            ->loadMissing('permissions')
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->contains($permission);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}
