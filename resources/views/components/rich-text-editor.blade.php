{{--
    Rich text editor (Tiptap) reusable.

    Pemakaian:
        <x-rich-text-editor name="translations[id][content]" :value="old('translations.id.content', $content)" />

    HTML editor disinkronkan ke hidden input `name`, sehingga terkirim
    sebagai field form biasa dan tervalidasi di backend seperti textarea.
    Sisip gambar memakai <x-media-picker /> global (satu per halaman).
--}}
@props(['name', 'value' => '', 'id' => null])

@php($editorId = $id ?? 'rte-'.md5($name))

@pushOnce('scripts')
    @vite(['resources/js/rich-text-editor.js'])
@endpushOnce

<div data-rich-text data-input="{{ $editorId }}-input" class="rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-[#F97316] focus-within:border-[#F97316]">
    <div data-rte-toolbar class="flex flex-wrap items-center gap-1 border-b border-gray-200 bg-gray-50 px-2 py-1.5" role="toolbar" aria-label="Format teks">
        <button type="button" data-command="paragraph" title="Paragraf" class="rte-btn">P</button>
        <button type="button" data-command="h2" title="Heading 2" class="rte-btn">H2</button>
        <button type="button" data-command="h3" title="Heading 3" class="rte-btn">H3</button>
        <button type="button" data-command="h4" title="Heading 4" class="rte-btn">H4</button>
        <span class="rte-sep"></span>
        <button type="button" data-command="bold" title="Tebal" aria-label="Tebal" class="rte-btn font-bold">B</button>
        <button type="button" data-command="italic" title="Miring" aria-label="Miring" class="rte-btn italic">I</button>
        <button type="button" data-command="underline" title="Garis bawah" aria-label="Garis bawah" class="rte-btn underline">U</button>
        <button type="button" data-command="strike" title="Coret" aria-label="Coret" class="rte-btn line-through">S</button>
        <span class="rte-sep"></span>
        <button type="button" data-command="bullet-list" title="Bullet list" class="rte-btn">•≡</button>
        <button type="button" data-command="ordered-list" title="Numbered list" class="rte-btn">1≡</button>
        <button type="button" data-command="blockquote" title="Kutipan" class="rte-btn">❝</button>
        <span class="rte-sep"></span>
        <button type="button" data-command="align-left" title="Rata kiri" class="rte-btn">⭰</button>
        <button type="button" data-command="align-center" title="Rata tengah" class="rte-btn">⬌</button>
        <button type="button" data-command="align-right" title="Rata kanan" class="rte-btn">⭲</button>
        <button type="button" data-command="align-justify" title="Rata kanan-kiri" class="rte-btn">☰</button>
        <span class="rte-sep"></span>
        <button type="button" data-command="link" title="Sisip/edit tautan" class="rte-btn">🔗</button>
        <button type="button" data-command="unlink" title="Lepas tautan" class="rte-btn">⛓‍💥</button>
        <span class="rte-sep"></span>
        <button type="button" data-command="table-insert" title="Sisip tabel 3×3" class="rte-btn">▦</button>
        <button type="button" data-command="table-add-row" title="Tambah baris" class="rte-btn">+⎯</button>
        <button type="button" data-command="table-add-col" title="Tambah kolom" class="rte-btn">+|</button>
        <button type="button" data-command="table-delete" title="Hapus tabel" class="rte-btn">🗑▦</button>
        <span class="rte-sep"></span>
        <button type="button" data-command="image" title="Sisip gambar dari Media Library" class="rte-btn">🖼</button>
        <button type="button" data-command="hr" title="Garis horizontal" class="rte-btn">―</button>
        <span class="rte-sep"></span>
        <button type="button" data-command="undo" title="Urungkan" class="rte-btn">↩</button>
        <button type="button" data-command="redo" title="Ulangi" class="rte-btn">↪</button>
    </div>

    <div data-rte-linkbar class="hidden items-center gap-2 border-b border-gray-200 bg-amber-50 px-3 py-2 [&:not(.hidden)]:flex">
        <input type="text" data-rte-link-input placeholder="https://… atau /halaman"
               class="flex-1 rounded-md border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-[#F97316] aria-[invalid=true]:border-red-500">
        <button type="button" data-rte-link-apply class="rounded-md bg-[#0F2A54] px-3 py-1 text-sm font-semibold text-white">Terapkan</button>
        <button type="button" data-rte-link-cancel class="rounded-md px-2 py-1 text-sm text-gray-600 hover:text-gray-900">Batal</button>
    </div>

    <div data-rte-editable class="min-h-48 bg-white px-4 py-3"></div>

    <input type="hidden" id="{{ $editorId }}-input" name="{{ $name }}" value="{{ $value }}">
</div>
