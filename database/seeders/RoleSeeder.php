<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Role awal sesuai PRD Phase 2.
     * Catatan: hak publish Editor masih interim (menunggu keputusan Q-01+
     * di PROJECT_DECISIONS) — saat ini hanya content_admin & super_admin.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        $roles = [
            'super_admin' => [
                'description' => 'Seluruh akses tanpa batas',
                'permissions' => Permission::all(),
            ],
            'content_admin' => [
                'description' => 'Kelola konten dan media sesuai izin',
                'permissions' => Permission::whereIn('name', [
                    'content.publish',
                    'content.manage',
                    'media.manage',
                    'contact.view',
                    'contact.update',
                ])->get(),
            ],
            'editor' => [
                'description' => 'Membuat dan mengubah draft konten',
                'permissions' => Permission::whereIn('name', [
                    'content.manage',
                ])->get(),
            ],
        ];

        foreach ($roles as $name => $role) {
            $model = Role::firstOrCreate(
                ['name' => $name],
                ['description' => $role['description']]
            );

            $model->syncPermissions($role['permissions']);
        }
    }
}
