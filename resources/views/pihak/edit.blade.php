@extends('layouts.app')

@section('title', 'Edit Data Pihak')
@section('page-title', 'Edit Data Pihak')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pihak.css') }}">
@endpush

@section('content')

{{-- Header bar --}}
<div class="detail-header-bar" style="margin-bottom:16px">
    <a href="{{ route('pihak.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <div class="detail-header-info">
        <div class="detail-header-title">Edit Data Pihak</div>
        <div class="detail-header-periode">ID: {{ $pihak->id }}</div>
    </div>
</div>

{{-- Info tabel --}}
<div class="tabel-info-card">
    <div class="tabel-info-title">
        <i class="fas fa-database"></i> Keberadaan data ini di database
    </div>
    <div class="tabel-info-list">
        <div class="tabel-info-item {{ true ? 'found' : '' }}">
            <i class="fas fa-check-circle"></i>
            <span>pihak</span>
        </div>
        <div class="tabel-info-item {{ $inPihak1 ? 'found' : 'notfound' }}">
            <i class="fas fa-{{ $inPihak1 ? 'check-circle' : 'times-circle' }}"></i>
            <span>perkara_pihak1</span>
        </div>
        <div class="tabel-info-item {{ $inPihak2 ? 'found' : 'notfound' }}">
            <i class="fas fa-{{ $inPihak2 ? 'check-circle' : 'times-circle' }}"></i>
            <span>perkara_pihak2</span>
        </div>
        <div class="tabel-info-item {{ $inEacValidasi ? 'found' : 'notfound' }}">
            <i class="fas fa-{{ $inEacValidasi ? 'check-circle' : 'times-circle' }}"></i>
            <span>aps_badilag.eac_validasi</span>
        </div>
    </div>
    <div class="tabel-info-note">
        <i class="fas fa-info-circle"></i>
        Update hanya akan diterapkan ke tabel yang memiliki data dengan ID ini.
        Tabel yang tidak memiliki data tidak akan terpengaruh.
    </div>
</div>

{{-- Form --}}
<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">
            <i class="fas fa-user-edit"></i> Informasi Data Pihak
        </div>
    </div>

    <form method="POST" action="{{ route('pihak.update', $pihak->id) }}" class="edit-form" id="editForm">
        @csrf
        @method('PUT')

        <div class="form-body">
            {{-- ID readonly --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-fingerprint"></i> Identitas
                </div>
                <div class="form-group">
                    <label class="form-label">ID Pihak</label>
                    <input type="text" class="form-control readonly" value="{{ $pihak->id }}" readonly>
                    <span class="form-help">ID tidak dapat diubah</span>
                </div>
            </div>

            {{-- Data Utama --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-user"></i> Data Utama
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Nama <span class="required">*</span>
                    </label>
                    <input type="text" name="nama" class="form-control @error('nama') is-error @enderror"
                           value="{{ old('nama', $pihak->nama) }}" maxlength="500" required>
                    @error('nama')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Alamat <span class="required">*</span>
                    </label>
                    <textarea name="alamat" class="form-control form-textarea @error('alamat') is-error @enderror"
                              maxlength="500" required>{{ old('alamat', $pihak->alamat) }}</textarea>
                    @error('alamat')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Pekerjaan <span class="required">*</span>
                    </label>
                    <input type="text" name="pekerjaan" class="form-control @error('pekerjaan') is-error @enderror"
                           value="{{ old('pekerjaan', $pihak->pekerjaan) }}" maxlength="255" required>
                    @error('pekerjaan')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Data Kelahiran --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-calendar-alt"></i> Data Kelahiran
                    <span class="section-optional">(Opsional)</span>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control"
                               value="{{ old('tempat_lahir', $pihak->tempat_lahir) }}" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control"
                               value="{{ old('tanggal_lahir', $pihak->tanggal_lahir) }}">
                    </div>
                </div>
            </div>

            {{-- Keterangan --}}
            <div class="form-section" style="border-bottom:none">
                <div class="form-section-title">
                    <i class="fas fa-sticky-note"></i> Keterangan
                    <span class="section-optional">(Opsional)</span>
                </div>
                <div class="form-group">
                    <textarea name="keterangan" class="form-control form-textarea"
                              maxlength="255">{{ old('keterangan', $pihak->keterangan) }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('pihak.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/pihak.js') }}"></script>
@endpush