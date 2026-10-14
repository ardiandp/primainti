@extends('admin.layout')

@section('title', 'Galeri')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[#0F2A54]">Galeri</h1>

        @can('create', App\Models\GalleryItem::class)
            <a href="{{ route('admin.gallery-items.create') }}"
               class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-4 py-2 transition">
                + Item
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin.gallery-items.index') }}" class="flex flex-wrap items-center gap-3 mb-6 text-sm">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul / caption…"
               class="rounded-lg border border-gray-300 px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-[#F97316]">

        <select name="category" class="rounded-lg border border-gray-300 px-3 py-2">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>
                    {{ $category->translationFor('id')?->name ?? $category->translations->first()?->name ?? '#'.$category->id }}
                </option>
            @endforeach
        </select>

        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2">
            <option value="">Semua status</option>
            <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
            <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
            <option value="archived" @selected(($filters['status'] ?? '') === 'archived')>Arsip</option>
        </select>

        <label class="flex items-center gap-1 text-gray-600">
            <input type="checkbox" name="trash" value="1" @checked(($filters['trash'] ?? '') === '1')> Trash
        </label>

        <button type="submit" class="rounded-lg bg-[#0F2A54] text-white px-4 py-2">Filter</button>
    </form>

    @if ($items->isEmpty())
        <p class="rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center text-gray-500">Belum ada item galeri.</p>
    @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($items as $item)
                <div class="rounded-xl border border-gray-200 overflow-hidden bg-white">
                    <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if ($item->asset)
                            <img src="{{ $item->asset->thumbnailUrl() ?? $item->asset->url() }}"
                                 alt="{{ $item->altFor($item->translations->first()?->caption) }}"
                                 loading="lazy" class="max-h-full max-w-full object-contain">
                        @else
                            <span class="text-xs text-gray-400">Tanpa media</span>
                        @endif
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-medium truncate">{{ $item->translations->first()?->title ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $item->category?->translationFor('id')?->name ?? 'Tanpa kategori' }} · {{ $item->status }}</p>
                        <div class="mt-2 flex gap-2">
                            @if ($item->trashed())
                                @can('update', $item)
                                    <form method="POST" action="{{ route('admin.gallery-items.restore', $item->id) }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-green-700 hover:underline">Pulihkan</button>
                                    </form>
                                @endcan
                                @can('delete', $item)
                                    <form method="POST" action="{{ route('admin.gallery-items.force-destroy', $item->id) }}"
                                          onsubmit="return confirm('Hapus permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $item)
                                    <a href="{{ route('admin.gallery-items.edit', $item) }}" class="text-xs text-[#1E4278] hover:underline">Ubah</a>
                                @endcan
                                @can('delete', $item)
                                    <form method="POST" action="{{ route('admin.gallery-items.destroy', $item) }}"
                                          onsubmit="return confirm('Pindahkan ke trash?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Trash</button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    @endif
@endsection
