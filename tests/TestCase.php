<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test tidak membutuhkan aset ter-build; hindari ketergantungan
        // pada public/build (lihat README: build dijalankan oleh composer setup).
        $this->withoutVite();
    }
}
