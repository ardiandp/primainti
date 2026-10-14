{{-- Form halaman CMS. Variabel: $page, $locales, $ogAssets, $submitLabel. --}}
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@php($canPublish = auth()->user()->can('publish', $page->exists ? $page : \App\Models\Page::class))

<div class="grid md:grid-cols-3 gap-4 mb-6">
    <div>
        <label for="template" class="block text-sm font-medium text-gray-700 mb-1">Template</label>
        <input type="text" id="template" name="template" value="{{ old('template', $page->template) }}"
               placeholder="default" maxlength="50"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
        <p class="mt-1 text-xs text-gray-500">Opsional. Kosongkan untuk template bawaan.</p>
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status" name="status"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            <option value="draft" @selected(old('status', $page->status) === 'draft')>Draft</option>
            @if ($canPublish || $page->status === 'published')
                <option value="published" @selected(old('status', $page->status) === 'published')>Published</option>
            @endif
            <option value="archived" @selected(old('status', $page->status) === 'archived')>Arsip</option>
        </select>
        @unless ($canPublish)
            <p class="mt-1 text-xs text-gray-500">Akun Anda hanya boleh menyimpan draft/arsip.</p>
        @endunless
    </div>

    <div>
        <label for="published_at" class="block text-sm font-medium text-gray-700 mb-1">Jadwal tayang</label>
        <input type="datetime-local" id="published_at" name="published_at"
               value="{{ old('published_at', $page->published_at?->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
        <p class="mt-1 text-xs text-gray-500">Opsional. Kosong = tayang segera saat published.</p>
    </div>
</div>

@foreach ($locales as $locale => $localeLabel)
    @php($t = $page->translations->firstWhere('locale', $locale))
    @php($ogId = old("translations.{$locale}.og_image_id", $t?->og_image_id))
    @php($ogAsset = $ogId ? ($ogAssets[$ogId] ?? null) : null)

    <fieldset class="rounded-xl border border-gray-200 mb-6">
        <legend class="px-2 text-sm font-bold text-[#0F2A54]">{{ $localeLabel }} ({{ $locale }})</legend>

        <div class="p-4 space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
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
                    <p class="mt-1 text-xs text-gray-500">Unik per bahasa. Kosong = dibuat dari judul.</p>
                </div>
            </div>

            <div>
                <label for="summary-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">Ringkasan</label>
                <textarea id="summary-{{ $locale }}" name="translations[{{ $locale }}][summary]" rows="2" maxlength="2000"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">{{ old("translations.{$locale}.summary", $t?->summary) }}</textarea>
            </div>

            <div>
                <span id="content-{{ $locale }}-label" class="block text-sm font-medium text-gray-700 mb-1">Konten</span>
                <x-rich-text-editor :name="'translations['.$locale.'][content]'"
                                    :value="old('translations.'.$locale.'.content', $t?->content ?? '')" />
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
                        @if ($ogAsset)
                            @if ($ogAsset->isImage())
                                <img src="{{ $ogAsset->thumbnailUrl() ?? $ogAsset->url() }}"
                                     alt="{{ $ogAsset->alt ?: $ogAsset->title }}" class="max-h-full max-w-full object-contain">
                            @else
                                <span data-og-filelabel class="text-xs text-gray-500 px-1 truncate">{{ $ogAsset->original_name }}</span>
                            @endif
                        @endif
                    </div>
                    <button type="button" data-og-pick="{{ $locale }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50">
                        Pilih gambar
                    </button>
                    <button type="button" data-og-clear="{{ $locale }}"
                            class="rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                        Hapus
                    </button>
                </div>
            </div>

            <div>
                <span class="block text-sm font-medium text-gray-700 mb-2">Sections</span>
                <div data-sections="{{ $locale }}" class="space-y-3">
                    @foreach (($t?->sections ?? collect()) as $index => $section)
                        <div data-section-row class="rounded-lg border border-gray-200 bg-gray-50 p-3 space-y-2">
                            <input type="hidden" name="sections[{{ $locale }}][{{ $index }}][id]" value="{{ $section->id }}">
                            <div class="grid md:grid-cols-[1fr_5rem_7rem_auto] gap-2 items-end">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Heading</label>
                                    <input type="text" name="sections[{{ $locale }}][{{ $index }}][heading]"
                                           value="{{ $section->heading }}" maxlength="255"
                                           class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Urutan</label>
                                    <input type="number" name="sections[{{ $locale }}][{{ $index }}][position]"
                                           value="{{ $section->position }}" min="0" max="100000"
                                           class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                                </div>
                                <label class="flex items-center gap-1 text-xs text-gray-600 pb-2">
                                    <input type="hidden" name="sections[{{ $locale }}][{{ $index }}][is_visible]" value="0">
                                    <input type="checkbox" name="sections[{{ $locale }}][{{ $index }}][is_visible]" value="1"
                                           @checked($section->is_visible)> Tampil
                                </label>
                                <label class="flex items-center gap-1 text-xs text-red-600 pb-2">
                                    <input type="checkbox" name="sections[{{ $locale }}][{{ $index }}][_remove]" value="1"> Hapus
                                </label>
                            </div>
                            <x-rich-text-editor :name="'sections['.$locale.']['.$index.'][body]'"
                                                :value="$section->body ?? ''" />
                        </div>
                    @endforeach
                </div>

                <template data-section-template="{{ $locale }}">
                    <div data-section-row class="rounded-lg border border-gray-200 bg-gray-50 p-3 space-y-2">
                        <div class="grid md:grid-cols-[1fr_5rem_7rem_auto] gap-2 items-end">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Heading</label>
                                <input type="text" name="sections[{{ $locale }}][__INDEX__][heading]" maxlength="255"
                                       class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Urutan</label>
                                <input type="number" name="sections[{{ $locale }}][__INDEX__][position]"
                                       value="__POSITION__" min="0" max="100000"
                                       class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                            </div>
                            <label class="flex items-center gap-1 text-xs text-gray-600 pb-2">
                                <input type="hidden" name="sections[{{ $locale }}][__INDEX__][is_visible]" value="0">
                                <input type="checkbox" name="sections[{{ $locale }}][__INDEX__][is_visible]" value="1" checked> Tampil
                            </label>
                            <button type="button" data-section-drop class="text-xs text-red-600 pb-2">Batal</button>
                        </div>
                        <x-rich-text-editor :name="'sections['.$locale.'][__INDEX__][body]'" :id="'__UNIQ__'" value="" />
                    </div>
                </template>

                <button type="button" data-section-add="{{ $locale }}"
                        data-next-index="{{ count($t?->sections ?? []) }}"
                        class="mt-2 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-sm text-gray-600 hover:border-[#F97316] hover:text-[#F97316]">
                    + Tambah section ({{ $locale }})
                </button>
            </div>
        </div>
    </fieldset>
@endforeach

<div class="flex items-center gap-3">
    <button type="submit"
            class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-5 py-2.5 transition">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('admin.pages.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
</div>

<x-media-picker />

@push('scripts')
<script>
// OG image picker + tambah section dinamis (form halaman).
(function () {
    let ogLocale = null;

    function openPicker() {
        window.dispatchEvent(new CustomEvent('open-media-picker-dom'));

        if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
            window.Livewire.dispatch('open-media-picker');
        }
    }

    document.querySelectorAll('[data-og-pick]').forEach((button) => {
        button.addEventListener('click', () => {
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

    window.addEventListener('media-picked', (event) => {
        if (!ogLocale) {
            return;
        }

        const locale = ogLocale;
        ogLocale = null;

        const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;

        if (!detail || !detail.id) {
            return;
        }

        const input = document.querySelector('[data-og-input="' + locale + '"]');
        const preview = document.querySelector('[data-og-preview="' + locale + '"]');

        if (input) {
            input.value = detail.id;
        }

        if (preview) {
            preview.classList.remove('hidden');

            if (detail.mime && detail.mime.indexOf('image/') === 0) {
                preview.innerHTML = '<img src="' + detail.thumbnail_url
                    + '" alt="" class="max-h-full max-w-full object-contain">';
            } else {
                preview.innerHTML = '<span class="text-xs text-gray-500 px-1 truncate">'
                    + (detail.name || 'Dokumen') + '</span>';
            }
        }
    });

    document.querySelectorAll('[data-section-add]').forEach((button) => {
        button.addEventListener('click', () => {
            const locale = button.dataset.sectionAdd;
            const template = document.querySelector('template[data-section-template="' + locale + '"]');
            const container = document.querySelector('[data-sections="' + locale + '"]');

            if (!template || !container) {
                return;
            }

            const index = parseInt(button.dataset.nextIndex || '0', 10);
            const uniq = 'rte-new-' + locale + '-' + index + '-' + Date.now();
            const html = template.innerHTML
                .split('__INDEX__').join(String(index))
                .split('__POSITION__').join(String(index))
                .split('__UNIQ__').join(uniq);

            container.insertAdjacentHTML('beforeend', html);
            button.dataset.nextIndex = String(index + 1);
            window.dispatchEvent(new CustomEvent('rte:refresh'));
        });
    });

    document.addEventListener('click', (event) => {
        const drop = event.target.closest('[data-section-drop]');

        if (drop) {
            drop.closest('[data-section-row]').remove();
        }
    });
})();
</script>
@endpush
