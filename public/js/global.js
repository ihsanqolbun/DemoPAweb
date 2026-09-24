// global.js — SIPP Laravel

document.addEventListener('DOMContentLoaded', function () {

    // ========================
    // Toggle Sidebar
    // ========================
    const toggleBtn   = document.getElementById('toggleSidebarBtn');
    const sidebar     = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    if (toggleBtn && sidebar && mainContent) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });

        // Restore state dari localStorage
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    }

    // ========================
    // Auto-hide alerts setelah 4 detik
    // ========================
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.4s';
            alert.style.opacity    = '0';
            setTimeout(function () { alert.remove(); }, 400);
        }, 4000);
    });

});