{{-- Form fasilitas. Variabel: $facility, $locales, $assets, $submitLabel. --}}
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@php($canPublish = auth()->user()->can('publish', $facility->exists ? $facility : \App\Models\Facility::class))

<div class="grid md:grid-cols-3 gap-4 mb-6">
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status" name="status"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            <option value="draft" @selected(old('status', $facility->status) === 'draft')>Draft</option>
            @if ($canPublish || $facility->status === 'published')
                <option value="published" @selected(old('status', $facility->status) === 'published')>Published</option>
            @endif
            <option value="archived" @selected(old('status', $facility->status) === 'archived')>Arsip</option>
        </select>
        @unless ($canPublish)
            <p class="mt-1 text-xs text-gray-500">Akun Anda hanya boleh menyimpan draft/arsip.</p>
        @endunless
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $facility->sort_order ?? 0) }}"
               min="0" max="100000"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <div>
        <label for="published_at" class="block text-sm font-medium text-gray-700 mb-1">Jadwal tayang</label>
        <input type="datetime-local" id="published_at" name="published_at"
               value="{{ old('published_at', $facility->published_at?->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>
</div>

@foreach ($locales as $locale => $localeLabel)
    @php($t = $facility->translations->firstWhere('locale', $locale))

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
                <label for="summary-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">Ringkasan</label>
                <textarea id="summary-{{ $locale }}" name="translations[{{ $locale }}][summary]" rows="2" maxlength="2000"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">{{ old("translations.{$locale}.summary", $t?->summary) }}</textarea>
            </div>

            <div>
                <span class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</span>
                <x-rich-text-editor :name="'translations['.$locale.'][description]'"
                                    :value="old('translations.'.$locale.'.description', $t?->description ?? '')" />
            </div>
        </div>
    </fieldset>
@endforeach

<div class="rounded-xl border border-gray-200 mb-6 p-4">
    <h2 class="text-sm font-bold text-[#0F2A54] mb-1">Foto Fasilitas</h2>
    <p class="text-xs text-gray-500 mb-3">Dari Media Library (boleh sama dengan foto galeri/produk). Foto pertama menjadi sampul.</p>

    @php($mediaRows = old('media', $facility->media->map(fn ($m) => [
        'id' => $m->id, 'media_asset_id' => $m->media_asset_id, 'position' => $m->position,
    ])->all() ?? []))

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
                    @endif
                </div>
                <div class="flex-1 space-y-1.5 text-sm">
                    <p class="text-xs text-gray-600 truncate">{{ $asset?->original_name ?? 'Foto' }}</p>
                    <label class="text-xs text-gray-600">Urutan
                        <input type="number" name="media[{{ $index }}][position]" value="{{ $row['position'] ?? $index }}"
                               min="0" max="100000" class="w-16 rounded-md border border-gray-300 px-1.5 py-1 text-xs">
                    </label>
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
        + Tambah foto dari Library
    </button>
</div>

<div class="flex items-center gap-3">
    <button type="submit"
            class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-5 py-2.5 transition">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('admin.facilities.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
</div>

<x-media-picker />

@push('scripts')
<script>
// Tambah foto fasilitas dari Media Picker.
(function () {
    let awaiting = false;

    const addButton = document.querySelector('[data-media-add]');

    function openPicker() {
        window.dispatchEvent(new CustomEvent('open-media-picker-dom'));

        if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
            window.Livewire.dispatch('open-media-picker');
        }
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    }

    if (addButton) {
        addButton.addEventListener('click', () => {
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

        const list = document.querySelector('[data-media-list]');
        const index = parseInt(addButton.dataset.nextIndex || '0', 10);
        const thumb = detail.mime && detail.mime.indexOf('image/') === 0
            ? '<img src="' + detail.thumbnail_url + '" alt="" class="max-h-full max-w-full object-contain">'
            : '<span class="text-xs text-gray-500 px-1">PDF</span>';

        list.insertAdjacentHTML('beforeend',
            '<div data-media-row class="rounded-lg border border-gray-200 bg-gray-50 p-3 flex gap-3 items-start">'
            + '<input type="hidden" name="media[' + index + '][media_asset_id]" value="' + detail.id + '">'
            + '<div class="h-16 w-20 shrink-0 overflow-hidden rounded-md border border-gray-200 bg-white flex items-center justify-center">' + thumb + '</div>'
            + '<div class="flex-1 space-y-1.5 text-sm">'
            + '<p class="text-xs text-gray-600 truncate">' + escapeHtml(detail.name) + '</p>'
            + '<label class="text-xs text-gray-600">Urutan '
            + '<input type="number" name="media[' + index + '][position]" value="' + index + '" min="0" max="100000" class="w-16 rounded-md border border-gray-300 px-1.5 py-1 text-xs"></label>'
            + '<div><button type="button" data-media-drop class="text-xs text-red-600">Batal</button></div>'
            + '</div></div>');

        addButton.dataset.nextIndex = String(index + 1);
    });

    document.addEventListener('click', (event) => {
        const drop = event.target.closest('[data-media-drop]');

        if (drop) {
            drop.closest('[data-media-row]').remove();
        }
    });
})();
</script>
@endpush
