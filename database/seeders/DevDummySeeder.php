<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\Facility;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\Media\MediaUploader;
use App\Services\MenuService;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/**
 * Data DUMMY untuk pengembangan lokal agar website terlihat real.
 *
 * - HANYA untuk environment local/testing. DITOLAK di production.
 * - Seluruh nama, spesifikasi, alamat, dan kontak di bawah ini FIKTIF.
 * - Gambar diunduh dari picsum.photos (atau placeholder GD bila offline).
 * - Dijalankan eksplisit: php artisan db:seed --class=DevDummySeeder
 * - Idempoten: berhenti bila dummy sudah pernah di-seed.
 */
class DevDummySeeder extends Seeder
{
    private ?User $admin = null;

    /** @var array<string, MediaAsset> */
    private array $pool = [];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('DevDummySeeder DITOLAK di production.');

            return;
        }

        if (\App\Models\PageTranslation::where('locale', 'id')->where('slug', 'tentang-kami')->exists()) {
            $this->command?->info('Dummy sudah pernah di-seed, dilewati.');

            return;
        }

        $this->admin = User::where('email', 'admin@primainti.test')->first();

        $this->seedMediaPool();
        $this->seedCategories();
        $this->seedProducts();
        $this->seedFacilities();
        $this->seedGallery();
        $this->seedPages();
        $this->seedMenus();
        $this->seedSettings();
        $this->seedMessages();

        $this->command?->info('DevDummySeeder selesai: konten fiktif untuk preview lokal.');
    }

    // --- Media ---

    private function seedMediaPool(): void
    {
        foreach ([
            'plant' => 'pabrik.jpg',
            'pump' => 'pompa.jpg',
            'valve' => 'katup.jpg',
            'pipe' => 'pipa.jpg',
            'warehouse' => 'gudang.jpg',
            'lab' => 'lab.jpg',
            'team' => 'tim.jpg',
            'tank' => 'tangki.jpg',
        ] as $seed => $name) {
            $this->pool[$seed] = $this->makeImageAsset('primanti-'.$seed, $name);
        }
    }

    private function makeImageAsset(string $seed, string $name, ?string $title = null): MediaAsset
    {
        $tmp = $this->fetchImage($seed);

        if ($tmp !== null) {
            try {
                return (new MediaUploader)->upload(new UploadedFile($tmp, $name, 'image/jpeg', null, true), $this->admin);
            } catch (\Throwable) {
                // jatuh ke placeholder lokal
            } finally {
                @unlink($tmp);
            }
        }

        $tmp = $this->gdPlaceholder($name);

        try {
            return (new MediaUploader)->upload(new UploadedFile($tmp, $name, 'image/jpeg', null, true), $this->admin, $title);
        } finally {
            @unlink($tmp);
        }
    }

    private function fetchImage(string $seed): ?string
    {
        try {
            $body = Http::timeout(15)->get("https://picsum.photos/seed/{$seed}/800/600")->body();

            if (strlen($body) < 10000) {
                return null;
            }

            $tmp = tempnam(sys_get_temp_dir(), 'dummy').'.jpg';
            file_put_contents($tmp, $body);

            if (@getimagesize($tmp) === false) {
                @unlink($tmp);

                return null;
            }

            return $tmp;
        } catch (\Throwable) {
            return null;
        }
    }

    private function gdPlaceholder(string $name): string
    {
        $img = imagecreatetruecolor(800, 600);
        imagefill($img, 0, 0, imagecolorallocate($img, 15, 42, 84));
        imagestring($img, 5, 330, 290, 'DUMMY', imagecolorallocate($img, 249, 115, 22));
        imagestring($img, 3, 290, 315, substr($name, 0, 24), imagecolorallocate($img, 255, 255, 255));

        $tmp = tempnam(sys_get_temp_dir(), 'dummy').'.jpg';
        imagejpeg($img, $tmp, 80);
        imagedestroy($img);

        return $tmp;
    }

    private function makePdfAsset(string $name, string $title): MediaAsset
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dummy').'.pdf';
        file_put_contents($tmp, $this->dummyPdfBytes($title));

        try {
            return (new MediaUploader)->upload(new UploadedFile($tmp, $name, 'application/pdf', null, true), $this->admin, $title);
        } finally {
            @unlink($tmp);
        }
    }

    private function dummyPdfBytes(string $title): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $title.' - DUMMY, bukan dokumen resmi');
        $stream = "BT /F1 16 Tf 40 120 Td ({$text}) Tj ET";
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 400 180] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            4 => '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $n => $body) {
            $offsets[$n] = strlen($pdf);
            $pdf .= "{$n} 0 obj\n{$body}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $off) {
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    // --- Katalog ---

    /** @var array<string, ProductCategory> */
    private array $categories = [];

    private function seedCategories(): void
    {
        foreach ([
            ['Pompa Industri', 'pompa-industri', 'Industrial Pumps', 'industrial-pumps'],
            ['Katup & Valve', 'katup-valve', 'Valves', 'valves'],
            ['Sistem Perpipaan', 'sistem-perpipaan', 'Piping Systems', 'piping-systems'],
        ] as [$id, $slugId, $en, $slugEn]) {
            $category = ProductCategory::create(['status' => 'published']);
            $category->translations()->create(['locale' => 'id', 'name' => $id, 'slug' => $slugId]);
            $category->translations()->create(['locale' => 'en', 'name' => $en, 'slug' => $slugEn]);
            $this->categories[$slugId] = $category;
        }
    }

    private function seedProducts(): void
    {
        $datasheet = $this->makePdfAsset('datasheet-ps-200.pdf', 'Datasheet PS-200');

        $rows = [
            [
                'cat' => 'pompa-industri', 'sku' => 'PS-200', 'featured' => true, 'order' => 0,
                'id' => ['Pompa Sentrifugal PS-200', 'pompa-sentrifugal-ps-200', 'Pompa sentrifugal andal untuk air bersih industri.'],
                'en' => ['Centrifugal Pump PS-200', 'centrifugal-pump-ps-200', 'Reliable centrifugal pump for industrial clean water.'],
                'specs' => [['Debit Maksimum', '200', 'm³/h'], ['Daya Motor', '15', 'kW'], ['Material Casing', 'Cast Iron', null], ['Koneksi', '4', 'inch']],
                'media' => ['pump', 'plant'],
                'docs' => [['datasheet', 'Datasheet PS-200', $datasheet]],
            ],
            [
                'cat' => 'pompa-industri', 'sku' => 'SP-75', 'featured' => true, 'order' => 1,
                'id' => ['Pompa Celup SP-75', 'pompa-celup-sp-75', 'Pompa celup untuk drainase dan air kotor ringan.'],
                'en' => ['Submersible Pump SP-75', 'submersible-pump-sp-75', 'Submersible pump for drainage and light wastewater.'],
                'specs' => [['Daya', '7,5', 'kW'], ['Kedalaman Maksimum', '30', 'm'], ['Tegangan', '380', 'V']],
                'media' => ['tank', 'pump'],
                'docs' => [],
            ],
            [
                'cat' => 'pompa-industri', 'sku' => 'DK-10', 'featured' => false, 'order' => 2,
                'id' => ['Pompa Dosing DK-10', 'pompa-dosing-dk-10', 'Pompa dosing presisi untuk bahan kimia.'],
                'en' => ['Dosing Pump DK-10', 'dosing-pump-dk-10', 'Precision dosing pump for chemicals.'],
                'specs' => [['Laju Alir', '10', 'L/h'], ['Tekanan Maksimum', '12', 'bar']],
                'media' => ['lab'],
                'docs' => [],
            ],
            [
                'cat' => 'katup-valve', 'sku' => 'GV-150', 'featured' => true, 'order' => 0,
                'id' => ['Gate Valve GV-150', 'gate-valve-gv-150', 'Gate valve bodi ductile iron untuk isolasi aliran.'],
                'en' => ['Gate Valve GV-150', 'gate-valve-gv-150', 'Ductile iron gate valve for flow isolation.'],
                'specs' => [['Diameter Nominal', '150', 'DN'], ['Tekanan', 'PN16', null], ['Material Bodi', 'Ductile Iron', null]],
                'media' => ['valve', 'plant'],
                'docs' => [],
            ],
            [
                'cat' => 'katup-valve', 'sku' => 'BV-50', 'featured' => false, 'order' => 1,
                'id' => ['Ball Valve BV-50', 'ball-valve-bv-50', 'Ball valve kompak seperempat putaran.'],
                'en' => ['Ball Valve BV-50', 'ball-valve-bv-50', 'Compact quarter-turn ball valve.'],
                'specs' => [['Ukuran', '2', 'inch'], ['Tekanan Kerja', '40', 'bar']],
                'media' => ['valve'],
                'docs' => [],
            ],
            [
                'cat' => 'sistem-perpipaan', 'sku' => 'HDPE-110', 'featured' => false, 'order' => 0,
                'id' => ['Pipa HDPE PN16', 'pipa-hdpe-pn16', 'Pipa HDPE untuk air bersih dan irigasi.'],
                'en' => ['HDPE Pipe PN16', 'hdpe-pipe-pn16', 'HDPE pipe for clean water and irrigation.'],
                'specs' => [['Diameter Luar', '110', 'mm'], ['Tekanan', '16', 'bar'], ['Panjang per Batang', '6', 'm']],
                'media' => ['pipe', 'warehouse'],
                'docs' => [],
            ],
            [
                'cat' => 'sistem-perpipaan', 'sku' => 'ELB-90-4', 'featured' => false, 'order' => 1,
                'id' => ['Elbow 90° CS 4 inch', 'elbow-90-4-inch', 'Sambungan belokan carbon steel.'],
                'en' => ['90° CS Elbow 4 inch', '90-cs-elbow-4-inch', 'Carbon steel elbow fitting.'],
                'specs' => [['Ukuran', '4', 'inch'], ['Standar', 'ASTM A234', null], ['Kelas Tekanan', '150', 'class']],
                'media' => ['pipe'],
                'docs' => [],
            ],
        ];

        foreach ($rows as $row) {
            $product = Product::create([
                'product_category_id' => $this->categories[$row['cat']]->id,
                'sku' => $row['sku'],
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'is_featured' => $row['featured'],
                'sort_order' => $row['order'],
            ]);

            foreach (['id', 'en'] as $locale) {
                [$name, $slug, $short] = $row[$locale];
                $product->translations()->create([
                    'locale' => $locale,
                    'name' => $name,
                    'slug' => $slug,
                    'short_description' => $short,
                    'description' => "<p>{$short} Spesifikasi lengkap tercantum pada tabel teknis di bawah.</p>",
                    'functions' => '<p>Menyalurkan fluida secara efisien dengan perawatan mudah.</p>',
                    'applications' => '<p>Industri umum, air bersih, dan irigasi.</p>',
                    'seo_title' => "{$name} | Primainti",
                    'seo_description' => $short,
                ]);
            }

            foreach ($row['specs'] as $i => [$label, $value, $unit]) {
                $spec = $product->specifications()->create(['sort_order' => $i, 'unit' => $unit]);
                $spec->translations()->create(['locale' => 'id', 'label' => $label, 'value' => $value]);
                $spec->translations()->create(['locale' => 'en', 'label' => $label, 'value' => $value]);
            }

            foreach ($row['media'] as $i => $key) {
                $product->media()->create([
                    'media_asset_id' => $this->pool[$key]->id,
                    'position' => $i,
                    'is_primary' => $i === 0,
                ]);
            }

            foreach ($row['docs'] as $i => [$type, $label, $asset]) {
                $product->documents()->create([
                    'media_asset_id' => $asset->id,
                    'type' => $type,
                    'label' => $label,
                    'position' => $i,
                ]);
            }
        }
    }

    // --- Fasilitas & galeri ---

    private function seedFacilities(): void
    {
        $rows = [
            ['Pabrik & Workshop', 'pabrik-workshop', 'Plant & Workshop', 'plant-workshop', 'Area produksi dan perakitan.', ['plant', 'team']],
            ['Gudang & Logistik', 'gudang-logistik', 'Warehouse & Logistics', 'warehouse-logistics', 'Penyimpanan dan distribusi barang.', ['warehouse', 'tank']],
            ['Lab Quality Control', 'lab-quality-control', 'QC Laboratory', 'qc-laboratory', 'Pengujian mutu produk.', ['lab', 'valve']],
        ];

        foreach ($rows as [$id, $slugId, $en, $slugEn, $summary, $media]) {
            $facility = Facility::create(['status' => 'published', 'published_at' => now()->subDays(5), 'sort_order' => 0]);
            $facility->translations()->create([
                'locale' => 'id', 'name' => $id, 'slug' => $slugId, 'summary' => $summary,
                'description' => "<p>{$summary} Dokumentasi kegiatan operasional tersedia di galeri.</p>",
            ]);
            $facility->translations()->create([
                'locale' => 'en', 'name' => $en, 'slug' => $slugEn, 'summary' => $summary,
                'description' => "<p>{$summary}</p>",
            ]);

            foreach ($media as $i => $key) {
                $facility->media()->create(['media_asset_id' => $this->pool[$key]->id, 'position' => $i]);
            }
        }
    }

    private function seedGallery(): void
    {
        $cats = [];

        foreach ([['Kegiatan', 'kegiatan', 'Activities', 'activities'], ['Fasilitas', 'fasilitas', 'Facilities', 'facilities']] as [$id, $slugId, $en, $slugEn]) {
            $cat = GalleryCategory::create(['status' => 'published']);
            $cat->translations()->create(['locale' => 'id', 'name' => $id, 'slug' => $slugId]);
            $cat->translations()->create(['locale' => 'en', 'name' => $en, 'slug' => $slugEn]);
            $cats[$slugId] = $cat;
        }

        $items = [
            ['kegiatan', 'team', 'Kunjungan Pabrik', 'kunjungan-pabrik', 'Factory Visit'],
            ['kegiatan', 'lab', 'Uji Produk', 'uji-produk', 'Product Testing'],
            ['kegiatan', 'warehouse', 'Persiapan Kirim', 'persiapan-kirim', 'Shipment Preparation'],
            ['fasilitas', 'plant', 'Area Produksi', 'area-produksi', 'Production Area'],
            ['fasilitas', 'tank', 'Tangki Uji', 'tangki-uji', 'Test Tank'],
            ['fasilitas', 'pipe', 'Stok Pipa', 'stok-pipa', 'Pipe Stock'],
        ];

        foreach ($items as $i => [$cat, $img, $title, $slug, $enTitle]) {
            $item = GalleryItem::create([
                'gallery_category_id' => $cats[$cat]->id,
                'media_asset_id' => $this->pool[$img]->id,
                'position' => $i,
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ]);
            $item->translations()->create(['locale' => 'id', 'title' => $title, 'slug' => $slug, 'caption' => $title.'.']);
            $item->translations()->create(['locale' => 'en', 'title' => $enTitle, 'slug' => $enTitle ? strtolower(str_replace(' ', '-', $enTitle)) : $slug, 'caption' => $enTitle.'.']);
        }
    }

    // --- Halaman ---

    private function seedPages(): void
    {
        $page = Page::create(['template' => 'default', 'status' => 'published', 'published_at' => now()->subDays(10)]);
        $page->translations()->create([
            'locale' => 'id',
            'title' => 'Tentang Kami',
            'slug' => 'tentang-kami',
            'summary' => 'Profil singkat perusahaan untuk kebutuhan preview.',
            'content' => '<p>Halaman profil DUMMY untuk preview pengembangan.</p>',
            'seo_title' => 'Tentang Kami | Primainti',
            'seo_description' => 'Profil singkat perusahaan.',
        ]);
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'About Us',
            'slug' => 'about-us',
            'summary' => 'Brief company profile for preview purposes.',
            'content' => '<p>DUMMY profile page for development preview.</p>',
        ]);

        $idTr = $page->translations->firstWhere('locale', 'id');
        $idTr->sections()->create(['position' => 0, 'heading' => 'Sejarah Singkat', 'body' => '<p>Bagian sejarah DUMMY.</p>', 'is_visible' => true]);
        $idTr->sections()->create(['position' => 1, 'heading' => 'Visi & Misi', 'body' => '<p>Bagian visi misi DUMMY.</p>', 'is_visible' => true]);
        $enTr = $page->translations->firstWhere('locale', 'en');
        $enTr->sections()->create(['position' => 0, 'heading' => 'Our Story', 'body' => '<p>DUMMY story section.</p>', 'is_visible' => true]);
    }

    // --- Menu & pengaturan & pesan ---

    private function seedMenus(): void
    {
        $menus = app(MenuService::class);

        $header = $menus->menu('header');
        $items = [
            ['Beranda', 'Home', 'url', ['url' => '/id']],
            ['Tentang Kami', 'About Us', 'page', ['page_slug' => 'tentang-kami']],
            ['Produk', 'Products', 'route', ['route_name' => 'public.products.index']],
            ['Fasilitas', 'Facilities', 'route', ['route_name' => 'public.facilities.index']],
            ['Galeri', 'Gallery', 'route', ['route_name' => 'public.gallery.index']],
            ['Kontak', 'Contact', 'url', ['url' => '/id/kontak']],
        ];

        foreach ($items as $i => [$id, $en, $type, $target]) {
            $attrs = ['menu_id' => $header->id, 'title' => $id, 'link_type' => $type, 'position' => $i];

            if ($type === 'page') {
                $attrs['page_id'] = Page::whereHas('translations', fn ($q) => $q->where('locale', 'id')->where('slug', $target['page_slug']))->first()?->id;
            } elseif ($type === 'route') {
                $attrs['route_name'] = $target['route_name'];
            } else {
                $attrs['url'] = $target['url'];
            }

            $item = MenuItem::create($attrs);
            $item->translations()->create(['locale' => 'en', 'title' => $en]);
        }

        $footer = $menus->menu('footer');

        foreach ([
            ['Produk', 'Products', 'route', 'public.products.index'],
            ['Galeri', 'Gallery', 'route', 'public.gallery.index'],
            ['Fasilitas', 'Facilities', 'route', 'public.facilities.index'],
            ['Kontak', 'Contact', 'url', '/id/kontak'],
        ] as $i => [$id, $en, $type, $target]) {
            $item = MenuItem::create([
                'menu_id' => $footer->id, 'title' => $id, 'link_type' => $type, 'position' => $i,
                'url' => $type === 'url' ? $target : null,
                'route_name' => $type === 'route' ? $target : null,
            ]);
            $item->translations()->create(['locale' => 'en', 'title' => $en]);
        }

        MenuService::flushTrees();
    }

    private function seedSettings(): void
    {
        app(SettingsService::class)->setMany([
            'tagline_id' => 'Solusi Pompa & Sistem Perpipaan Industri (DUMMY)',
            'tagline_en' => 'Industrial Pump & Piping Solutions (DUMMY)',
            'contact_phone' => '+62 21 555 0100',
            'contact_whatsapp' => '+62 812 555 0100',
            'contact_email' => 'info@primainti.test',
            'address' => 'Jl. Industri Raya No. 88, Cikarang, Bekasi 17530 (DUMMY)',
            'opening_hours' => 'Senin–Jumat 08.00–17.00',
            'social_facebook' => 'https://facebook.com/primanti.dummy',
            'social_instagram' => 'https://instagram.com/primainti.dummy',
            'cta_label_id' => 'Minta Penawaran',
            'cta_label_en' => 'Request a Quote',
            'cta_url' => '/id/kontak',
            'seo_default_title' => 'Primainti (Preview Dev)',
            'seo_default_description' => 'Website preview pengembangan berisi data dummy.',
        ]);
    }

    private function seedMessages(): void
    {
        ContactMessage::create([
            'name' => 'Andi', 'email' => 'andi@example.com', 'company' => 'PT Contoh',
            'subject' => 'Tanya harga pompa', 'message' => 'Mohon info harga PS-200 (pesan DUMMY).',
            'status' => 'new',
        ]);
        ContactMessage::create([
            'name' => 'Sari', 'email' => 'sari@example.com', 'phone' => '0819000001',
            'subject' => 'Ketersediaan stok', 'message' => 'Apakah GV-150 ready? (pesan DUMMY).',
            'status' => 'in_progress', 'internal_note' => 'Sudah dibalas via email (catatan DUMMY).', 'is_read' => true,
        ]);
        ContactMessage::create([
            'name' => 'Budi', 'email' => 'budi@example.com',
            'subject' => 'Terima kasih', 'message' => 'Barang diterima dengan baik (pesan DUMMY).',
            'status' => 'resolved', 'is_read' => true,
        ]);
    }
}
