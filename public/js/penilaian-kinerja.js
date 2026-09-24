document.addEventListener('DOMContentLoaded', function () {
    initDragScroll('pkTableScroll');
    initDragScroll('detailTableScroll');
    limitTahunInput();
    initLoadingSpinner();
});

// Drag-to-scroll untuk tabel lebar
function initDragScroll(id) {
    const el = document.getElementById(id);
    if (!el) return;

    if (el.scrollWidth > el.clientWidth) el.style.cursor = 'grab';

    let isDown = false, startX, scrollLeft;

    el.addEventListener('mousedown', (e) => {
        if (e.target.closest('a')) return;
        if (el.scrollWidth <= el.clientWidth) return;
        isDown = true;
        el.style.cursor = 'grabbing';
        startX     = e.pageX - el.offsetLeft;
        scrollLeft = el.scrollLeft;
    });

    el.addEventListener('mouseleave', () => { isDown = false; el.style.cursor = 'grab'; });
    el.addEventListener('mouseup',    () => { isDown = false; el.style.cursor = 'grab'; });

    el.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x    = e.pageX - el.offsetLeft;
        const walk = (x - startX) * 1.5;
        el.scrollLeft = scrollLeft - walk;
    });
}

// Batasi input tahun max = tahun sekarang
function limitTahunInput() {
    const input = document.querySelector('input[name="tahun"]');
    if (!input) return;
    const maxYear = parseInt(input.max, 10);
    if (!maxYear) return;
    const clamp = () => {
        if (parseInt(input.value, 10) > maxYear) input.value = maxYear;
    };
    input.addEventListener('change', clamp);
    input.addEventListener('blur', clamp);
}

// Loading spinner — muncul saat form submit atau link cell diklik
function initLoadingSpinner() {
    // Buat overlay
    const overlay = document.createElement('div');
    overlay.id = 'pk-loading-overlay';
    overlay.innerHTML = `
        <div class="pk-loading-box">
            <div class="pk-spinner"></div>
            <div class="pk-loading-text">Memuat data...</div>
        </div>
    `;
    document.body.appendChild(overlay);

    const show = () => { overlay.classList.add('active'); };

    // Trigger saat filter form submit
    const form = document.querySelector('.pk-filter');
    if (form) form.addEventListener('submit', show);

    // Trigger saat klik cell tabel atau card indikator
    document.querySelectorAll('.pk-cell, .pk-ind-card').forEach(el => {
        el.addEventListener('click', show);
    });

    // Sembunyikan spinner kalau back dari browser
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) overlay.classList.remove('active');
    });
}