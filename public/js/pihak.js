// pihak.js — SIPP

document.addEventListener('DOMContentLoaded', function () {

    // ========================
    // Auto-submit search saat ngetik (debounce 500ms)
    // ========================
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.closest('form').submit();
            }, 500);
        });
    }

    // ========================
    // Konfirmasi sebelum submit form edit
    // ========================
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            const nama = document.querySelector('input[name="nama"]')?.value.trim();
            const alamat = document.querySelector('textarea[name="alamat"]')?.value.trim();
            const pekerjaan = document.querySelector('input[name="pekerjaan"]')?.value.trim();

            if (!nama || !alamat || !pekerjaan) {
                e.preventDefault();
                alert('Nama, Alamat, dan Pekerjaan wajib diisi.');
                return;
            }
        });
    }

    // ========================
    // Keyboard shortcut
    // ========================
    document.addEventListener('keydown', function (e) {
        // Ctrl+F — fokus ke search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            const searchInput = document.querySelector('.search-box input');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        }
    });

    // ========================
    // Preserve scroll position saat search
    // ========================
    const SCROLL_KEY = 'sipp_scroll_pos';

    // Restore scroll position setelah load
    const savedScroll = sessionStorage.getItem(SCROLL_KEY);
    if (savedScroll) {
        window.scrollTo(0, parseInt(savedScroll));
        sessionStorage.removeItem(SCROLL_KEY);
    }

    // Simpan posisi scroll sebelum form submit
    document.querySelectorAll('.table-search-bar, .detail-search-form').forEach(function(form) {
        form.addEventListener('submit', function() {
            sessionStorage.setItem(SCROLL_KEY, window.scrollY);
        });
    });

    // Simpan juga untuk per-page select
    document.querySelectorAll('.per-page-select').forEach(function(sel) {
        sel.addEventListener('change', function() {
            sessionStorage.setItem(SCROLL_KEY, window.scrollY);
        });
    });
});