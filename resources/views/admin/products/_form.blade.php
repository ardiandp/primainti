{{-- Form produk. Variabel: $product, $locales, $categories, $assets, $submitLabel. --}}
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@php($canPublish = auth()->user()->can('publish', $product->exists ? $product : \App\Models\Product::class))

<div class="grid md:grid-cols-3 gap-4 mb-6">
    <div>
        <label for="product_category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
        <select id="product_category_id" name="product_category_id" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            <option value="">— Pilih —</option>
            @foreach ($categories as $id => $name)
                <option value="{{ $id }}" @selected((string) old('product_category_id', $product->product_category_id) === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">Kode / Model (SKU)</label>
        <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="255"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status" name="status"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            <option value="draft" @selected(old('status', $product->status) === 'draft')>Draft</option>
            @if ($canPublish || $product->status === 'published')
                <option value="published" @selected(old('status', $product->status) === 'published')>Published</option>
            @endif
            <option value="archived" @selected(old('status', $product->status) === 'archived')>Arsip</option>
        </select>
        @unless ($canPublish)
            <p class="mt-1 text-xs text-gray-500">Akun Anda hanya boleh menyimpan draft/arsip.</p>
        @endunless
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}"
               min="0" max="100000"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <div>
        <label for="published_at" class="block text-sm font-medium text-gray-700 mb-1">Jadwal tayang</label>
        <input type="datetime-local" id="published_at" name="published_at"
               value="{{ old('published_at', $product->published_at?->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700 pt-6">
        <input type="hidden" name="is_featured" value="0">
        <input type="checkbox" name="is_featured" value="1" @checked((bool) old('is_featured', $product->is_featured))>
        Produk unggulan
    </label>
</div>

@foreach ($locales as $locale => $localeLabel)
    @php($t = $product->translations->firstWhere('locale', $locale))
    @php($ogId = old("translations.{$locale}.og_image_id", $t?->og_image_id))
    @php($ogAsset = $ogId ? ($assets[$ogId] ?? null) : null)

    <fieldset class="rounded-xl border border-gray-200 mb-6">
        <legend class="px-2 text-sm font-bold text-[#0F2A54]">{{ $localeLabel }} ({{ $locale }})</legend>

        <div class="p-4 space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="name-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                    <input type="text" id="name-{{ $locale }}" name="translations[{{ $locale }}][name]"
                           value="{{ old("translations.{$locale}.name", $t?->name) }}" required maxlength="255"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <div>
                    <label for="slug-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" id="slug-{{ $locale }}" name="translations[{{ $locale }}][slug]"
                           value="{{ old("translations.{$locale}.slug", $t?->slug) }}" maxlength="255" pattern="[A-Za-z0-9\-_]+"
                           placeholder="otomatis-dari-nama"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>
            </div>

            <div>
                <label for="short-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi singkat</label>
                <textarea id="short-{{ $locale }}" name="translations[{{ $locale }}][short_description]" rows="2" maxlength="500"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">{{ old("translations.{$locale}.short_description", $t?->short_description) }}</textarea>
            </div>

            <div>
                <span class="block text-sm font-medium text-gray-700 mb-1">Deskripsi lengkap</span>
                <x-rich-text-editor :name="'translations['.$locale.'][description]'"
                                    :value="old('translations.'.$locale.'.description', $t?->description ?? '')" />
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-1">Fungsi</span>
                    <x-rich-text-editor :name="'translations['.$locale.'][functions]'"
                                        :value="old('translations.'.$locale.'.functions', $t?->functions ?? '')" />
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-1">Aplikasi</span>
                    <x-rich-text-editor :name="'translations['.$locale.'][applications]'"
                                        :value="old('translations.'.$locale.'.applications', $t?->applications ?? '')" />
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="seo-title-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">SEO title</label>
                    <input type="text" id="seo-title-{{ $locale }}" name="translations[{{ $locale }}][seo_title]"
                           value="{{ old("translations.{$locale}.seo_title", $t?->seo_title) }}" maxlength="255"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>
                <div>
                    <label for="seo-description-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">Meta description</label>
                    <input type="text" id="seo-description-{{ $locale }}" name="translations[{{ $locale }}][seo_description]"
                           value="{{ old("translations.{$locale}.seo_description", $t?->seo_description) }}" maxlength="500"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>
            </div>

            <div>
                <span class="block text-sm font-medium text-gray-700 mb-1">OG image (Media Library)</span>
                <input type="hidden" name="translations[{{ $locale }}][og_image_id]"
                       data-og-input="{{ $locale }}" value="{{ $ogId }}">
                <div class="flex items-center gap-3">
                    <div data-og-preview="{{ $locale }}"
                         class="{{ $ogAsset ? '' : 'hidden' }} h-16 w-24 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center">
                        @if ($ogAsset && $ogAsset->isImage())
                            <img src="{{ $ogAsset->thumbnailUrl() ?? $ogAsset->url() }}"
                                 alt="{{ $ogAsset->alt ?: $ogAsset->title }}" class="max-h-full max-w-full object-contain">
                        @endif
                    </div>
                    <button type="button" data-og-pick="{{ $locale }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50">Pilih gambar</button>
                    <button type="button" data-og-clear="{{ $locale }}"
                            class="rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50">Hapus</button>
                </div>
            </div>
        </div>
    </fieldset>
@endforeach

{{-- Spesifikasi teknis --}}
<div class="rounded-xl border border-gray-200 mb-6 p-4">
    <h2 class="text-sm font-bold text-[#0F2A54] mb-1">Spesifikasi Teknis</h2>
    <p class="text-xs text-gray-500 mb-3">Label + nilai per bahasa, satuan bahasa-netral (mis. mm, kg, bar).</p>

    @php($specRows = old('specs', $product->specifications->map(fn ($s) => [
        'id' => $s->id,
        'sort_order' => $s->sort_order,
        'unit' => $s->unit,
        'translations' => $s->translations->mapWithKeys(fn ($st) => [$st->locale => ['label' => $st->label, 'value' => $st->value]])->all(),
    ])->all() ?? []))

    <div data-specs class="space-y-3">
        @foreach ($specRows as $index => $row)
            <div data-spec-row class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                @if (! empty($row['id']))
                    <input type="hidden" name="specs[{{ $index }}][id]" value="{{ $row['id'] }}">
                @endif
                <div class="grid md:grid-cols-[5rem_8rem_1fr_auto] gap-2 items-end mb-2">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Urutan</label>
                        <input type="number" name="specs[{{ $index }}][sort_order]" value="{{ $row['sort_order'] ?? $index }}"
                               min="0" max="100000" class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Satuan</label>
                        <input type="text" name="specs[{{ $index }}][unit]" value="{{ $row['unit'] ?? '' }}"
                               maxlength="50" placeholder="mm"
                               class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                    </div>
                    <div></div>
                    <div class="flex gap-2 pb-1">
                        @if (! empty($row['id']))
                            <label class="flex items-center gap-1 text-xs text-red-600">
                                <input type="checkbox" name="specs[{{ $index }}][_remove]" value="1"> Hapus
                            </label>
                        @else
                            <button type="button" data-spec-drop class="text-xs text-red-600">Batal</button>
                        @endif
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-2">
                    @foreach ($locales as $locale => $localeLabel)
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="specs[{{ $index }}][translations][{{ $locale }}][label]"
                                   value="{{ $row['translations'][$locale]['label'] ?? '' }}" maxlength="255"
                                   placeholder="Label ({{ $locale }})"
                                   class="rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                            <input type="text" name="specs[{{ $index }}][translations][{{ $locale }}][value]"
                                   value="{{ $row['translations'][$locale]['value'] ?? '' }}" maxlength="2000"
                                   placeholder="Nilai ({{ $locale }})"
                                   class="rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <template data-spec-template>
        <div data-spec-row class="rounded-lg border border-gray-200 bg-gray-50 p-3">
            <div class="grid md:grid-cols-[5rem_8rem_1fr_auto] gap-2 items-end mb-2">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Urutan</label>
                    <input type="number" name="specs[__INDEX__][sort_order]" value="__POSITION__" min="0" max="100000"
                           class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Satuan</label>
                    <input type="text" name="specs[__INDEX__][unit]" maxlength="50" placeholder="mm"
                           class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                </div>
                <div></div>
                <div class="pb-1">
                    <button type="button" data-spec-drop class="text-xs text-red-600">Batal</button>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-2">
                @foreach ($locales as $locale => $localeLabel)
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="specs[__INDEX__][translations][{{ $locale }}][label]"
                               maxlength="255" placeholder="Label ({{ $locale }})"
                               class="rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                        <input type="text" name="specs[__INDEX__][translations][{{ $locale }}][value]"
                               maxlength="2000" placeholder="Nilai ({{ $locale }})"
                               class="rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                    </div>
                @endforeach
            </div>
        </div>
    </template>

    <button type="button" data-spec-add data-next-index="{{ count($specRows) }}"
            class="mt-2 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-sm text-gray-600 hover:border-[#F97316] hover:text-[#F97316]">
        + Tambah spesifikasi
    </button>
</div>

{{-- Galeri media --}}
<div class="rounded-xl border border-gray-200 mb-6 p-4">
    <h2 class="text-sm font-bold text-[#0F2A54] mb-1">Galeri Media</h2>
    <p class="text-xs text-gray-500 mb-3">Dari Media Library (tanpa upload ulang). Tandai satu sebagai utama.</p>

    @php($mediaRows = old('media', $product->media->map(fn ($m) => [
        'id' => $m->id, 'media_asset_id' => $m->media_asset_id, 'position' => $m->position,
    ])->all() ?? []))
    @php($primaryAssetId = old('primary_media_asset_id', $product->media->firstWhere('is_primary', true)?->media_asset_id))

    <div data-media-list class="grid sm:grid-cols-2 gap-3">
        @foreach ($mediaRows as $index => $row)
            @php($asset = ($row['media_asset_id'] ?? null) ? ($assets[$row['media_asset_id']] ?? null) : null)
            <div data-media-row class="rounded-lg border border-gray-200 bg-gray-50 p-3 flex gap-3 items-start">
                @if (! empty($row['id']))
                    <input type="hidden" name="media[{{ $index }}][id]" value="{{ $row['id'] }}">
                @endif
                <input type="hidden" name="media[{{ $index }}][media_asset_id]" value="{{ $row['media_asset_id'] ?? '' }}">
                <div class="h-16 w-20 shrink-0 overflow-hidden rounded-md border border-gray-200 bg-white flex items-center justify-center">
                    @if ($asset && $asset->isImage())
                        <img src="{{ $asset->thumbnailUrl() ?? $asset->url() }}" alt="" class="max-h-full max-w-full object-contain">
                    @elseif ($asset)
                        <span class="text-xs text-gray-500 px-1">PDF</span>
                    @endif
                </div>
                <div class="flex-1 space-y-1.5 text-sm">
                    <p class="text-xs text-gray-600 truncate">{{ $asset?->original_name ?? 'Media #' . ($row['media_asset_id'] ?? '?') }}</p>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-600">Urutan
                            <input type="number" name="media[{{ $index }}][position]" value="{{ $row['position'] ?? $index }}"
                                   min="0" max="100000" class="w-16 rounded-md border border-gray-300 px-1.5 py-1 text-xs">
                        </label>
                        <label class="flex items-center gap-1 text-xs font-medium text-[#0F2A54]">
                            <input type="radio" name="primary_media_asset_id" value="{{ $row['media_asset_id'] ?? '' }}"
                                   @checked((string) $primaryAssetId === (string) ($row['media_asset_id'] ?? ''))> Utama
                        </label>
                    </div>
                    @if (! empty($row['id']))
                        <label class="flex items-center gap-1 text-xs text-red-600">
                            <input type="checkbox" name="media[{{ $index }}][_remove]" value="1"> Lepas
                        </label>
                    @else
                        <button type="button" data-media-drop class="text-xs text-red-600">Batal</button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" data-media-add data-next-index="{{ count($mediaRows) }}"
            class="mt-3 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-sm text-gray-600 hover:border-[#F97316] hover:text-[#F97316]">
        + Tambah media dari Library
    </button>
</div>

{{-- Dokumen --}}
<div class="rounded-xl border border-gray-200 mb-6 p-4">
    <h2 class="text-sm font-bold text-[#0F2A54] mb-1">Dokumen</h2>
    <p class="text-xs text-gray-500 mb-3">Datasheet / manual / sertifikat (PDF dari Media Library).</p>

    @php($docRows = old('documents', $product->documents->map(fn ($d) => [
        'id' => $d->id, 'media_asset_id' => $d->media_asset_id, 'type' => $d->type,
        'label' => $d->label, 'position' => $d->position,
    ])->all() ?? []))

    <div data-doc-list class="space-y-2">
        @foreach ($docRows as $index => $row)
            @php($asset = ($row['media_asset_id'] ?? null) ? ($assets[$row['media_asset_id']] ?? null) : null)
            <div data-doc-row class="rounded-lg border border-gray-200 bg-gray-50 p-3 grid md:grid-cols-[1fr_10rem_10rem_5rem_auto] gap-2 items-end">
                @if (! empty($row['id']))
                    <input type="hidden" name="documents[{{ $index }}][id]" value="{{ $row['id'] }}">
                @endif
                <input type="hidden" name="documents[{{ $index }}][media_asset_id]" value="{{ $row['media_asset_id'] ?? '' }}">
                <div class="text-sm">
                    <p class="font-medium truncate">{{ $row['label'] ?: ($asset?->original_name ?? 'Dokumen') }}</p>
                    <input type="text" name="documents[{{ $index }}][label]" value="{{ $row['label'] ?? '' }}"
                           maxlength="255" placeholder="Label tautan"
                           class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Jenis</label>
                    <select name="documents[{{ $index }}][type]" class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                        @foreach (\App\Models\ProductDocument::TYPES as $type)
                            <option value="{{ $type }}" @selected(($row['type'] ?? 'datasheet') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Urutan</label>
                    <input type="number" name="documents[{{ $index }}][position]" value="{{ $row['position'] ?? $index }}"
                           min="0" max="100000" class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                </div>
                <div class="text-xs text-gray-500">{{ $asset?->original_name }}</div>
                <div>
                    @if (! empty($row['id']))
                        <label class="flex items-center gap-1 text-xs text-red-600">
                            <input type="checkbox" name="documents[{{ $index }}][_remove]" value="1"> Hapus
                        </label>
                    @else
                        <button type="button" data-doc-drop class="text-xs text-red-600">Batal</button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" data-doc-add data-next-index="{{ count($docRows) }}"
            class="mt-3 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-sm text-gray-600 hover:border-[#F97316] hover:text-[#F97316]">
        + Tambah dokumen dari Library
    </button>
</div>

<div class="flex items-center gap-3">
    <button type="submit"
            class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-5 py-2.5 transition">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
</div>

<x-media-picker />

@push('scripts')
<script>
// Picker + baris dinamis form produk (galeri, dokumen, OG, spesifikasi).
(function () {
    let mode = null; // 'og:<locale>' | 'media' | 'doc'
    let ogLocale = null;

    function openPicker() {
        window.dispatchEvent(new CustomEvent('open-media-picker-dom'));

        if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
            window.Livewire.dispatch('open-media-picker');
        }
    }

    function detailOf(event) {
        const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
        return detail && detail.id ? detail : null;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    }

    // --- OG image ---
    document.querySelectorAll('[data-og-pick]').forEach((button) => {
        button.addEventListener('click', () => {
            mode = 'og';
            ogLocale = button.dataset.ogPick;
            openPicker();
        });
    });

    document.querySelectorAll('[data-og-clear]').forEach((button) => {
        button.addEventListener('click', () => {
            const locale = button.dataset.ogClear;
            const input = document.querySelector('[data-og-input="' + locale + '"]');
            const preview = document.querySelector('[data-og-preview="' + locale + '"]');

            if (input) {
                input.value = '';
            }

            if (preview) {
                preview.classList.add('hidden');
                preview.innerHTML = '';
            }
        });
    });

    // --- Galeri & dokumen ---
    const mediaAdd = document.querySelector('[data-media-add]');
    const docAdd = document.querySelector('[data-doc-add]');

    if (mediaAdd) {
        mediaAdd.addEventListener('click', () => {
            mode = 'media';
            openPicker();
        });
    }

    if (docAdd) {
        docAdd.addEventListener('click', () => {
            mode = 'doc';
            openPicker();
        });
    }

    window.addEventListener('media-picked', (event) => {
        if (!mode) {
            return;
        }

        // Alur gambar RTE memakai flag internal modulnya; abaikan di sini.
        const current = mode;
        const detail = detailOf(event);

        if (!detail) {
            mode = null;
            return;
        }

        if (current === 'og' && ogLocale) {
            const locale = ogLocale;
            mode = null;
            ogLocale = null;

            const input = document.querySelector('[data-og-input="' + locale + '"]');
            const preview = document.querySelector('[data-og-preview="' + locale + '"]');

            if (input) {
                input.value = detail.id;
            }

            if (preview) {
                preview.classList.remove('hidden');
                preview.innerHTML = detail.mime && detail.mime.indexOf('image/') === 0
                    ? '<img src="' + detail.thumbnail_url + '" alt="" class="max-h-full max-w-full object-contain">'
                    : '<span class="text-xs text-gray-500 px-1">' + escapeHtml(detail.name) + '</span>';
            }

            return;
        }

        if (current === 'media') {
            mode = null;

            const list = document.querySelector('[data-media-list]');
            const index = parseInt(mediaAdd.dataset.nextIndex || '0', 10);
            const thumb = detail.mime && detail.mime.indexOf('image/') === 0
                ? '<img src="' + detail.thumbnail_url + '" alt="" class="max-h-full max-w-full object-contain">'
                : '<span class="text-xs text-gray-500 px-1">PDF</span>';

            list.insertAdjacentHTML('beforeend',
                '<div data-media-row class="rounded-lg border border-gray-200 bg-gray-50 p-3 flex gap-3 items-start">'
                + '<input type="hidden" name="media[' + index + '][media_asset_id]" value="' + detail.id + '">'
                + '<div class="h-16 w-20 shrink-0 overflow-hidden rounded-md border border-gray-200 bg-white flex items-center justify-center">' + thumb + '</div>'
                + '<div class="flex-1 space-y-1.5 text-sm">'
                + '<p class="text-xs text-gray-600 truncate">' + escapeHtml(detail.name) + '</p>'
                + '<div class="flex items-center gap-2">'
                + '<label class="text-xs text-gray-600">Urutan '
                + '<input type="number" name="media[' + index + '][position]" value="' + index + '" min="0" max="100000" class="w-16 rounded-md border border-gray-300 px-1.5 py-1 text-xs"></label>'
                + '<label class="flex items-center gap-1 text-xs font-medium text-[#0F2A54]">'
                + '<input type="radio" name="primary_media_asset_id" value="' + detail.id + '"> Utama</label>'
                + '</div>'
                + '<button type="button" data-media-drop class="text-xs text-red-600">Batal</button>'
                + '</div></div>');

            mediaAdd.dataset.nextIndex = String(index + 1);
            return;
        }

        if (current === 'doc') {
            mode = null;

            const list = document.querySelector('[data-doc-list]');
            const index = parseInt(docAdd.dataset.nextIndex || '0', 10);

            list.insertAdjacentHTML('beforeend',
                '<div data-doc-row class="rounded-lg border border-gray-200 bg-gray-50 p-3 grid md:grid-cols-[1fr_10rem_10rem_5rem_auto] gap-2 items-end">'
                + '<input type="hidden" name="documents[' + index + '][media_asset_id]" value="' + detail.id + '">'
                + '<div class="text-sm"><p class="font-medium truncate">' + escapeHtml(detail.name) + '</p>'
                + '<input type="text" name="documents[' + index + '][label]" value="' + escapeHtml(detail.name) + '" maxlength="255" placeholder="Label tautan"'
                + ' class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm"></div>'
                + '<div><label class="block text-xs text-gray-600 mb-1">Jenis</label>'
                + '<select name="documents[' + index + '][type]" class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">'
                + '<option value="datasheet">Datasheet</option><option value="manual">Manual</option><option value="sertifikat">Sertifikat</option>'
                + '</select></div>'
                + '<div><label class="block text-xs text-gray-600 mb-1">Urutan</label>'
                + '<input type="number" name="documents[' + index + '][position]" value="' + index + '" min="0" max="100000"'
                + ' class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm"></div>'
                + '<div class="text-xs text-gray-500">' + escapeHtml(detail.mime || '') + '</div>'
                + '<div><button type="button" data-doc-drop class="text-xs text-red-600">Batal</button></div>'
                + '</div>');

            docAdd.dataset.nextIndex = String(index + 1);
        }
    });

    // --- Spesifikasi ---
    const specAdd = document.querySelector('[data-spec-add]');

    if (specAdd) {
        specAdd.addEventListener('click', () => {
            const template = document.querySelector('[data-spec-template]');
            const container = document.querySelector('[data-specs]');

            if (!template || !container) {
                return;
            }

            const index = parseInt(specAdd.dataset.nextIndex || '0', 10);

            container.insertAdjacentHTML('beforeend', template.innerHTML
                .split('__INDEX__').join(String(index))
                .split('__POSITION__').join(String(index)));

            specAdd.dataset.nextIndex = String(index + 1);
        });
    }

    document.addEventListener('click', (event) => {
        const drop = event.target.closest('[data-spec-drop],[data-media-drop],[data-doc-drop]');

        if (drop) {
            const row = drop.closest('[data-spec-row],[data-media-row],[data-doc-row]');

            if (row) {
                row.remove();
            }
        }
    });
})();
</script>
@endpush
