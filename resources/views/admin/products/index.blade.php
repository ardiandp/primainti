@extends('admin.layout')

@section('title', 'Produk')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[#0F2A54]">Produk</h1>

        @can('create', App\Models\Product::class)
            <a href="{{ route('admin.products.create') }}"
               class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-4 py-2 transition">
                + Produk
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-center gap-3 mb-6 text-sm">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama / slug / SKU…"
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

    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-2 font-medium">Nama</th>
                    <th class="px-4 py-2 font-medium">Kategori</th>
                    <th class="px-4 py-2 font-medium">SKU</th>
                    <th class="px-4 py-2 font-medium">Status</th>
                    <th class="px-4 py-2 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium">
                            {{ $product->translations->first()?->name ?? '—' }}
                            @if ($product->is_featured)
                                <span class="ml-1 rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-[#EA580C]">Unggulan</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-500">
                            {{ $product->category?->translationFor('id')?->name ?? $product->category?->translations->first()?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-2 text-gray-500">{{ $product->sku ?? '—' }}</td>
                        <td class="px-4 py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                {{ $product->status === 'published' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $product->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $product->status === 'archived' ? 'bg-gray-200 text-gray-600' : '' }}">
                                {{ $product->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex justify-end gap-2">
                                @if ($product->trashed())
                                    @can('update', $product)
                                        <form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
                                            @csrf
                                            <button type="submit" class="text-sm text-green-700 hover:underline">Pulihkan</button>
                                        </form>
                                    @endcan
                                    @can('delete', $product)
                                        <form method="POST" action="{{ route('admin.products.force-destroy', $product->id) }}"
                                              onsubmit="return confirm('Hapus permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $product)
                                        <a href="{{ route('admin.products.preview', $product) }}" class="text-sm text-gray-600 hover:underline">Pratinjau</a>
                                    @endcan
                                    @can('update', $product)
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-sm text-[#1E4278] hover:underline">Ubah</a>
                                    @endcan
                                    @can('delete', $product)
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
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
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
