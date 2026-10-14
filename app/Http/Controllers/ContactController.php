<?php

namespace App\Http\Controllers;

/**
 * Halaman kontak (provisional Phase 8).
 * Info dari pengaturan global; formulir + peta menyusul di Phase 9.
 */
class ContactController extends Controller
{
    public function show(string $locale)
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        return view('contact.show', ['locale' => $locale]);
    }
}
