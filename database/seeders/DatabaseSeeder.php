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

        // Akun admin HANYA untuk pengembangan lokal.
        // Password bawaan UserFactory ('password') — bukan kredensial produksi.
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@primainti.test',
        ]);

        $admin->roles()->sync(
            Role::where('name', 'super_admin')->firstOrFail()
        );
    }
}
