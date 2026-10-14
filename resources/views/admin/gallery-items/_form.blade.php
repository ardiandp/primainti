{{-- Form item galeri. Variabel: $item, $locales, $categories, $asset, $submitLabel. --}}
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@php($canPublish = auth()->user()->can('publish', $item->exists ? $item : \App\Models\GalleryItem::class))
@php($assetId = old('media_asset_id', $item->media_asset_id))

<div class="grid md:grid-cols-2 gap-4 mb-6">
    <div>
        <span class="block text-sm font-medium text-gray-700 mb-1">Media * (dari Library)</span>
        <input type="hidden" name="media_asset_id" data-asset-input value="{{ $assetId }}">
        <div class="flex items-center gap-3">
            <div data-asset-preview
                 class="{{ $asset ? '' : 'hidden' }} h-20 w-28 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center">
                @if ($asset && $asset->isImage())
                    <img src="{{ $asset->thumbnailUrl() ?? $asset->url() }}"
                         alt="{{ $asset->alt ?: $asset->title }}" class="max-h-full max-w-full object-contain">
                @endif
            </div>
            <button type="button" data-asset-pick
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50">Pilih media</button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="gallery_category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <select id="gallery_category_id" name="gallery_category_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="">— Tanpa kategori —</option>
                @foreach ($categories as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('gallery_category_id', $item->gallery_category_id) === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="status" name="status"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="draft" @selected(old('status', $item->status) === 'draft')>Draft</option>
                @if ($canPublish || $item->status === 'published')
                    <option value="published" @selected(old('status', $item->status) === 'published')>Published</option>
                @endif
                <option value="archived" @selected(old('status', $item->status) === 'archived')>Arsip</option>
            </select>
        </div>

        <div>
            <label for="taken_at" class="block text-sm font-medium text-gray-700 mb-1">Tanggal foto</label>
            <input type="date" id="taken_at" name="taken_at"
                   value="{{ old('taken_at', $item->taken_at?->format('Y-m-d')) }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
        </div>

        <div>
            <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
            <input type="number" id="position" name="position" value="{{ old('position', $item->position ?? 0) }}"
                   min="0" max="100000"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
        </div>
    </div>
</div>

<div class="mb-6">
    <label for="alt_override" class="block text-sm font-medium text-gray-700 mb-1">Alt text override</label>
    <input type="text" id="alt_override" name="alt_override" value="{{ old('alt_override', $item->alt_override) }}"
           maxlength="255" placeholder="Kosong = pakai alt/title media"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
</div>

@foreach ($locales as $locale => $localeLabel)
    @php($t = $item->translations->firstWhere('locale', $locale))

    <fieldset class="rounded-xl border border-gray-200 mb-6">
        <legend class="px-2 text-sm font-bold text-[#0F2A54]">{{ $localeLabel }} ({{ $locale }})</legend>

        <div class="p-4 grid md:grid-cols-2 gap-4">
            <div>
                <label for="title-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">Judul *</label>
                <input type="text" id="title-{{ $locale }}" name="translations[{{ $locale }}][title]"
                       value="{{ old("translations.{$locale}.title", $t?->title) }}" required maxlength="255"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            </div>

            <div>
                <label for="slug-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" id="slug-{{ $locale }}" name="translations[{{ $locale }}][slug]"
                       value="{{ old("translations.{$locale}.slug", $t?->slug) }}" maxlength="255" pattern="[A-Za-z0-9\-_]+"
                       placeholder="otomatis-dari-judul"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            </div>

            <div class="md:col-span-2">
                <label for="caption-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                <textarea id="caption-{{ $locale }}" name="translations[{{ $locale }}][caption]" rows="2" maxlength="2000"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">{{ old("translations.{$locale}.caption", $t?->caption) }}</textarea>
            </div>
        </div>
    </fieldset>
@endforeach

<div class="flex items-center gap-3">
    <button type="submit"
            class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-5 py-2.5 transition">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('admin.gallery-items.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
</div>

<x-media-picker />

@push('scripts')
<script>
// Pilih satu media existing untuk item galeri.
(function () {
    let awaiting = false;

    const pickButton = document.querySelector('[data-asset-pick]');

    function openPicker() {
        window.dispatchEvent(new CustomEvent('open-media-picker-dom'));

        if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
            window.Livewire.dispatch('open-media-picker');
        }
    }

    if (pickButton) {
        pickButton.addEventListener('click', () => {
            awaiting = true;
            openPicker();
        });
    }

    window.addEventListener('media-picked', (event) => {
        if (!awaiting) {
            return;
        }

        awaiting = false;

        const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;

        if (!detail || !detail.id) {
            return;
        }

        const input = document.querySelector('[data-asset-input]');
        const preview = document.querySelector('[data-asset-preview]');

        if (input) {
            input.value = detail.id;
        }

        if (preview) {
            preview.classList.remove('hidden');
            preview.innerHTML = detail.mime && detail.mime.indexOf('image/') === 0
                ? '<img src="' + detail.thumbnail_url + '" alt="" class="max-h-full max-w-full object-contain">'
                : '<span class="text-xs text-gray-500 px-1">PDF</span>';
        }
    });
})();
</script>
@endpush
