import './bootstrap';

/**
 * Tablet photograph gallery — slider with fslightbox integration
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-tablet-gallery]').forEach(initGallery);
});

function initGallery(el) {
    const sources = JSON.parse(el.dataset.sources || '[]');
    if (!sources.length) return;

    const mainImg     = el.querySelector('[data-gallery-main]');
    const counter     = el.querySelector('[data-gallery-counter]');
    const thumbsWrap  = el.querySelector('[data-gallery-thumbs]');
    const prevBtn     = el.querySelector('[data-gallery-prev]');
    const nextBtn     = el.querySelector('[data-gallery-next]');
    const thumbs      = thumbsWrap ? [...thumbsWrap.querySelectorAll('button')] : [];
    const lightboxKey = el.dataset.lightboxKey;

    const total   = sources.length;
    let current   = 0;
    let animating = false;

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function goto(idx) {
        if (animating) return;
        const next = ((idx % total) + total) % total;
        if (next === current && idx === current) return;

        animating = true;
        current = next;

        // Crossfade
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.src = sources[current];
            mainImg.onload = () => {
                mainImg.style.opacity = '1';
                animating = false;
            };
            // Fallback if image is cached (onload may not fire)
            if (mainImg.complete) {
                mainImg.style.opacity = '1';
                animating = false;
            }
        }, 180);

        // Counter
        if (counter) {
            counter.textContent = `${pad(current + 1)} / ${pad(total)}`;
        }

        // Thumbnails
        thumbs.forEach((btn, i) => {
            btn.classList.toggle('gallery-thumb--active', i === current);
        });

        // Scroll active thumb into view
        if (thumbs[current]) {
            thumbs[current].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    // Navigation
    if (prevBtn) prevBtn.addEventListener('click', () => goto(current - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goto(current + 1));

    thumbs.forEach((btn, i) => {
        btn.addEventListener('click', () => goto(i));
    });

    // Click main image → open fslightbox at current index
    if (mainImg) {
        mainImg.addEventListener('click', () => {
            if (lightboxKey && window.fsLightboxInstances && window.fsLightboxInstances[lightboxKey]) {
                window.fsLightboxInstances[lightboxKey].open(current);
            }
        });
    }

    // Keyboard navigation (when gallery is focused/hovered)
    el.setAttribute('tabindex', '0');
    el.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') { e.preventDefault(); goto(current - 1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); goto(current + 1); }
    });
}
