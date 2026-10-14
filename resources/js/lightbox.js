/**
 * Lightbox galeri yang dapat dioperasikan keyboard.
 *
 * - Enter/Space pada thumbnail membuka (button natif).
 * - Esc menutup; ←/→ navigasi; Tab terperangkap dalam dialog.
 * - Fokus dikembalikan ke thumbnail pemicu saat dialog ditutup.
 */
(function () {
    const gallery = document.querySelector('[data-lightbox-gallery]');
    const lightbox = document.querySelector('[data-lightbox]');

    if (!gallery || !lightbox) {
        return;
    }

    const image = lightbox.querySelector('[data-lightbox-image]');
    const caption = lightbox.querySelector('[data-lightbox-caption]');
    const closeButton = lightbox.querySelector('[data-lightbox-close]');
    const prevButton = lightbox.querySelector('[data-lightbox-prev]');
    const nextButton = lightbox.querySelector('[data-lightbox-next]');
    const backdrop = lightbox.querySelector('[data-lightbox-backdrop]');

    const triggers = Array.from(gallery.querySelectorAll('[data-lightbox-item]'));

    let current = 0;
    let opener = null;

    function render() {
        const trigger = triggers[current];

        if (!trigger) {
            return;
        }

        image.src = trigger.dataset.full || '';
        image.alt = trigger.dataset.alt || '';
        caption.textContent = trigger.dataset.caption || '';
    }

    function open(index, trigger) {
        current = (index + triggers.length) % triggers.length;
        opener = trigger || null;

        render();
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
        document.body.style.overflow = 'hidden';
        closeButton.focus();
    }

    function close() {
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
        document.body.style.overflow = '';

        if (opener) {
            opener.focus();
            opener = null;
        }
    }

    function step(delta) {
        current = (current + delta + triggers.length) % triggers.length;
        render();
    }

    triggers.forEach((trigger, index) => {
        trigger.addEventListener('click', () => open(index, trigger));
    });

    closeButton.addEventListener('click', close);
    backdrop.addEventListener('click', close);
    prevButton.addEventListener('click', () => step(-1));
    nextButton.addEventListener('click', () => step(1));

    document.addEventListener('keydown', (event) => {
        if (lightbox.classList.contains('hidden')) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            close();
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            step(-1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            step(1);
        } else if (event.key === 'Tab') {
            // Perangkap fokus sederhana dalam dialog.
            const focusables = [prevButton, closeButton, nextButton].filter(Boolean);
            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });
})();
