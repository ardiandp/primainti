{{-- Form item menu. Variabel: $menu, $item, $options, $submitLabel, $action. --}}
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($item->exists)
        @method('PUT')
    @endif

    <input type="hidden" name="menu_id" value="{{ $menu->id }}">

    <div class="grid md:grid-cols-2 gap-4 mb-4">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Label default *</label>
            <input type="text" id="title" name="title" value="{{ old('title', $item->title) }}"
                   required maxlength="255"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            <p class="mt-1 text-xs text-gray-500">Dipakai bila label bahasa kosong.</p>
        </div>

        <div>
            <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Induk (sub-menu)</label>
            <select id="parent_id" name="parent_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="">— Top level —</option>
                @foreach ($options['parents'] as $id => $label)
                    @if (! $item->exists || (int) $id !== (int) $item->id)
                        <option value="{{ $id }}" @selected((string) old('parent_id', $item->parent_id) === (string) $id)>{{ $label }}</option>
                    @endif
                @endforeach
            </select>
        </div>

        @foreach (['id' => 'Label Indonesia', 'en' => 'Label English'] as $locale => $label)
            <div>
                <label for="label-{{ $locale }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                <input type="text" id="label-{{ $locale }}" name="translations[{{ $locale }}][title]"
                       value="{{ old("translations.{$locale}.title", $item->translations->firstWhere('locale', $locale)?->title) }}"
                       maxlength="255"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-gray-200 p-4 mb-4">
        <label for="link_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis tautan *</label>
        <select id="link_type" name="link_type"
                class="w-full md:w-64 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            @foreach (\App\Models\MenuItem::LINK_TYPES as $type)
                <option value="{{ $type }}" @selected(old('link_type', $item->link_type) === $type)>{{ $type }}</option>
            @endforeach
        </select>

        <div data-target="url" class="mt-3">
            <label for="url" class="block text-sm font-medium text-gray-700 mb-1">URL</label>
            <input type="text" id="url" name="url" value="{{ old('url', $item->url) }}" maxlength="500"
                   placeholder="https://… atau /path"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
        </div>

        <div data-target="page" class="mt-3">
            <label for="page_id" class="block text-sm font-medium text-gray-700 mb-1">Halaman CMS</label>
            <select id="page_id" name="page_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="">— Pilih —</option>
                @foreach ($options['pages'] as $id => $label)
                    <option value="{{ $id }}" @selected((string) old('page_id', $item->page_id) === (string) $id)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Halaman draft/arsip ditandai rusak dan disembunyikan di publik.</p>
        </div>

        <div data-target="product_category" class="mt-3">
            <label for="product_category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori produk</label>
            <select id="product_category_id" name="product_category_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="">— Pilih —</option>
                @foreach ($options['categories'] as $id => $label)
                    <option value="{{ $id }}" @selected((string) old('product_category_id', $item->product_category_id) === (string) $id)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div data-target="route" class="mt-3">
            <label for="route_name" class="block text-sm font-medium text-gray-700 mb-1">Rute</label>
            <select id="route_name" name="route_name"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="">— Pilih —</option>
                @foreach ($options['routes'] as $route => $label)
                    <option value="{{ $route }}" @selected(old('route_name', $item->route_name) === $route)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div>
            <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
            <input type="number" id="position" name="position" value="{{ old('position', $item->position ?? 0) }}"
                   min="0" max="100000"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700 pt-6">
            <input type="hidden" name="opens_in_new_tab" value="0">
            <input type="checkbox" name="opens_in_new_tab" value="1"
                   @checked((bool) old('opens_in_new_tab', $item->opens_in_new_tab))>
            Buka di tab baru
        </label>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-5 py-2.5 transition">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.menus.edit', $menu) }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
    </div>
</form>

<script>
// Tampilkan field target sesuai jenis tautan.
(function () {
    const select = document.getElementById('link_type');

    function toggle() {
        document.querySelectorAll('[data-target]').forEach((block) => {
            block.style.display = block.dataset.target === select.value ? '' : 'none';
        });
    }

    if (select) {
        select.addEventListener('change', toggle);
        toggle();
    }
})();
</script>
