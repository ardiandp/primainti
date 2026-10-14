@extends('admin.layout')

@section('title', 'Kategori Galeri')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[#0F2A54]">Kategori Galeri</h1>

        @can('create', App\Models\GalleryCategory::class)
            <a href="{{ route('admin.gallery-categories.create') }}"
               class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-4 py-2 transition">
                + Kategori
            </a>
        @endcan
    </div>

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

    <form method="GET" action="{{ route('admin.gallery-categories.index') }}" class="flex flex-wrap items-center gap-3 mb-6 text-sm">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama / slug…"
               class="rounded-lg border border-gray-300 px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-[#F97316]">

        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2">
            <option value="">Semua status</option>
            <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
            <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
        </select>

        <label class="flex items-center gap-1 text-gray-600">
            <input type="checkbox" name="trash" value="1" @checked(($filters['trash'] ?? '') === '1')> Trash
        </label>

        <button type="submit" class="rounded-lg bg-[#0F2A54] text-white px-4 py-2">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-2 font-medium">Nama</th>
                    <th class="px-4 py-2 font-medium">Item</th>
                    <th class="px-4 py-2 font-medium">Status</th>
                    <th class="px-4 py-2 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium">{{ $category->translations->first()?->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $category->items_count }}</td>
                        <td class="px-4 py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                {{ $category->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $category->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex justify-end gap-2">
                                @if ($category->trashed())
                                    @can('update', $category)
                                        <form method="POST" action="{{ route('admin.gallery-categories.restore', $category->id) }}">
                                            @csrf
                                            <button type="submit" class="text-sm text-green-700 hover:underline">Pulihkan</button>
                                        </form>
                                    @endcan
                                    @can('delete', $category)
                                        <form method="POST" action="{{ route('admin.gallery-categories.force-destroy', $category->id) }}"
                                              onsubmit="return confirm('Hapus permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $category)
                                        <a href="{{ route('admin.gallery-categories.edit', $category) }}" class="text-sm text-[#1E4278] hover:underline">Ubah</a>
                                    @endcan
                                    @can('delete', $category)
                                        <form method="POST" action="{{ route('admin.gallery-categories.destroy', $category) }}"
                                              onsubmit="return confirm('Pindahkan ke trash?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">Trash</button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t border-gray-100">
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>
@endsection
