<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gerbang smoke pra-deploy (lihat docs/deployment/launch-runbook.md §6).
 * Berjalan di database kosong: mensimulasikan instalasi segar.
 */
class DeploySmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_respond_on_empty_database(): void
    {
        $this->get('/')->assertRedirect('/'.config('app.locale'));

        foreach ([
            '/id', '/en',
            '/id/produk', '/id/galeri', '/id/fasilitas', '/id/kontak',
            '/sitemap.xml', '/robots.txt',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_login_is_reachable_and_guarded(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_dev_admin_seed_is_skipped_in_production(): void
    {
        $this->app['env'] = 'production';

        (new DatabaseSeeder)->run();

        // Role tetap di-seed (otorisasi butuh), tanpa kredensial default.
        $this->assertNotNull(Role::where('name', 'super_admin')->first());
        $this->assertNull(User::where('email', 'admin@primainti.test')->first());
        $this->assertEquals(0, User::count());
    }

    public function test_dev_admin_seed_exists_outside_production(): void
    {
        (new DatabaseSeeder)->run();

        $admin = User::where('email', 'admin@primainti.test')->firstOrFail();
        $this->assertTrue($admin->hasRole('super_admin'));
    }
}
