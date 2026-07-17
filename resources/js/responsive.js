/**
 * Deteksi perangkat & terapkan class is-mobile / is-desktop pada body.
 * Membuat layout berubah otomatis saat resize atau rotasi layar.
 */
const MOBILE_BREAKPOINT = 992;

function applyViewportMode() {
    const isMobile = window.innerWidth <= MOBILE_BREAKPOINT;
    const body = document.body;

    body.classList.toggle('is-mobile', isMobile);
    body.classList.toggle('is-desktop', !isMobile);
    body.classList.toggle('has-mobile-nav', isMobile);

    document.documentElement.style.setProperty(
        '--viewport-mode',
        isMobile ? 'mobile' : 'desktop'
    );
}

applyViewportMode();

let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(applyViewportMode, 120);
});

window.addEventListener('orientationchange', () => {
    setTimeout(applyViewportMode, 150);
});
