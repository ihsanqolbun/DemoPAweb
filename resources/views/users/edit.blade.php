@extends('layouts.app')

@section('title', 'Edit User')
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
            <i class="fas fa-user-edit"></i>
        </div>
        <div class="flex-grow">
            <h2 class="form-card-title">Edit User</h2>
            <p class="form-card-subtitle">Perbarui data akun <strong>{{ $user->username }}</strong></p>
        </div>
        {{-- Tombol Reset Password --}}
        <button type="button" class="btn btn-warning" onclick="openResetModal()">
            <i class="fas fa-key"></i> Reset Password
        </button>
    </div>

    {{-- Info tambahan --}}
    <div class="user-meta-bar">
        <div class="user-meta-item">
            <i class="fas fa-id-badge"></i>
            <span>ID: <strong>{{ $user->id }}</strong></span>
        </div>
        <div class="user-meta-item">
            <i class="fas fa-calendar-plus"></i>
            <span>Dibuat: <strong>{{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}</strong></span>
        </div>
        <div class="user-meta-item">
            <span class="role-badge role-{{ $user->role }}">
                {{ match($user->role) {
                    'super_admin' => 'Super Admin',
                    'admin'       => 'Admin',
                    'pimpinan'    => 'Pimpinan',
                    'hakim'       => 'Hakim',
                    default       => ucfirst($user->role),
                } }}
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('users.update', $user->id) }}" id="editForm">
        @csrf
        @method('PUT')

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
                       value="{{ old('name', $user->name) }}"
                       placeholder="Nama lengkap user"
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
                       value="{{ old('username', $user->username) }}"
                       placeholder="Username login"
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
                        class="form-control form-select {{ $errors->has('role') ? 'is-invalid' : '' }}"
                        {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                    @foreach($validRoles as $role)
                    <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>
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
                @if(auth()->id() === $user->id)
                    {{-- Hidden input karena select disabled tidak terkirim --}}
                    <input type="hidden" name="role" value="{{ $user->role }}">
                    <div class="form-hint"><i class="fas fa-info-circle"></i> Role akun sendiri tidak dapat diubah</div>
                @endif
                @error('role')
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

{{-- Modal Reset Password --}}
<div class="modal-overlay" id="resetModal">
    <div class="modal-box">
        <div class="modal-icon modal-icon-warning">
            <i class="fas fa-key"></i>
        </div>
        <h3 class="modal-title">Reset Password</h3>
        <p class="modal-desc">
            Reset password <strong>{{ $user->username }}</strong> ke password default?
        </p>
        <div class="reset-info-box">
            <i class="fas fa-info-circle"></i>
            Password baru: <code>password123</code><br>
            <small>Informasikan password ini ke user setelah reset.</small>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeResetModal()">Batal</button>
            <form method="POST" action="{{ route('users.reset-password', $user->id) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-key"></i> Ya, Reset Password
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/users.js') }}"></script>
@endpush