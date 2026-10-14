<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_locale_home(): void
    {
        $this->get('/')->assertRedirect('/'.config('app.locale'));
    }

    public function test_locale_home_returns_successful_response(): void
    {
        $this->get('/id')->assertOk();
        $this->get('/en')->assertOk();
    }
}
