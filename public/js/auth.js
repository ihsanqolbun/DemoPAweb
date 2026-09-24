// auth.js — SIPP Login Page

document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('togglePasswordBtn');
    if (!btn) return;

    btn.addEventListener('click', function () {
        const input    = document.getElementById('password');
        const icon     = document.getElementById('eyeIcon');
        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        icon.classList.toggle('fa-eye',      !isHidden);
        icon.classList.toggle('fa-eye-slash', isHidden);
    });
});