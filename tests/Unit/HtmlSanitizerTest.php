<?php

namespace Tests\Unit;

use App\Services\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_keeps_basic_formatting_links_images_and_tables(): void
    {
        $clean = app(HtmlSanitizer::class)->clean(
            '<h2>Judul</h2><p style="text-align: center;">Halo <strong>dunia</strong></p>'
            .'<a href="https://example.com" target="_blank">Tautan</a>'
            .'<img src="/storage/media/a.jpg" alt="foto">'
            .'<table><tr><th scope="col">A</th><td>B</td></tr></table>'
        );

        $this->assertStringContainsString('<h2>Judul</h2>', $clean);
        $this->assertStringContainsString('<strong>dunia</strong>', $clean);
        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('src="/storage/media/a.jpg"', $clean);
        $this->assertStringContainsString('<table>', $clean);
    }

    public function test_strips_scripts_event_handlers_and_dangerous_urls(): void
    {
        $clean = app(HtmlSanitizer::class)->clean(
            '<p onclick="jahat()">Teks</p><script>alert(1)</script>'
            .'<a href="javascript:alert(2)">x</a><iframe src="https://evil.test"></iframe>'
        );

        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('<iframe', $clean);
        $this->assertStringContainsString('Teks', $clean);
    }

    public function test_empty_content_returns_null(): void
    {
        $sanitizer = app(HtmlSanitizer::class);

        $this->assertNull($sanitizer->clean(null));
        $this->assertNull($sanitizer->clean(''));
        $this->assertNull($sanitizer->clean('<p></p>'));
    }
}
