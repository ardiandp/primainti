import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import Link from '@tiptap/extension-link';
import { Table, TableRow, TableHeader, TableCell } from '@tiptap/extension-table';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';

/**
 * Rich text editor Tiptap reusable.
 *
 * Pemakaian: <x-rich-text-editor name="..." value="..." />
 * Setiap root [data-rich-text] diinisialisasi independen (mendukung
 * multi-bahasa ID/EN dalam satu form). HTML disinkronkan ke hidden input
 * bernama `$name` sehingga terkirim sebagai field form biasa.
 *
 * Sisip gambar memakai Media Picker global (satu <x-media-picker /> per
 * halaman): toolbar mengirim event pembuka, lalu hasil `media-picked`
 * dimasukkan ke editor yang sedang meminta.
 */

/** Editor yang sedang menunggu hasil Media Picker. */
let pendingImageEditor = null;

function openMediaPicker(editor) {
    pendingImageEditor = editor;

    // Jalur Alpine/DOM (selalu tersedia).
    window.dispatchEvent(new CustomEvent('open-media-picker-dom'));

    // Jalur Livewire (bila runtime Livewire termuat).
    if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
        window.Livewire.dispatch('open-media-picker');
    }
}

window.addEventListener('media-picked', (event) => {
    if (!pendingImageEditor) {
        return;
    }

    const editor = pendingImageEditor;
    pendingImageEditor = null;

    // Livewire mengirim params sebagai detail array atau objek — dukung keduanya.
    const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;

    if (!detail || !detail.url) {
        return;
    }

    editor.chain().focus();

    if (detail.mime === 'application/pdf') {
        // Dokumen: sisipkan sebagai tautan unduhan, bukan gambar.
        editor
            .chain()
            .insertContent({
                type: 'paragraph',
                content: [
                    {
                        type: 'text',
                        text: detail.title || detail.name || 'Unduh dokumen',
                        marks: [{ type: 'link', attrs: { href: detail.url } }],
                    },
                ],
            })
            .run();
    } else {
        editor.chain().setImage({ src: detail.url, alt: detail.title || '' }).run();
    }
});

function isSafeUrl(raw) {
    const value = (raw || '').trim();

    if (value === '') {
        return false;
    }

    // Relatif (/path, #anchor) atau http(s)/mailto saja.
    return /^(#|\/|https?:\/\/|mailto:)/i.test(value);
}

/** Perbarui status aktif/nonaktif tombol toolbar sesuai seleksi. */
function refreshToolbar(editor, toolbar) {
    const checks = {
        bold: () => editor.isActive('bold'),
        italic: () => editor.isActive('italic'),
        underline: () => editor.isActive('underline'),
        strike: () => editor.isActive('strike'),
        blockquote: () => editor.isActive('blockquote'),
        'bullet-list': () => editor.isActive('bulletList'),
        'ordered-list': () => editor.isActive('orderedList'),
        h2: () => editor.isActive('heading', { level: 2 }),
        h3: () => editor.isActive('heading', { level: 3 }),
        h4: () => editor.isActive('heading', { level: 4 }),
        paragraph: () => editor.isActive('paragraph'),
        'align-left': () => editor.isActive({ textAlign: 'left' }),
        'align-center': () => editor.isActive({ textAlign: 'center' }),
        'align-right': () => editor.isActive({ textAlign: 'right' }),
        'align-justify': () => editor.isActive({ textAlign: 'justify' }),
        link: () => editor.isActive('link'),
    };

    toolbar.querySelectorAll('[data-command]').forEach((button) => {
        const check = checks[button.dataset.command];

        if (check) {
            button.classList.toggle('is-active', check());
            button.setAttribute('aria-pressed', check() ? 'true' : 'false');
        }

        if (button.dataset.command === 'undo') {
            button.disabled = !editor.can().undo();
        }

        if (button.dataset.command === 'redo') {
            button.disabled = !editor.can().redo();
        }
    });
}

function hideLinkBar(root) {
    const bar = root.querySelector('[data-rte-linkbar]');

    if (bar) {
        bar.classList.add('hidden');
    }
}

function runCommand(editor, root, command, value) {
    const chain = editor.chain().focus();

    switch (command) {
        case 'bold':
            chain.toggleBold().run();
            break;
        case 'italic':
            chain.toggleItalic().run();
            break;
        case 'underline':
            chain.toggleUnderline().run();
            break;
        case 'strike':
            chain.toggleStrike().run();
            break;
        case 'h2':
            chain.toggleHeading({ level: 2 }).run();
            break;
        case 'h3':
            chain.toggleHeading({ level: 3 }).run();
            break;
        case 'h4':
            chain.toggleHeading({ level: 4 }).run();
            break;
        case 'paragraph':
            chain.setParagraph().run();
            break;
        case 'bullet-list':
            chain.toggleBulletList().run();
            break;
        case 'ordered-list':
            chain.toggleOrderedList().run();
            break;
        case 'blockquote':
            chain.toggleBlockquote().run();
            break;
        case 'align-left':
            chain.setTextAlign('left').run();
            break;
        case 'align-center':
            chain.setTextAlign('center').run();
            break;
        case 'align-right':
            chain.setTextAlign('right').run();
            break;
        case 'align-justify':
            chain.setTextAlign('justify').run();
            break;
        case 'undo':
            chain.undo().run();
            break;
        case 'redo':
            chain.redo().run();
            break;
        case 'hr':
            chain.setHorizontalRule().run();
            break;
        case 'link': {
            if (editor.isActive('link')) {
                chain.unsetLink().run();
                hideLinkBar(root);
            } else {
                const bar = root.querySelector('[data-rte-linkbar]');
                const input = root.querySelector('[data-rte-link-input]');

                if (bar && input) {
                    input.value = editor.getAttributes('link').href || '';
                    bar.classList.remove('hidden');
                    input.focus();
                }
            }
            break;
        }
        case 'unlink':
            chain.unsetLink().run();
            hideLinkBar(root);
            break;
        case 'table-insert':
            chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
            break;
        case 'table-add-row':
            chain.addRowAfter().run();
            break;
        case 'table-add-col':
            chain.addColumnAfter().run();
            break;
        case 'table-delete':
            chain.deleteTable().run();
            break;
        case 'image':
            openMediaPicker(editor);
            break;
        default:
            break;
    }

    refreshToolbar(editor, root.querySelector('[data-rte-toolbar]'));
}

function initRichText(root) {
    if (root.dataset.rteReady === '1') {
        return;
    }

    root.dataset.rteReady = '1';

    const editable = root.querySelector('[data-rte-editable]');
    const toolbar = root.querySelector('[data-rte-toolbar]');
    const input = document.getElementById(root.dataset.input);

    if (!editable || !toolbar || !input) {
        return;
    }

    const editor = new Editor({
        element: editable,
        extensions: [
            StarterKit.configure({ heading: { levels: [2, 3, 4] } }),
            Underline,
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
            Link.configure({
                openOnClick: false,
                autolink: true,
                HTMLAttributes: { rel: 'noopener noreferrer', target: '_blank' },
            }),
            Table.configure({ resizable: false }),
            TableRow,
            TableHeader,
            TableCell,
            Image.configure({ inline: false, HTMLAttributes: { loading: 'lazy' } }),
            Placeholder.configure({ placeholder: 'Tulis konten halaman…' }),
        ],
        content: input.value || '',
        editorProps: {
            attributes: { class: 'rte-content rte-editable' },
        },
        onUpdate: ({ editor: updated }) => {
            input.value = updated.getHTML();
        },
    });

    toolbar.addEventListener('click', (event) => {
        const button = event.target.closest('[data-command]');

        if (!button || button.disabled) {
            return;
        }

        event.preventDefault();
        runCommand(editor, root, button.dataset.command, button.dataset.value);
    });

    const linkApply = root.querySelector('[data-rte-link-apply]');
    const linkInput = root.querySelector('[data-rte-link-input]');
    const linkCancel = root.querySelector('[data-rte-link-cancel]');

    if (linkApply && linkInput) {
        linkApply.addEventListener('click', (event) => {
            event.preventDefault();

            if (!isSafeUrl(linkInput.value)) {
                linkInput.setAttribute('aria-invalid', 'true');
                linkInput.focus();
                return;
            }

            linkInput.removeAttribute('aria-invalid');
            editor.chain().focus().setLink({ href: linkInput.value.trim() }).run();
            hideLinkBar(root);
            refreshToolbar(editor, toolbar);
        });
    }

    if (linkCancel) {
        linkCancel.addEventListener('click', (event) => {
            event.preventDefault();
            hideLinkBar(root);
            editor.commands.focus();
        });
    }

    editor.on('transaction', () => refreshToolbar(editor, toolbar));
    editor.on('selectionUpdate', () => refreshToolbar(editor, toolbar));
    refreshToolbar(editor, toolbar);
}

document.querySelectorAll('[data-rich-text]').forEach(initRichText);
