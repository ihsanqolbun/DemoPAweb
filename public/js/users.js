// users.js — Manajemen User
// SIPP Laravel — Pengadilan Agama Tasikmalaya

document.addEventListener('DOMContentLoaded', function () {

    // ================================================
    // Tutup modal saat klik overlay
    // ================================================
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    // ================================================
    // Tutup modal dengan tombol Escape
    // ================================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function (m) {
                m.classList.remove('active');
            });
        }
    });

});

// ================================================
// Modal Hapus User
// ================================================
function confirmDelete(userId, username) {
    const modal    = document.getElementById('deleteModal');
    const nameEl   = document.getElementById('deleteUsername');
    const form     = document.getElementById('deleteForm');

    if (!modal || !nameEl || !form) return;

    nameEl.textContent = username;
    form.action = '/users/' + userId;

    modal.classList.add('active');
}

function closeModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.classList.remove('active');
}

// ================================================
// Modal Reset Password
// ================================================
function openResetModal() {
    const modal = document.getElementById('resetModal');
    if (modal) modal.classList.add('active');
}

function closeResetModal() {
    const modal = document.getElementById('resetModal');
    if (modal) modal.classList.remove('active');
}

// ================================================
// Toggle show/hide password
// ================================================
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
    } else {
        input.type = 'password';
        if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
}