<?php

namespace App\Livewire;

use App\Models\MediaAsset;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Modal Media Picker reusable untuk SEMUA modul (produk, galeri, halaman…).
 *
 * Pemakaian di view modul mana pun:
 *
 *   <button type="button" wire:click="$dispatch('open-media-picker')">Pilih Media</button>
 *   <x-media-picker />
 *
 *   <script>
 *   window.addEventListener('media-picked', (event) => {
 *       // event.detail: { id, url, thumbnail_url, title, name, mime }
 *   });
 *   </script>
 *
 * Properti opsional: <livewire:media-picker mode="image" /> (image|document|semua).
 */
class MediaPicker extends Component
{
    use WithPagination;

    public bool $open = false;

    public string $search = '';

    public string $type = '';

    /** image | document | (kosong = semua) */
    public string $mode = '';

    #[On('open-media-picker')]
    public function openPicker(): void
    {
        $this->open = true;

        $this->resetPage();
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function pick(int $mediaId): void
    {
        $asset = MediaAsset::query()
            ->with('variants')
            ->findOrFail($mediaId);

        $this->dispatch('media-picked', [
            'id' => $asset->id,
            'url' => $asset->url(),
            'thumbnail_url' => $asset->thumbnailUrl() ?? $asset->url(),
            'title' => $asset->title,
            'name' => $asset->original_name,
            'mime' => $asset->mime_type,
        ]);

        $this->open = false;
    }

    public function render()
    {
        $assets = collect();

        if (auth()->user()?->can('viewAny', MediaAsset::class)) {
            $assets = MediaAsset::query()
                ->with('variants')
                ->when($this->type === 'image', fn ($q) => $q->where('mime_type', 'like', 'image/%'))
                ->when($this->type === 'document', fn ($q) => $q->where('mime_type', 'application/pdf'))
                ->when($this->mode === 'image', fn ($q) => $q->where('mime_type', 'like', 'image/%'))
                ->when($this->mode === 'document', fn ($q) => $q->where('mime_type', 'application/pdf'))
                ->when($this->search !== '', function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('title', 'like', "%{$this->search}%")
                            ->orWhere('original_name', 'like', "%{$this->search}%");
                    });
                })
                ->latest()
                ->paginate(12);
        }

        return view('livewire.media-picker', [
            'assets' => $assets,
        ]);
    }
}
