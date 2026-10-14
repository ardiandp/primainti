<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed data pengembangan (bukan data produksi).
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Akun admin HANYA untuk pengembangan lokal/testing.
        // Di production seeder ini MENOLAK membuat kredensial default:
        // akun admin produksi wajib dibuat manual oleh pemilik (lihat runbook).
        if ($this->isProduction()) {
            $this->command?->warn('DatabaseSeeder: akun admin dev dilewati di production.');

            return;
        }

        // Password bawaan UserFactory ('password') — bukan kredensial produksi.
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@primainti.test',
        ]);

        $admin->roles()->sync(
            Role::where('name', 'super_admin')->firstOrFail()
        );
    }

    private function isProduction(): bool
    {
        return app()->isProduction();
    }
}
