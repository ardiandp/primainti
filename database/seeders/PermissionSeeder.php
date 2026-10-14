<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Daftar permission seluruh modul. Permission content/media/settings
     * mulai dipakai saat modulnya dibangun (Phase 4+).
     */
    public function run(): void
    {
        $permissions = [
            // User management (Phase 2)
            'user.view',
            'user.create',
            'user.update',
            'user.deactivate',
            'user.reset_access',
            // Role management (Phase 2)
            'role.view',
            'role.create',
            'role.update',
            'role.delete',
            // Konten (dipakai Phase 4+)
            'content.publish',
            'content.manage',
            // Media (dipakai Phase 3+)
            'media.manage',
            // Pengaturan situs (dipakai Phase 7+)
            'settings.update',
            // Pesan kontak (dipakai Phase 9+)
            'contact.view',
            'contact.update',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }
}