@extends('layouts.app')

@section('title', 'Data Pihak')
@section('page-title', 'Data Pihak')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pihak.css') }}">
@endpush

@section('content')

<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">
            <i class="fas fa-users"></i> Data Pihak
            <span class="total-badge">{{ number_format($rows->total()) }}</span>
        </div>
        {{-- Tombol Tambah — uncomment kalau mau aktifkan
        <a href="{{ route('pihak.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Data
        </a>
        --}}
    </div>

    {{-- Search + per-page --}}
    <form method="GET" action="{{ route('pihak.index') }}" class="table-search-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search"
                   placeholder="Cari nama, alamat, pekerjaan, ID..."
                   value="{{ $search }}">
        </div>
        @if($search)
        <a href="{{ route('pihak.index', ['per_page' => $perPage]) }}"
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
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Tempat Lahir</th>
                    <th>Tanggal Lahir</th>
                    <th>Pekerjaan</th>
                    <th>Keterangan</th>
                    <th width="90">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                <tr>
                    <td class="text-center text-muted">{{ $rows->firstItem() + $i }}</td>
                    <td class="id-cell">{{ $row->id }}</td>
                    <td class="fw-500">{{ $row->nama ?? '-' }}</td>
                    <td class="td-truncate" title="{{ $row->alamat }}">{{ $row->alamat ?? '-' }}</td>
                    <td>
                        @if($row->tempat_lahir)
                            {{ $row->tempat_lahir }}
                        @else
                            <span class="null-val">—</span>
                        @endif
                    </td>
                    <td class="nowrap">
                        @if($row->tanggal_lahir)
                            {{ \Carbon\Carbon::parse($row->tanggal_lahir)->format('d/m/Y') }}
                        @else
                            <span class="null-val">—</span>
                        @endif
                    </td>
                    <td>{{ $row->pekerjaan ?? '-' }}</td>
                    <td class="td-truncate" title="{{ $row->keterangan ?? '' }}">
                        @if($row->keterangan)
                            {{ $row->keterangan }}
                        @else
                            <span class="null-val">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div style="display:flex;gap:6px;justify-content:center">
                            <a href="{{ route('pihak.edit', $row->id) }}" class="btn-edit-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            {{-- Tombol Hapus — uncomment kalau mau aktifkan
                            <form method="POST" action="{{ route('pihak.destroy', $row->id) }}"
                                  onsubmit="return confirm('Hapus data ini dari dummy_aps.eac_validasi?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            --}}
                        </div>
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
            @if($search) <span class="filtered-info">(hasil filter)</span> @endif
        </div>
        <div class="pagination-nav">
            @if($rows->onFirstPage())
                <span class="pg-btn pg-disabled"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $rows->previousPageUrl() }}" class="pg-btn"><i class="fas fa-chevron-left"></i></a>
            @endif

            @foreach($rows->getUrlRange(max(1, $rows->currentPage()-2), min($rows->lastPage(), $rows->currentPage()+2)) as $page => $url)
                @if($page == $rows->currentPage())
                    <span class="pg-btn pg-active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="pg-btn">{{ $page }}</a>
                @endif
            @endforeach

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
        <i class="fas fa-inbox"></i>
        <p>{{ $search ? 'Tidak ditemukan: "' . $search . '"' : 'Tidak ada data pihak.' }}</p>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/pihak.js') }}"></script>
@endpush