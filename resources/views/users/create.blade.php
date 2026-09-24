@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Manajemen User')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('content')

<div class="form-page-header">
    <a href="{{ route('users.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-icon">
            <i class="fas fa-user-plus"></i>
        </div>
        <div>
            <h2 class="form-card-title">Tambah User Baru</h2>
            <p class="form-card-subtitle">Lengkapi data berikut untuk membuat akun baru</p>
        </div>
    </div>

    <form method="POST" action="{{ route('users.store') }}" id="createForm">
        @csrf

        <div class="form-grid">
            {{-- Nama Lengkap --}}
            <div class="form-group">
                <label class="form-label" for="name">
                    Nama Lengkap <span class="required-star">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name') }}"
                       placeholder="Contoh: Ahmad Fauzi, S.H."
                       autocomplete="off">
                @error('name')
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Username --}}
            <div class="form-group">
                <label class="form-label" for="username">
                    Username <span class="required-star">*</span>
                </label>
                <input type="text"
                       id="username"
                       name="username"
                       class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                       value="{{ old('username') }}"
                       placeholder="Contoh: ahmad.fauzi"
                       autocomplete="off">
                @error('username')
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Role --}}
            <div class="form-group">
                <label class="form-label" for="role">
                    Role <span class="required-star">*</span>
                </label>
                <select id="role" name="role"
                        class="form-control form-select {{ $errors->has('role') ? 'is-invalid' : '' }}">
                    <option value="">— Pilih Role —</option>
                    @foreach($validRoles as $role)
                    <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                        {{ match($role) {
                            'super_admin' => 'Super Admin',
                            'admin'       => 'Admin',
                            'pimpinan'    => 'Pimpinan',
                            'hakim'       => 'Hakim',
                            default       => ucfirst($role),
                        } }}
                    </option>
                    @endforeach
                </select>
                @error('role')
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Spacer --}}
            <div></div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">
                    Password <span class="required-star">*</span>
                </label>
                <div class="input-password-wrap">
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Minimal 6 karakter"
                           autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div class="form-group">
                <label class="form-label" for="password_confirmation">
                    Konfirmasi Password <span class="required-star">*</span>
                </label>
                <div class="input-password-wrap">
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="form-control"
                           placeholder="Ulangi password"
                           autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan User
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/users.js') }}"></script>
@endpush