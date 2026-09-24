<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIPP Pengadilan Agama Tasikmalaya</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="auth-page">

<div class="login-wrapper">

    {{-- Panel Kiri --}}
    <div class="login-brand">
        <div class="brand-logo">SIPP</div>
        <div class="brand-title">Sistem Informasi Penelusuran Perkara</div>
        <div class="brand-sub">
            Pengadilan Agama Tasikmalaya<br>
            Kelas 1A
        </div>
        <ul class="brand-features">
            <li><i class="fas fa-chart-bar"></i> Rekap perkara real-time</li>
            <li><i class="fas fa-users"></i> Manajemen data pihak</li>
            <li><i class="fas fa-star"></i> Penilaian kinerja pegawai</li>
            <li><i class="fas fa-file-export"></i> Export PDF & Excel</li>
        </ul>
    </div>

    {{-- Panel Kanan — Form Login --}}
    <div class="login-form-panel">
        <div class="form-title">Selamat datang</div>
        <div class="form-subtitle">Masuk dengan akun yang telah diberikan</div>

        @if($errors->has('username'))
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ $errors->first('username') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            {{-- Username --}}
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                        value="{{ old('username') }}"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        autofocus
                    >
                </div>
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-password" id="togglePasswordBtn">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            {{-- Remember Me --}}
            <div class="form-remember">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Masuk
            </button>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} Pengadilan Agama Tasikmalaya
        </div>
    </div>
</div>

<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>