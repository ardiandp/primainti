<?php

namespace App\Http\Controllers;

use App\Models\Facility;

/**
 * Halaman fasilitas publik (provisional Phase 6; disempurnakan Phase 8).
 * Hanya fasilitas live, terurut. Klaim/lokasi tak terverifikasi tidak tampil
 * karena memang tidak ada kolomnya sebelum disetujui pemilik.
 */
class FacilityShowController extends Controller
{
    public function index(string $locale)
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        $facilities = Facility::live()
            ->with(['translations', 'media.asset'])
            ->ordered()
            ->paginate(10);

        return view('facilities.index', [
            'locale' => $locale,
            'facilities' => $facilities,
        ]);
    }
}
