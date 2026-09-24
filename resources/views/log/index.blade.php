@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/log.css') }}">
@endpush

@section('content')

<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">
            <i class="fas fa-list-alt"></i> Log Aktivitas
            <span class="total-badge">{{ number_format($rows->total()) }}</span>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('log.index') }}" class="table-search-bar" style="flex-wrap:wrap;gap:10px">
        <input type="hidden" name="sort_by"  value="{{ $sortBy }}">
        <input type="hidden" name="sort_dir" value="{{ $sortDir }}">

        {{-- Search --}}
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search"
                   placeholder="Cari username, keterangan, IP..."
                   value="{{ $search }}">
        </div>

        {{-- Filter aksi --}}
        <select name="aksi" class="per-page-select" style="min-width:160px">
            <option value="">Semua Aksi</option>
            @foreach($daftarAksi as $a)
            <option value="{{ $a }}" {{ $aksiFilter === $a ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>

        {{-- Filter role --}}
        <select name="role" class="per-page-select">
            <option value="">Semua Role</option>
            @foreach(['super_admin','admin','pimpinan','hakim'] as $r)
            <option value="{{ $r }}" {{ $roleFilter === $r ? 'selected' : '' }}>
                {{ match($r) {
                    'super_admin' => 'Super Admin',
                    'admin'       => 'Admin',
                    'pimpinan'    => 'Pimpinan',
                    'hakim'       => 'Hakim',
                } }}
            </option>
            @endforeach
        </select>

        {{-- Per page --}}
        <select name="per_page" class="per-page-select" onchange="this.form.submit()">
            @foreach([10, 25, 50] as $pp)
            <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }} / hal</option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-filter"></i> Filter
        </button>

        @if($search || $aksiFilter || $roleFilter)
        <a href="{{ route('log.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-times"></i> Reset
        </a>
        @endif
    </form>

    @if($rows->count() > 0)
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th width="44">No</th>
                    @php
                        function logSortLink($col, $label, $sortBy, $sortDir, $search, $aksiFilter, $roleFilter, $perPage) {
                            $newDir = ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc';
                            $url = request()->url() . '?' . http_build_query([
                                'sort_by'  => $col,
                                'sort_dir' => $newDir,
                                'search'   => $search,
                                'aksi'     => $aksiFilter,
                                'role'     => $roleFilter,
                                'per_page' => $perPage,
                            ]);
                            $icon = $sortBy === $col
                                ? ($sortDir === 'asc' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>')
                                : ' <i class="fas fa-sort sort-inactive"></i>';
                            return '<a href="' . $url . '" class="sort-link">' . $label . $icon . '</a>';
                        }
                    @endphp
                    <th>{!! logSortLink('created_at', 'Waktu',    $sortBy, $sortDir, $search, $aksiFilter, $roleFilter, $perPage) !!}</th>
                    <th>{!! logSortLink('username',   'Username', $sortBy, $sortDir, $search, $aksiFilter, $roleFilter, $perPage) !!}</th>
                    <th>{!! logSortLink('role',       'Role',     $sortBy, $sortDir, $search, $aksiFilter, $roleFilter, $perPage) !!}</th>
                    <th>{!! logSortLink('aksi',       'Aksi',     $sortBy, $sortDir, $search, $aksiFilter, $roleFilter, $perPage) !!}</th>
                    <th>Keterangan</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                <tr>
                    <td class="text-center text-muted">{{ $rows->firstItem() + $i }}</td>
                    <td class="nowrap text-muted" style="font-size:12px">
                        {{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="fw-500">
                        <div class="log-user-cell">
                            <div class="log-avatar">{{ strtoupper(substr($row->username ?? 'S', 0, 1)) }}</div>
                            {{ $row->username ?? '-' }}
                        </div>
                    </td>
                    <td>
                        @if($row->role)
                        <span class="role-badge role-{{ $row->role }}">
                            {{ match($row->role) {
                                'super_admin' => 'Super Admin',
                                'admin'       => 'Admin',
                                'pimpinan'    => 'Pimpinan',
                                'hakim'       => 'Hakim',
                                default       => ucfirst($row->role),
                            } }}
                        </span>
                        @else
                        <span class="null-val">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="aksi-badge aksi-{{ \Str::slug($row->aksi ?? '') }}">
                            {{ $row->aksi ?? '-' }}
                        </span>
                    </td>
                    <td class="log-keterangan">{{ $row->keterangan ?? '-' }}</td>
                    <td class="nowrap" style="font-size:12px;color:#888;font-family:monospace">
                        {{ $row->ip_address ?? '-' }}
                    </td>
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
            @if($search || $aksiFilter || $roleFilter)
                <span class="filtered-info">(hasil filter)</span>
            @endif
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
        <i class="fas fa-clipboard-list"></i>
        <p>{{ ($search || $aksiFilter || $roleFilter) ? 'Tidak ditemukan log yang sesuai filter.' : 'Belum ada log aktivitas.' }}</p>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/log.js') }}"></script>
@endpush