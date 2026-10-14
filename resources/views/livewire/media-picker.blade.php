{{-- Entangle agar state $open Livewire tersinkron ke Alpine (x-show);
     dengarkan juga event DOM untuk pemicu dari JavaScript biasa (toolbar RTE). --}}
<div x-data="{ open: $wire.entangle('open') }" x-on:open-media-picker-dom.window="$wire.openPicker()">
    {{-- Tombol pemicu Livewire oleh modul pemakai: wire:click="$dispatch('open-media-picker')" --}}

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="display:none">
        <div class="absolute inset-0 bg-black/60" wire:click="close"></div>

        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3">
                <h2 class="font-bold text-[#0F2A54]">Pilih Media</h2>
                <button type="button" wire:click="close" class="text-gray-400 hover:text-gray-700 text-xl leading-none">×</button>
            </div>

            <div class="px-5 py-3 flex items-center gap-3 border-b border-gray-100">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari…"
                       class="rounded-lg border border-gray-300 px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-[#F97316]">

                @if ($mode === '')
                    <select wire:model.live="type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="image">Gambar</option>
                        <option value="document">PDF</option>
                    </select>
                @endif
            </div>

            <div class="p-5 overflow-y-auto flex-1">
                @if (auth()->user()?->cannot('viewAny', App\Models\MediaAsset::class))
                    <p class="text-sm text-gray-500">Anda tidak memiliki akses ke media library.</p>
                @elseif ($assets->isEmpty())
                    <p class="text-sm text-gray-500">Tidak ada media ditemukan. Unggah lewat menu Media Library.</p>
                @else
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                        @foreach ($assets as $asset)
                            <button type="button" wire:click="pick({{ $asset->id }})"
                                    class="group rounded-lg border border-gray-200 overflow-hidden hover:border-[#F97316] hover:shadow transition text-left">
                                <div class="h-24 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    @if ($asset->thumbnailUrl())
                                        <img src="{{ $asset->thumbnailUrl() }}" alt="{{ $asset->alt ?: $asset->title }}" class="max-h-full max-w-full object-contain">
                                    @elseif ($asset->isImage())
                                        <img src="{{ $asset->url() }}" alt="{{ $asset->alt ?: $asset->title }}" class="max-h-full max-w-full object-contain">
                                    @else
                                        <span class="text-xl font-bold text-gray-400">PDF</span>
                                    @endif
                                </div>
                                <div class="px-2 py-1.5">
                                    <p class="text-xs font-medium truncate">{{ $asset->title ?: $asset->original_name }}</p>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $assets->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
