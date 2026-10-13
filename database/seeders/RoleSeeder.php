<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed role dasar. Daftar permission menyusul di Phase 2 (RBAC).
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'administrator', 'description' => 'Akses penuh ke seluruh modul'],
            ['name' => 'editor', 'description' => 'Kelola konten, produk, galeri, dan pesan kontak'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
