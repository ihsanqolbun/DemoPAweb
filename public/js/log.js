// log.js — Log Aktivitas
document.addEventListener('DOMContentLoaded', function () {
    // Auto-submit form saat filter dropdown berubah
    const filterSelects = document.querySelectorAll('select[name="aksi"], select[name="role"]');
    filterSelects.forEach(function (sel) {
        sel.addEventListener('change', function () {
            this.closest('form').submit();
        });
    });
});