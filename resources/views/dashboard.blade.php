@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')

{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="welcome-banner-text">
        <div class="welcome-banner-title">
            Selamat datang, {{ auth()->user()->name ?? 'Admin' }}
        </div>
        <div class="welcome-banner-sub">
            {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            — Pengadilan Agama Tasikmalaya
        </div>
    </div>
    <div class="welcome-banner-stats">
        <div class="welcome-stat">
            <div class="welcome-stat-val">{{ $totalMasukBulanIni }}</div>
            <div class="welcome-stat-label">Perkara Bulan Ini</div>
        </div>
        <div class="welcome-stat">
            <div class="welcome-stat-val">
                {{ $totalMasukBulanIni > 0 ? round((($putus + $minutasi) / $totalMasukBulanIni) * 100) : 0 }}%
            </div>
            <div class="welcome-stat-label">Terselesaikan</div>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Perkara Masuk Bulan Ini</div>
        <div class="stat-value green">{{ $totalMasukBulanIni }}</div>
        <div class="stat-sub">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM Y') }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Dalam Proses</div>
        <div class="stat-value amber">{{ $dalamProses }}</div>
        <div class="stat-sub">Belum diputus</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Perkara Putus</div>
        <div class="stat-value blue">{{ $putus }}</div>
        <div class="stat-sub">Sudah ada putusan</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Mendekati Deadline</div>
        <div class="stat-value red">{{ $mendekatiDeadline }}</div>
        <div class="stat-sub">Perlu perhatian</div>
    </div>
</div>

{{-- Dashboard Grid --}}
<div class="dashboard-grid">

    {{-- Bar Chart Perkara per Jenis --}}
    <div class="card">
        <div class="card-title">
            Perkara per jenis
            <span class="card-title-sub">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM Y') }}</span>
        </div>
        @forelse($jenisPerkaraData as $item)
            @php $maxCount = $jenisPerkaraData->max('jumlah') ?: 1; @endphp
            <div class="bar-row">
                <div class="bar-label" title="{{ $item['nama'] }}">{{ $item['nama'] }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ round(($item['jumlah'] / $maxCount) * 100) }}%"></div>
                </div>
                <div class="bar-count">{{ $item['jumlah'] }}</div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Tidak ada data bulan ini</p>
            </div>
        @endforelse
    </div>

    {{-- Notifikasi --}}
    <div class="card">
        <div class="card-title">
            Notifikasi & reminder
            @if($notifikasi->count() > 0)
                <span class="card-title-sub" style="color:#e74c3c">{{ $notifikasi->count() }} baru</span>
            @endif
        </div>
        @forelse($notifikasi as $notif)
            <div class="notif-item">
                <div class="notif-dot" style="background: {{ $notif['warna'] }}"></div>
                <div>
                    <div class="notif-text">{{ $notif['pesan'] }}</div>
                    <div class="notif-time">{{ $notif['waktu'] }}</div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-check-circle" style="color:#1D9E75"></i>
                <p>Tidak ada notifikasi</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Akses Cepat --}}
<div class="card" style="margin-top:16px">
    <div class="card-title">Akses cepat</div>
    <div class="shortcut-grid">
        <a href="{{ route('rekap-perkara.index') }}" class="shortcut-item">
            <div class="shortcut-icon"><i class="fas fa-file-alt"></i></div>
            <div class="shortcut-label">Rekap Perkara</div>
        </a>
        <a href="{{ route('rekap-perkara.export.pdf', ['tanggal' => today()->toDateString()]) }}" class="shortcut-item" target="_blank">
            <div class="shortcut-icon red-icon"><i class="fas fa-file-pdf"></i></div>
            <div class="shortcut-label">Export PDF</div>
        </a>
        <a href="{{ route('rekap-perkara.export.excel', ['tanggal' => today()->toDateString()]) }}" class="shortcut-item">
            <div class="shortcut-icon excel-icon"><i class="fas fa-file-excel"></i></div>
            <div class="shortcut-label">Export Excel</div>
        </a>
        <a href="{{ route('pihak.index') }}" class="shortcut-item">
            <div class="shortcut-icon blue-icon"><i class="fas fa-users"></i></div>
            <div class="shortcut-label">Data Pihak</div>
        </a>
        <a href="{{ route('penilaian-kinerja.index') }}" class="shortcut-item">
            <div class="shortcut-icon amber-icon"><i class="fas fa-star"></i></div>
            <div class="shortcut-label">Penilaian Kinerja</div>
        </a>
        @if(auth()->user()?->role === 'super_admin')
        <a href="{{ route('users.index') }}" class="shortcut-item">
            <div class="shortcut-icon dark-icon"><i class="fas fa-cog"></i></div>
            <div class="shortcut-label">Manajemen User</div>
        </a>
        @endif
    </div>
</div>

@endsection