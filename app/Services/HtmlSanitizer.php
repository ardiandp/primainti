<?php

namespace App\Services;

use Mews\Purifier\Facades\Purifier;

/**
 * Sanitasi HTML konten CMS (output rich text editor).
 *
 * Dipakai saat PENYIMPANAN agar database hanya berisi HTML aman;
 * render memakai output escaped-blade {!! !!} karena sudah bersih.
 * Jangan pernah render HTML user tanpa lewat service ini.
 */
class HtmlSanitizer
{
    public function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $cleaned = Purifier::clean($html, 'cms_content');

        return trim((string) $cleaned) === '' ? null : (string) $cleaned;
    }
}
