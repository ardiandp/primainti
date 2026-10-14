@extends('admin.layout')

@section('title', 'Pengaturan Situs')

@section('content')
    <h1 class="text-xl font-bold text-[#0F2A54] mb-6">Pengaturan Situs</h1>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        @foreach ($groups as $group => $fields)
            <fieldset class="rounded-xl border border-gray-200 mb-6">
                <legend class="px-2 text-sm font-bold text-[#0F2A54]">{{ $group }}</legend>

                <div class="p-4 grid md:grid-cols-2 gap-4">
                    @foreach ($fields as $key => $meta)
                        @php($value = old("settings.{$key}", $values[$key] ?? null))

                        @if ($meta['type'] === 'media_image')
                            @php($asset = $value ? ($assets[$value] ?? null) : null)
                            <div class="md:col-span-2">
                                <span class="block text-sm font-medium text-gray-700 mb-1">{{ $meta['label'] }}</span>
                                <input type="hidden" name="settings[{{ $key }}]" data-media-input="{{ $key }}" value="{{ $value }}">
                                <div class="flex items-center gap-3">
                                    <div data-media-preview="{{ $key }}"
                                         class="{{ $asset ? '' : 'hidden' }} h-16 w-24 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center">
                                        @if ($asset)
                                            <img src="{{ $asset->thumbnailUrl() ?? $asset->url() }}"
                                                 alt="{{ $asset->alt ?: $asset->title }}" class="max-h-full max-w-full object-contain">
                                        @endif
                                    </div>
                                    <button type="button" data-media-pick="{{ $key }}"
                                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50">Pilih gambar</button>
                                    <button type="button" data-media-clear="{{ $key }}"
                                            class="rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50">Hapus</button>
                                </div>
                            </div>
                        @elseif ($meta['type'] === 'textarea')
                            <div class="md:col-span-2">
                                <label for="setting-{{ $key }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $meta['label'] }}</label>
                                <textarea id="setting-{{ $key }}" name="settings[{{ $key }}]" rows="3"
                                          maxlength="{{ $meta['max'] ?? 2000 }}"
                                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">{{ $value }}</textarea>
                            </div>
                        @else
                            <div>
                                <label for="setting-{{ $key }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $meta['label'] }}</label>
                                <input type="text" id="setting-{{ $key }}" name="settings[{{ $key }}]"
                                       value="{{ $value }}" maxlength="{{ $meta['max'] ?? 255 }}"
                                       @if ($meta['type'] === 'email') inputmode="email" placeholder="nama@domain.com" @endif
                                       @if ($meta['type'] === 'url') inputmode="url" placeholder="https://…" @endif
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                            </div>
                        @endif
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <p class="mb-4 text-xs text-gray-500">Kunci rahasia (SMTP, API key) tidak disimpan di sini — tetap di environment server.</p>

        <button type="submit"
                class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-5 py-2.5 transition">
            Simpan pengaturan
        </button>
    </form>

    <x-media-picker />

    @push('scripts')
    <script>
    // Picker logo/favicon: satu modal untuk semua field media.
    (function () {
        let field = null;

        function openPicker() {
            window.dispatchEvent(new CustomEvent('open-media-picker-dom'));

            if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                window.Livewire.dispatch('open-media-picker');
            }
        }

        document.querySelectorAll('[data-media-pick]').forEach((button) => {
            button.addEventListener('click', () => {
                field = button.dataset.mediaPick;
                openPicker();
            });
        });

        document.querySelectorAll('[data-media-clear]').forEach((button) => {
            button.addEventListener('click', () => {
                const key = button.dataset.mediaClear;
                const input = document.querySelector('[data-media-input="' + key + '"]');
                const preview = document.querySelector('[data-media-preview="' + key + '"]');

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
            if (!field) {
                return;
            }

            const key = field;
            field = null;

            const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;

            if (!detail || !detail.id) {
                return;
            }

            const input = document.querySelector('[data-media-input="' + key + '"]');
            const preview = document.querySelector('[data-media-preview="' + key + '"]');

            if (input) {
                input.value = detail.id;
            }

            if (preview) {
                if (detail.mime && detail.mime.indexOf('image/') === 0) {
                    preview.classList.remove('hidden');
                    preview.innerHTML = '<img src="' + detail.thumbnail_url
                        + '" alt="" class="max-h-full max-w-full object-contain">';
                } else {
                    alert('Pilih berkas gambar untuk pengaturan ini.');
                }
            }
        });
    })();
    </script>
    @endpush
@endsection
