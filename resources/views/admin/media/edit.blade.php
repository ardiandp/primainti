@extends('admin.layout')

@section('title', 'Edit Media')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[#0F2A54]">Detail Media</h1>
        <a href="{{ route('admin.media.index') }}" class="text-sm text-[#0F2A54] hover:underline">← Kembali ke Media Library</a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Pratinjau --}}
        <div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center min-h-[240px] p-4">
                @if ($media->isImage())
                    <img src="{{ $media->url() }}" alt="{{ $media->alt ?: $media->title }}" class="max-h-80 object-contain rounded">
                @else
                    <span class="text-5xl font-bold text-gray-400">PDF</span>
                @endif
            </div>

            {{-- Pemeriksaan referensi pemakaian --}}
            @if (count($usages) > 0)
                <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3">
                    <p class="font-semibold mb-1">⚠ Media ini masih dipakai:</p>
                    <ul class="list-disc list-inside">
                        @foreach ($usages as $usage)
                            <li>{{ $usage['label'] }}: {{ $usage['count'] }} referensi</li>
                        @endforeach
                    </ul>
                    <p class="mt-1 text-xs">Media tidak bisa dihapus selama referensi masih ada.</p>
                </div>
            @endif
        </div>

        {{-- Form metadata --}}
        <div>
            <form method="POST" action="{{ route('admin.media.update', $media) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $media->title) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <div>
                    <label for="alt" class="block text-sm font-medium text-gray-700 mb-1">Alt Text (aksesibilitas)</label>
                    <input id="alt" name="alt" type="text" value="{{ old('alt', $media->alt) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <div>
                    <label for="caption" class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <input id="caption" name="caption" type="text" value="{{ old('caption', $media->caption) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">{{ old('description', $media->description) }}</textarea>
                </div>

                @can('update', $media)
                    <button type="submit"
                            class="rounded-lg bg-[#F97316] hover:bg-[#EA580C] text-white text-sm font-semibold px-4 py-2 transition">
                        Simpan Metadata
                    </button>
                @endcan
            </form>

            <div class="mt-6 rounded-lg border border-gray-200 p-4 text-sm space-y-1 text-gray-700">
                <p class="font-semibold text-[#0F2A54] mb-2">Detail Teknis</p>
                <p><span class="text-gray-500">Nama file:</span> {{ $media->original_name }}</p>
                <p><span class="text-gray-500">MIME:</span> {{ $media->mime_type }}</p>
                <p><span class="text-gray-500">Ukuran:</span> {{ number_format($media->size / 1024, 1) }} KB</p>
                <p><span class="text-gray-500">Dimensi:</span> {{ $media->width ? $media->width.'×'.$media->height.' px' : '—' }}</p>
                <p><span class="text-gray-500">Diunggah:</span> {{ $media->created_at->format('d M Y H:i') }} oleh {{ $media->uploader?->name ?? '—' }}</p>
                <p><span class="text-gray-500">Path:</span> <code class="text-xs">{{ $media->path }}</code></p>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($media->trashed())
                    @can('update', $media)
                        <form method="POST" action="{{ route('admin.media.restore', $media) }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 transition">
                                Pulihkan dari Trash
                            </button>
                        </form>
                    @endcan

                    @can('delete', $media)
                        <form method="POST" action="{{ route('admin.media.force-destroy', $media) }}"
                              onsubmit="return confirm('Hapus PERMANEN file ini beserta thumbnailnya? Tindakan tidak bisa dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 transition">
                                Hapus Permanen
                            </button>
                        </form>
                    @endcan
                @else
                    @can('delete', $media)
                        <form method="POST" action="{{ route('admin.media.destroy', $media) }}"
                              onsubmit="return confirm('Pindahkan media ke trash?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-lg border border-red-300 text-red-600 text-sm font-semibold px-4 py-2 hover:bg-red-50 transition">
                                Pindah ke Trash
                            </button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>
    </div>
@endsection
