@extends('layouts.app')

@section('title', 'Tambah Data Pihak')
@section('page-title', 'Tambah Data Pihak')

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
        <div class="detail-header-title">Tambah Data Pihak</div>
        <div class="detail-header-periode">Master Data Pihak</div>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">
            <i class="fas fa-user-plus"></i> Form Tambah Pihak
        </div>
    </div>

    <form method="POST" action="{{ route('pihak.store') }}" id="createForm">
        @csrf

        <div class="form-body">

            {{-- Data Utama --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-fingerprint"></i> Identitas
                </div>
                <div class="form-group">
                    <label class="form-label">ID Pihak <span class="required">*</span></label>
                    <input type="text" name="id" class="form-control @error('id') is-error @enderror"
                           value="{{ old('id') }}" maxlength="50" required>
                    <span class="form-help">ID unik untuk tabel pihak</span>
                    @error('id') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-user"></i> Data Utama
                </div>

                <div class="form-group">
                    <label class="form-label">Nama <span class="required">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-error @enderror"
                           value="{{ old('nama') }}" maxlength="500" required>
                    @error('nama') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat <span class="required">*</span></label>
                    <textarea name="alamat" class="form-control form-textarea @error('alamat') is-error @enderror"
                              maxlength="500" required>{{ old('alamat') }}</textarea>
                    @error('alamat') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Pekerjaan <span class="required">*</span></label>
                    <input type="text" name="pekerjaan" class="form-control @error('pekerjaan') is-error @enderror"
                           value="{{ old('pekerjaan') }}" maxlength="255" required>
                    @error('pekerjaan') <span class="form-error">{{ $message }}</span> @enderror
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
                               value="{{ old('tempat_lahir') }}" maxlength="50">
                        <span class="form-help">Kosongkan jika tidak diketahui</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control"
                               value="{{ old('tanggal_lahir') }}">
                    </div>
                </div>
            </div>

            {{-- Keterangan --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-sticky-note"></i> Keterangan
                    <span class="section-optional">(Opsional)</span>
                </div>
                <div class="form-group">
                    <textarea name="keterangan" class="form-control form-textarea"
                              maxlength="255">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            {{-- Opsi tabel terkait --}}
            <div class="form-section" style="border-bottom:none">
                <div class="form-section-title">
                    <i class="fas fa-database"></i> Opsi Tabel Terkait
                </div>
                <div class="form-group">
                    <label class="form-label">Tambahkan juga ke tabel:</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="insert_perkara_pihak1"
                                   {{ old('insert_perkara_pihak1') ? 'checked' : '' }}>
                            <span>perkara_pihak1 <span class="form-help" style="display:inline">(menggunakan pihak_id referensi)</span></span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="insert_perkara_pihak2"
                                   {{ old('insert_perkara_pihak2') ? 'checked' : '' }}>
                            <span>perkara_pihak2 <span class="form-help" style="display:inline">(menggunakan pihak_id referensi)</span></span>
                        </label>
                    </div>
                    <span class="form-help">Data akan diinsert dengan pihak_id = ID yang dimasukkan di atas</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('pihak.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Data
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/pihak.js') }}"></script>
@endpush