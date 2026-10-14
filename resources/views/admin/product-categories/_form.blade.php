{{-- Form kategori produk. Variabel: $category, $locales, $submitLabel. --}}
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@php($canPublish = auth()->user()->can('publish', $category->exists ? $category : \App\Models\ProductCategory::class))

<div class="mb-6 max-w-md">
    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
    <select id="status" name="status"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316]">
        <option value="draft" @selected(old('status', $category->status) === 'draft')>Draft</option>
        @if ($canPublish || $category->status === 'published')
            <option value="published" @selected(old('status', $category->status) === 'published')>Published</option>
        @endif
    </select>
    @unless ($canPublish)
        <p class="mt-1 text-xs text-gray-500">Akun Anda hanya boleh menyimpan draft.</p>
    @endunless
</div>

@foreach ($locales as $locale => $localeLabel)
    @php($t = $category->translations->firstWhere('locale', $locale))

    <fieldset class="rounded-xl border border-gray-200 mb-6">
        <legend class="px-2 text-sm font-bold text-[#0F2A54]">{{ $localeLabel }} ({{ $locale }})</legend>

        <div class="p-4 grid md:grid-cols-2 gap-4">
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
                <p class="mt-1 text-xs text-gray-500">Unik per bahasa. Kosong = dibuat dari nama.</p>
            </div>
        </div>
    </fieldset>
@endforeach

<div class="flex items-center gap-3">
    <button type="submit"
            class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-5 py-2.5 transition">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('admin.product-categories.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
</div>
