<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIPP') — Pengadilan Agama Tasikmalaya</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    @stack('styles')
</head>
<body>

@php $role = auth()->user()?->role; @endphp

{{-- SIDEBAR --}}
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <div class="logo-icon">
                <span>SIPP</span>
            </div>
            <div class="logo-text">
                Sistem Informasi<br>Penelusuran Perkara
            </div>
        </div>
        <button class="toggle-btn" id="toggleSidebarBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <div class="sidebar-menu">
        <div class="menu-title">Menu Utama</div>

        {{-- Dashboard: semua role --}}
        <a href="{{ route('dashboard') }}"
           class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           data-tooltip="Dashboard">
            <i class="fas fa-chart-bar"></i>
            <span class="menu-text">Dashboard</span>
        </a>

        <div class="menu-title">Modul</div>

        {{-- Rekap Perkara: semua role --}}
        <a href="{{ route('rekap-perkara.index') }}"
           class="menu-item {{ request()->routeIs('rekap-perkara.*') ? 'active' : '' }}"
           data-tooltip="Rekap Perkara">
            <i class="fas fa-file-alt"></i>
            <span class="menu-text">Rekap Perkara</span>
        </a>

        {{-- Data Pihak: super_admin + admin saja --}}
        @if(in_array($role, ['super_admin', 'admin']))
        <a href="{{ route('pihak.index') }}"
           class="menu-item {{ request()->routeIs('pihak.*') ? 'active' : '' }}"
           data-tooltip="Data Pihak">
            <i class="fas fa-users"></i>
            <span class="menu-text">Data Pihak</span>
        </a>
        @endif

        {{-- Penilaian Kinerja: semua kecuali hakim --}}
        @if($role !== 'hakim')
        <a href="{{ route('penilaian-kinerja.index') }}"
           class="menu-item {{ request()->routeIs('penilaian-kinerja.*') ? 'active' : '' }}"
           data-tooltip="Penilaian Kinerja">
            <i class="fas fa-star"></i>
            <span class="menu-text">Penilaian Kinerja</span>
        </a>
        @endif

        {{-- Manajemen User + Log: super_admin + admin --}}
        @if(in_array($role, ['super_admin', 'admin']))
        <div class="menu-title">Sistem</div>
        <a href="{{ route('users.index') }}"
           class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}"
           data-tooltip="Manajemen User">
            <i class="fas fa-cog"></i>
            <span class="menu-text">Manajemen User</span>
        </a>
        @endif

        {{-- Log Aktivitas: super_admin saja --}}
        @if($role === 'super_admin')
        <a href="{{ route('log.index') }}"
           class="menu-item {{ request()->routeIs('log.*') ? 'active' : '' }}"
           data-tooltip="Log Aktivitas">
            <i class="fas fa-list"></i>
            <span class="menu-text">Log Aktivitas</span>
        </a>
        @endif
    </div>

    {{-- User Info + Logout --}}
    <div class="sidebar-user">
        <div class="sidebar-avatar">
            {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ auth()->user()?->name ?? 'Admin' }}</div>
            <div class="sidebar-user-role">{{ ucfirst(str_replace('_', ' ', $role ?? '')) }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
            @csrf
            <button type="submit" class="sidebar-logout-btn" data-tooltip="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>
</div>

{{-- MAIN CONTENT --}}
<div class="main-content" id="mainContent">

    {{-- TOPBAR --}}
    <div class="header">
        <div class="header-left">
            <h1 class="header-title">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="header-right">
            <div class="header-date">
                <i class="fas fa-calendar-alt"></i>
                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </div>
            <div class="header-user">
                <i class="fas fa-user-circle"></i>
                {{ auth()->user()?->name ?? 'Admin' }}
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    <div style="padding: 0 28px">
        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
        @endif
    </div>

    {{-- CONTENT --}}
    <div class="content">
        @yield('content')
    </div>
</div>

<script src="{{ asset('js/global.js') }}"></script>
@stack('scripts')

</body>
</html>