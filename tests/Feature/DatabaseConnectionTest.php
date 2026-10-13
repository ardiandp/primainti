<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    /**
     * Test koneksi database: koneksi terkonfigurasi dan dapat di-query.
     */
    public function test_database_connection_is_working(): void
    {
        $result = DB::select('select 1 as ok');

        $this->assertSame(1, (int) $result[0]->ok);
    }

    /**
     * Koneksi menyediakan nama driver yang valid.
     */
    public function test_database_driver_is_configured(): void
    {
        $default = (string) config('database.default');

        $this->assertNotEmpty($default);
        $this->assertNotEmpty(config("database.connections.{$default}.driver"));
    }
}
