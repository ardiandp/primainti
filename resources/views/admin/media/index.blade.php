@extends('admin.layout')

@section('title', 'Media Library')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[#0F2A54]">Media Library</h1>

        @can('create', App\Models\MediaAsset::class)
            <form method="POST" action="{{ route('admin.media.store') }}"
                  enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="files[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif,.pdf"
                       class="text-sm text-gray-600" required>
                <button type="submit"
                        class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-4 py-2 transition">
                    Unggah
                </button>
            </form>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any() || session('upload_errors'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
            @foreach (session('upload_errors', []) as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Toolbar pencarian & filter --}}
    <form method="GET" action="{{ route('admin.media.index') }}" class="flex flex-wrap items-center gap-3 mb-6 text-sm">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul / nama file / alt…"
               class="rounded-lg border border-gray-300 px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-[#F97316]">

        <select name="type" class="rounded-lg border border-gray-300 px-3 py-2">
            <option value="">Semua jenis</option>
            <option value="image" @selected(($filters['type'] ?? '') === 'image')>Gambar</option>
            <option value="document" @selected(($filters['type'] ?? '') === 'document')>Dokumen (PDF)</option>
        </select>

        <select name="view" class="rounded-lg border border-gray-300 px-3 py-2">
            <option value="grid" @selected(($filters['view'] ?? 'grid') === 'grid')>Grid</option>
            <option value="list" @selected(($filters['view'] ?? '') === 'list')>List</option>
        </select>

        <label class="flex items-center gap-1 text-gray-600">
            <input type="checkbox" name="trash" value="1" @checked(($filters['trash'] ?? '') === '1')>
            Trash
        </label>

        <button type="submit" class="rounded-lg border border-[#0F2A54] text-[#0F2A54] px-4 py-2 hover:bg-[#0F2A54] hover:text-white transition">
            Terapkan
        </button>
    </form>

    @if ($assets->isEmpty())
        <p class="text-gray-500 text-sm">Belum ada media{{ ($filters['q'] ?? '') !== '' ? ' yang cocok' : '' }}.</p>
    @elseif (($filters['view'] ?? 'grid') === 'list')
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500">
                        <th class="py-2 pr-4">Nama</th>
                        <th class="py-2 pr-4">Jenis</th>
                        <th class="py-2 pr-4">Ukuran</th>
                        <th class="py-2 pr-4">Dimensi</th>
                        <th class="py-2 pr-4">Diunggah</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr class="border-b border-gray-100">
                            <td class="py-3 pr-4 font-medium">{{ $asset->title ?: $asset->original_name }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $asset->mime_type }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ number_format($asset->size / 1024, 1) }} KB</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $asset->width ? $asset->width.'×'.$asset->height : '—' }}</td>
                            <td class="py-3 pr-4 text-gray-600">{{ $asset->created_at->format('d M Y H:i') }}</td>
                            <td class="py-3 pr-4 text-right">
                                @can('update', $asset)
                                    <a href="{{ route('admin.media.edit', $asset) }}" class="text-[#0F2A54] hover:underline text-xs">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($assets as $asset)
                <a href="{{ route('admin.media.edit', $asset) }}"
                   class="group rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition">
                    <div class="h-36 bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if ($asset->thumbnailUrl())
                            <img src="{{ $asset->thumbnailUrl() }}" alt="{{ $asset->alt ?: $asset->title }}"
                                 class="max-h-full max-w-full object-contain">
                        @elseif ($asset->isImage())
                            <img src="{{ $asset->url() }}" alt="{{ $asset->alt ?: $asset->title }}"
                                 class="max-h-full max-w-full object-contain">
                        @else
                            <span class="text-3xl font-bold text-gray-400">PDF</span>
                        @endif
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-medium truncate">{{ $asset->title ?: $asset->original_name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ number_format($asset->size / 1024, 1) }} KB</p>
                        @if ($asset->trashed())
                            <span class="inline-block mt-1 text-xs text-red-600 font-semibold">Trash</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <div class="mt-6">
        {{ $assets->links() }}
    </div>
@endsection
