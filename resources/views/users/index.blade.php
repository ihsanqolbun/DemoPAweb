@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('content')

@php $isSuperAdmin = auth()->user()?->role === 'super_admin'; @endphp

<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">
            <i class="fas fa-users-cog"></i> Daftar User
            <span class="total-badge">{{ number_format($rows->total()) }}</span>
        </div>
        {{-- Tombol tambah: hanya super_admin --}}
        @if($isSuperAdmin)
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah User
        </a>
        @endif
    </div>

    {{-- Search + per-page --}}
    <form method="GET" action="{{ route('users.index') }}" class="table-search-bar">
        <input type="hidden" name="sort_by"  value="{{ $sortBy }}">
        <input type="hidden" name="sort_dir" value="{{ $sortDir }}">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search"
                   placeholder="Cari nama, username, role..."
                   value="{{ $search }}">
        </div>
        @if($search)
        <a href="{{ route('users.index', ['per_page' => $perPage]) }}"
           class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
        @endif
        <select name="per_page" class="per-page-select" onchange="this.form.submit()">
            @foreach([10, 25, 50] as $pp)
            <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }} / hal</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-search"></i> Cari
        </button>
    </form>

    @if($rows->count() > 0)
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th width="44">No</th>
                    @php
                        function sortLink($col, $label, $sortBy, $sortDir, $search, $perPage) {
                            $newDir = ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc';
                            $url = request()->url() . '?' . http_build_query([
                                'sort_by'  => $col,
                                'sort_dir' => $newDir,
                                'search'   => $search,
                                'per_page' => $perPage,
                            ]);
                            $icon = $sortBy === $col
                                ? ($sortDir === 'asc' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>')
                                : ' <i class="fas fa-sort sort-inactive"></i>';
                            return '<a href="' . $url . '" class="sort-link">' . $label . $icon . '</a>';
                        }
                    @endphp
                    <th>{!! sortLink('name',       'Nama Lengkap', $sortBy, $sortDir, $search, $perPage) !!}</th>
                    <th>{!! sortLink('username',   'Username',     $sortBy, $sortDir, $search, $perPage) !!}</th>
                    <th>{!! sortLink('role',       'Role',         $sortBy, $sortDir, $search, $perPage) !!}</th>
                    <th>{!! sortLink('created_at', 'Dibuat',       $sortBy, $sortDir, $search, $perPage) !!}</th>
                    {{-- Kolom aksi hanya untuk super_admin --}}
                    @if($isSuperAdmin)
                    <th width="100" class="text-center">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                <tr>
                    <td class="text-center text-muted">{{ $rows->firstItem() + $i }}</td>
                    <td class="fw-500">
                        <div class="user-name-cell">
                            <div class="user-avatar-sm">{{ strtoupper(substr($row->name, 0, 1)) }}</div>
                            {{ $row->name }}
                        </div>
                    </td>
                    <td class="username-cell">{{ $row->username }}</td>
                    <td>
                        <span class="role-badge role-{{ $row->role }}">
                            {{ match($row->role) {
                                'super_admin' => 'Super Admin',
                                'admin'       => 'Admin',
                                'pimpinan'    => 'Pimpinan',
                                'hakim'       => 'Hakim',
                                default       => ucfirst($row->role),
                            } }}
                        </span>
                    </td>
                    <td class="nowrap text-muted" style="font-size:12px">
                        {{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') }}
                    </td>
                    @if($isSuperAdmin)
                    <td class="text-center">
                        <div class="action-group">
                            <a href="{{ route('users.edit', $row->id) }}"
                               class="btn-action btn-action-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if(auth()->id() !== $row->id)
                            <button type="button"
                                    class="btn-action btn-action-delete"
                                    title="Hapus"
                                    onclick="confirmDelete({{ $row->id }}, '{{ addslashes($row->username) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                            @else
                            <span class="btn-action btn-action-self" title="Akun aktif">
                                <i class="fas fa-user-check"></i>
                            </span>
                            @endif
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($rows->hasPages())
    <div class="pagination-simple">
        <div class="pagination-info">
            {{ number_format($rows->firstItem()) }}–{{ number_format($rows->lastItem()) }}
            dari {{ number_format($rows->total()) }}
            @if($search) <span class="filtered-info">(hasil filter)</span> @endif
        </div>
        <div class="pagination-nav">
            @if($rows->onFirstPage())
                <span class="pg-btn pg-disabled"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $rows->previousPageUrl() }}" class="pg-btn"><i class="fas fa-chevron-left"></i></a>
            @endif

            @if($rows->currentPage() == 1)
                <span class="pg-btn pg-active">1</span>
            @else
                <a href="{{ $rows->url(1) }}" class="pg-btn">1</a>
            @endif

            @if($rows->currentPage() > 3)
                <span class="pg-btn pg-disabled">...</span>
            @endif

            @foreach(range(max(2, $rows->currentPage()-1), min($rows->lastPage()-1, $rows->currentPage()+1)) as $page)
                @if($page == $rows->currentPage())
                    <span class="pg-btn pg-active">{{ $page }}</span>
                @else
                    <a href="{{ $rows->url($page) }}" class="pg-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if($rows->currentPage() < $rows->lastPage() - 2)
                <span class="pg-btn pg-disabled">...</span>
            @endif

            @if($rows->lastPage() > 1)
                @if($rows->currentPage() == $rows->lastPage())
                    <span class="pg-btn pg-active">{{ $rows->lastPage() }}</span>
                @else
                    <a href="{{ $rows->url($rows->lastPage()) }}" class="pg-btn">{{ $rows->lastPage() }}</a>
                @endif
            @endif

            @if($rows->currentPage() == $rows->lastPage())
                <span class="pg-btn pg-disabled"><i class="fas fa-chevron-right"></i></span>
            @else
                <a href="{{ $rows->nextPageUrl() }}" class="pg-btn"><i class="fas fa-chevron-right"></i></a>
            @endif
        </div>
    </div>
    @endif

    @else
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <p>{{ $search ? 'Tidak ditemukan: "' . $search . '"' : 'Belum ada data user.' }}</p>
    </div>
    @endif
</div>

{{-- Modal Konfirmasi Hapus — hanya render untuk super_admin --}}
@if($isSuperAdmin)
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <i class="fas fa-trash"></i>
        </div>
        <h3 class="modal-title">Hapus User</h3>
        <p class="modal-desc">Anda yakin ingin menghapus user <strong id="deleteUsername"></strong>?<br>Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="{{ asset('js/users.js') }}"></script>
@endpush