@extends('layouts.app')

@section('title', 'Detail — ' . $judulKategori)
@section('page-title', 'Detail Perkara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rekap-perkara.css') }}">
@endpush

@section('content')

@php
    $baseUrl = route('rekap-perkara.detail') . '?' . http_build_query(request()->except(['sort_by','sort_dir','page']));
@endphp

{{-- Header --}}
<div class="detail-header-bar">
    <a href="{{ route('rekap-perkara.index', ['mode'=>'range','tanggal_mulai'=>$tanggalMulai,'tanggal_akhir'=>$tanggalAkhir]) }}"
       class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <div class="detail-header-info">
        <div class="detail-header-title">{{ $judulKategori }}</div>
        <div class="detail-header-periode">
            <i class="fas fa-calendar-alt"></i>
            @if($tanggalMulai == $tanggalAkhir)
                {{ \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMMM Y') }}
            @else
                {{ \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMMM Y') }}
                — {{ \Carbon\Carbon::parse($tanggalAkhir)->locale('id')->isoFormat('D MMMM Y') }}
                ({{ $jumlahHari }} hari)
            @endif
        </div>
    </div>
    <span class="total-badge">{{ number_format($totalData) }} perkara</span>
</div>

{{-- Toolbar --}}
<div class="detail-toolbar">
    <form method="GET" action="{{ route('rekap-perkara.detail') }}" class="detail-search-form">
        <input type="hidden" name="kategori"      value="{{ $kategori }}">
        <input type="hidden" name="tanggal_mulai" value="{{ $tanggalMulai }}">
        <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
        <input type="hidden" name="sort_by"       value="{{ $sortBy }}">
        <input type="hidden" name="sort_dir"      value="{{ $sortDir }}">
        <div class="search-box" style="flex:1">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Cari nomor perkara, nama pihak, e-filing ID..."
                   value="{{ $search }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
        @if($search)
        <a href="{{ route('rekap-perkara.detail', ['kategori'=>$kategori,'tanggal_mulai'=>$tanggalMulai,'tanggal_akhir'=>$tanggalAkhir]) }}"
           class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
        @endif
        <select name="per_page" class="per-page-select" onchange="this.form.submit()">
            @foreach([10,25,50] as $pp)
            <option value="{{ $pp }}" {{ $perPage==$pp?'selected':'' }}>{{ $pp }} / hal</option>
            @endforeach
        </select>
    </form>
    <div style="display:flex;gap:8px">
        @php $ep = http_build_query(['tanggal_mulai'=>$tanggalMulai,'tanggal_akhir'=>$tanggalAkhir,'mode'=>'range']); @endphp
        <a href="{{ route('rekap-perkara.export.pdf').'?'.$ep }}" class="btn btn-pdf btn-sm" target="_blank">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('rekap-perkara.export.excel').'?'.$ep }}" class="btn btn-excel btn-sm">
            <i class="fas fa-file-excel"></i> Excel
        </a>
    </div>
</div>

{{-- Tabel --}}
<div class="data-card">
    @if($rows->count() > 0)
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th width="44">No</th>
                    <th><a href="{{ $baseUrl }}&sort_by=tanggal_pendaftaran&sort_dir={{ $sortBy=='tanggal_pendaftaran'&&$sortDir=='asc'?'desc':'asc' }}" class="sort-link">
                        Tanggal <i class="fas fa-{{ $sortBy=='tanggal_pendaftaran'?($sortDir=='asc'?'sort-up':'sort-down'):'sort' }} sort-icon {{ $sortBy=='tanggal_pendaftaran'?'active':'' }}"></i>
                    </a></th>
                    <th><a href="{{ $baseUrl }}&sort_by=nomor_perkara&sort_dir={{ $sortBy=='nomor_perkara'&&$sortDir=='asc'?'desc':'asc' }}" class="sort-link">
                        No. Perkara <i class="fas fa-{{ $sortBy=='nomor_perkara'?($sortDir=='asc'?'sort-up':'sort-down'):'sort' }} sort-icon {{ $sortBy=='nomor_perkara'?'active':'' }}"></i>
                    </a></th>
                    <th><a href="{{ $baseUrl }}&sort_by=jenis_perkara_nama&sort_dir={{ $sortBy=='jenis_perkara_nama'&&$sortDir=='asc'?'desc':'asc' }}" class="sort-link">
                        Jenis Perkara <i class="fas fa-{{ $sortBy=='jenis_perkara_nama'?($sortDir=='asc'?'sort-up':'sort-down'):'sort' }} sort-icon {{ $sortBy=='jenis_perkara_nama'?'active':'' }}"></i>
                    </a></th>
                    <th><a href="{{ $baseUrl }}&sort_by=cara_daftar&sort_dir={{ $sortBy=='cara_daftar'&&$sortDir=='asc'?'desc':'asc' }}" class="sort-link">
                        Cara Daftar <i class="fas fa-{{ $sortBy=='cara_daftar'?($sortDir=='asc'?'sort-up':'sort-down'):'sort' }} sort-icon {{ $sortBy=='cara_daftar'?'active':'' }}"></i>
                    </a></th>
                    <th>Pihak 1</th>
                    <th>Pihak 2</th>
                    <th>Hakim PN</th>
                    <th>E-filing ID</th>
                    <th><a href="{{ $baseUrl }}&sort_by=status_perkara&sort_dir={{ $sortBy=='status_perkara'&&$sortDir=='asc'?'desc':'asc' }}" class="sort-link">
                        Status <i class="fas fa-{{ $sortBy=='status_perkara'?($sortDir=='asc'?'sort-up':'sort-down'):'sort' }} sort-icon {{ $sortBy=='status_perkara'?'active':'' }}"></i>
                    </a></th>
                    <th>Tgl Putusan</th>
                    <th>Tgl Minutasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                @php
                    $row = (array)$row;
                    $statusMap = [
                        'dalam_proses'     => ['label'=>'Dalam Proses','class'=>'badge-proses'],
                        'putus'            => ['label'=>'Putus',        'class'=>'badge-putus'],
                        'minutasi_selesai' => ['label'=>'Minutasi',     'class'=>'badge-minutasi'],
                    ];
                    $s = $statusMap[$row['status_perkara']??'dalam_proses'] ?? ['label'=>'-','class'=>''];
                @endphp
                <tr>
                    <td class="text-center text-muted">{{ $rows->firstItem() + $i }}</td>
                    <td class="nowrap">{{ $row['tanggal_pendaftaran'] ? \Carbon\Carbon::parse($row['tanggal_pendaftaran'])->format('d/m/Y') : '-' }}</td>
                    <td class="nowrap fw-500">{{ $row['nomor_perkara'] ?? '-' }}</td>
                    <td><span class="badge badge-jenis">{{ $row['jenis_perkara_nama'] ?? '-' }}</span></td>
                    <td class="text-center">
                        <span class="badge {{ ($row['cara_daftar']??'')==='e-court'?'badge-ecourt':'badge-manual' }}">
                            {{ ($row['cara_daftar']??'')==='e-court'?'E-Court':'Manual' }}
                        </span>
                    </td>
                    <td class="td-truncate" title="{{ $row['pihak1_text']??'' }}">{{ $row['pihak1_text']??'-' }}</td>
                    <td class="td-truncate" title="{{ $row['pihak2_text']??'' }}">{{ $row['pihak2_text']??'-' }}</td>
                    <td>{{ $row['hakim_nama']??'-' }}</td>
                    <td style="font-size:11px;color:#999">{{ $row['efiling_id']??'-' }}</td>
                    <td class="text-center"><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></td>
                    <td class="nowrap">{{ ($row['tanggal_putusan']??'') ? \Carbon\Carbon::parse($row['tanggal_putusan'])->format('d/m/Y') : '-' }}</td>
                    <td class="nowrap">{{ ($row['tanggal_minutasi']??'') ? \Carbon\Carbon::parse($row['tanggal_minutasi'])->format('d/m/Y') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($rows->hasPages())
    <div class="pagination-simple">
        <div class="pagination-info">
            {{ number_format($rows->firstItem()) }}–{{ number_format($rows->lastItem()) }}
            dari {{ number_format($rows->total()) }}
        </div>
        <div class="pagination-nav">
            {{-- Prev --}}
            @if($rows->onFirstPage())
                <span class="pg-btn pg-disabled"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $rows->previousPageUrl() }}" class="pg-btn"><i class="fas fa-chevron-left"></i></a>
            @endif

            {{-- Halaman 1 --}}
            @if($rows->currentPage() == 1)
                <span class="pg-btn pg-active">1</span>
            @else
                <a href="{{ $rows->url(1) }}" class="pg-btn">1</a>
            @endif

            {{-- Ellipsis kiri --}}
            @if($rows->currentPage() > 3)
                <span class="pg-btn pg-disabled">...</span>
            @endif

            {{-- Halaman sekitar current (bukan 1 dan last) --}}
            @foreach(range(max(2, $rows->currentPage()-1), min($rows->lastPage()-1, $rows->currentPage()+1)) as $page)
                @if($page == $rows->currentPage())
                    <span class="pg-btn pg-active">{{ $page }}</span>
                @else
                    <a href="{{ $rows->url($page) }}" class="pg-btn">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Ellipsis kanan --}}
            @if($rows->currentPage() < $rows->lastPage() - 2)
                <span class="pg-btn pg-disabled">...</span>
            @endif

            {{-- Halaman terakhir --}}
            @if($rows->lastPage() > 1)
                @if($rows->currentPage() == $rows->lastPage())
                    <span class="pg-btn pg-active">{{ $rows->lastPage() }}</span>
                @else
                    <a href="{{ $rows->url($rows->lastPage()) }}" class="pg-btn">{{ $rows->lastPage() }}</a>
                @endif
            @endif

            {{-- Next --}}
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
        <p>{{ $search ? 'Tidak ditemukan: "'.$search.'"' : 'Tidak ada perkara '.$judulKategori.' pada periode ini.' }}</p>
    </div>
    @endif
</div>

@endsection