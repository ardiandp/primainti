{{--
    Media Picker reusable — dipakai modul mana pun (produk, galeri, halaman, dst).

    Contoh pemakaian:

        <button type="button" wire:click="$dispatch('open-media-picker')">Pilih Media</button>
        <x-media-picker />

        <script>
        window.addEventListener('media-picked', (event) => {
            // event.detail = { id, url, thumbnail_url, title, name, mime }
            document.querySelector('#media-id').value = event.detail.id;
        });
        </script>

    Varian: <x-media-picker mode="image" /> atau <x-media-picker mode="document" />
--}}
<livewire:media-picker :mode="$mode ?? ''" />
