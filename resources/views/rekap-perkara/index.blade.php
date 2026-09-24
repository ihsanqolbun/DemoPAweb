@extends('layouts.app')

@section('title', 'Rekap Perkara')
@section('page-title', 'Rekap Perkara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rekap-perkara.css') }}">
@endpush

@section('content')

@php
    $detailBase = route('rekap-perkara.detail') . '?tanggal_mulai=' . $tanggalMulai . '&tanggal_akhir=' . $tanggalAkhir;
    function sortUrl($col, $currentSortBy, $currentSortDir, $base) {
        $dir = ($currentSortBy === $col && $currentSortDir === 'asc') ? 'desc' : 'asc';
        return $base . '&sort_by=' . $col . '&sort_dir=' . $dir;
    }
    $baseUrl  = route('rekap-perkara.index') . '?' . http_build_query(request()->except(['sort_by','sort_dir','page']));
    $bisaExport = !in_array(auth()->user()?->role, ['hakim']);
@endphp

{{-- FILTER --}}
<div class="filter-card">
    <div class="filter-card-title"><i class="fas fa-filter"></i> Filter Periode</div>
    <form method="GET" action="{{ route('rekap-perkara.index') }}" id="filterForm">
        <input type="hidden" name="mode" id="modeInput" value="{{ $mode }}">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <div class="mode-tabs">
            <button type="button" class="mode-tab {{ $mode=='hari' ? 'active':'' }}" data-mode="hari">
                <i class="fas fa-calendar-day"></i> Per Hari
            </button>
            <button type="button" class="mode-tab {{ $mode=='bulan' ? 'active':'' }}" data-mode="bulan">
                <i class="fas fa-calendar-alt"></i> Per Bulan
            </button>
            <button type="button" class="mode-tab {{ $mode=='range' ? 'active':'' }}" data-mode="range">
                <i class="fas fa-calendar-week"></i> Range Bebas
            </button>
        </div>
        <div class="filter-inputs-row">
            <div id="panel-hari" class="filter-input-group" style="{{ $mode!='hari'?'display:none':'' }}">
                <label class="filter-label">Tanggal</label>
                <input type="date" name="tanggal" class="filter-input"
                       value="{{ $tanggal ?? today()->toDateString() }}" max="{{ today()->toDateString() }}">
            </div>
            <div id="panel-bulan" class="filter-input-group" style="{{ $mode!='bulan'?'display:none':'' }}">
                <label class="filter-label">Bulan</label>
                <input type="month" name="bulan" class="filter-input" value="{{ $bulan ?? now()->format('Y-m') }}">
            </div>
            <div id="panel-range" class="filter-input-group" style="{{ $mode!='range'?'display:none':'' }}">
                <div class="filter-range-inner">
                    <div>
                        <label class="filter-label">Mulai</label>
                        <input type="date" name="tanggal_mulai" class="filter-input"
                               value="{{ $tanggalMulai }}" max="{{ today()->toDateString() }}">
                    </div>
                    <span class="range-sep">—</span>
                    <div>
                        <label class="filter-label">Akhir</label>
                        <input type="date" name="tanggal_akhir" class="filter-input"
                               value="{{ $tanggalAkhir }}" max="{{ today()->toDateString() }}">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="align-self:flex-end">
                <i class="fas fa-search"></i> Tampilkan
            </button>
        </div>
    </form>
    <div class="filter-info-bar">
        <i class="fas fa-info-circle"></i>
        Menampilkan data:
        <strong>
            @if($tanggalMulai == $tanggalAkhir)
                {{ \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMMM Y') }}
            @else
                {{ \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMMM Y') }}
                — {{ \Carbon\Carbon::parse($tanggalAkhir)->locale('id')->isoFormat('D MMMM Y') }}
                ({{ $jumlahHari }} hari)
            @endif
        </strong>
    </div>
</div>

{{-- SUMMARY CARDS --}}
<div class="summary-cards">
    <a href="{{ $detailBase }}&kategori=semua" class="scard scard-green">
        <div class="scard-icon"><i class="fas fa-folder-open"></i></div>
        <div class="scard-body">
            <div class="scard-num">{{ number_format($totalMasuk) }}</div>
            <div class="scard-label">Total Masuk</div>
        </div>
    </a>
    <a href="{{ $detailBase }}&kategori=ecourt" class="scard scard-amber">
        <div class="scard-icon"><i class="fas fa-globe"></i></div>
        <div class="scard-body">
            <div class="scard-num">{{ number_format($eCourt) }}</div>
            <div class="scard-label">E-Court</div>
            <div class="scard-pct">{{ $totalMasuk > 0 ? round(($eCourt/$totalMasuk)*100) : 0 }}%</div>
        </div>
    </a>
    <a href="{{ $detailBase }}&kategori=manual" class="scard scard-blue">
        <div class="scard-icon"><i class="fas fa-pen"></i></div>
        <div class="scard-body">
            <div class="scard-num">{{ number_format($manual) }}</div>
            <div class="scard-label">Manual</div>
            <div class="scard-pct">{{ $totalMasuk > 0 ? round(($manual/$totalMasuk)*100) : 0 }}%</div>
        </div>
    </a>
    <a href="{{ $detailBase }}&kategori=putus" class="scard scard-teal">
        <div class="scard-icon"><i class="fas fa-gavel"></i></div>
        <div class="scard-body">
            <div class="scard-num">{{ number_format($putus) }}</div>
            <div class="scard-label">Putus</div>
        </div>
    </a>
    <a href="{{ $detailBase }}&kategori=minutasi" class="scard scard-indigo">
        <div class="scard-icon"><i class="fas fa-check-circle"></i></div>
        <div class="scard-body">
            <div class="scard-num">{{ number_format($minutasi) }}</div>
            <div class="scard-label">Minutasi</div>
        </div>
    </a>
    <a href="{{ $detailBase }}&kategori=dalam_proses" class="scard scard-red">
        <div class="scard-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="scard-body">
            <div class="scard-num">{{ number_format($dalamProses) }}</div>
            <div class="scard-label">Dalam Proses</div>
        </div>
    </a>
</div>

{{-- JENIS CARDS --}}
@if(count($jenisCards) > 0)
<div class="section-header">
    <span class="section-title"><i class="fas fa-layer-group"></i> Breakdown per Jenis Perkara</span>
    <span class="section-sub">{{ count($jenisCards) }} jenis</span>
</div>
<div class="jenis-cards-grid">
    @foreach($jenisCards as $jc)
    <a href="{{ $detailBase }}&kategori={{ $jc['slug'] }}" class="jcard">
        <div class="jcard-num">{{ number_format($jc['jumlah']) }}</div>
        <div class="jcard-label">{{ $jc['label'] }}</div>
        <div class="jcard-pct">{{ $totalMasuk > 0 ? round(($jc['jumlah']/$totalMasuk)*100,1) : 0 }}%</div>
        <div class="jcard-bar"><div class="jcard-bar-fill" style="width:{{ $totalMasuk > 0 ? min(100,round(($jc['jumlah']/$totalMasuk)*100)) : 0 }}%"></div></div>
    </a>
    @endforeach
</div>
@endif

{{-- 3 CHARTS COMPACT --}}
@if($totalMasuk > 0)
<div class="charts-row">
    <div class="chart-compact">
        <div class="chart-compact-title">Jenis Perkara</div>
        <div style="height:160px"><canvas id="chartJenis"></canvas></div>
    </div>
    <div class="chart-compact">
        <div class="chart-compact-title">Cara Daftar</div>
        <div style="height:160px"><canvas id="chartCara"></canvas></div>
    </div>
    <div class="chart-compact">
        <div class="chart-compact-title">Status Penyelesaian</div>
        <div style="height:160px"><canvas id="chartStatus"></canvas></div>
    </div>
</div>
@endif

{{-- TABEL --}}
<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">
            <i class="fas fa-table"></i> Data Perkara
            <span class="total-badge">{{ number_format($rows->total()) }}</span>
        </div>
        {{-- Tombol export: hanya untuk non-hakim --}}
        @if($bisaExport)
        <div style="display:flex;gap:8px;align-items:center">
            <a href="{{ route('rekap-perkara.export.pdf', request()->query()) }}" class="btn btn-pdf btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('rekap-perkara.export.excel', request()->query()) }}" class="btn btn-excel btn-sm">
                <i class="fas fa-file-excel"></i> Excel
            </a>
        </div>
        @endif
    </div>

    {{-- Search + per-page --}}
    <form method="GET" action="{{ route('rekap-perkara.index') }}" class="table-search-bar">
        <input type="hidden" name="mode" value="{{ $mode }}">
        @if($mode=='hari')
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
        @elseif($mode=='bulan')
            <input type="hidden" name="bulan" value="{{ $bulan }}">
        @else
            <input type="hidden" name="tanggal_mulai" value="{{ $tanggalMulai }}">
            <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
        @endif
        <input type="hidden" name="sort_by"  value="{{ $sortBy }}">
        <input type="hidden" name="sort_dir" value="{{ $sortDir }}">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Cari nomor perkara, nama pihak..." value="{{ $search }}">
        </div>
        @if($search)
        <a href="{{ route('rekap-perkara.index', array_merge(request()->except(['search','page']))) }}"
           class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
        @endif
        <select name="per_page" class="per-page-select" onchange="this.form.submit()">
            @foreach([10,25,50] as $pp)
            <option value="{{ $pp }}" {{ $perPage==$pp?'selected':'' }}>{{ $pp }} / hal</option>
            @endforeach
        </select>
    </form>

    @if($rows->count() > 0)
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th width="44">No</th>
                    <th><a href="{{ $baseUrl }}&sort_by=tanggal_pendaftaran&sort_dir={{ $sortBy=='tanggal_pendaftaran' && $sortDir=='asc' ? 'desc' : 'asc' }}" class="sort-link">
                        Tanggal <i class="fas fa-{{ $sortBy=='tanggal_pendaftaran' ? ($sortDir=='asc'?'sort-up':'sort-down') : 'sort' }} sort-icon {{ $sortBy=='tanggal_pendaftaran'?'active':'' }}"></i>
                    </a></th>
                    <th><a href="{{ $baseUrl }}&sort_by=nomor_perkara&sort_dir={{ $sortBy=='nomor_perkara' && $sortDir=='asc' ? 'desc' : 'asc' }}" class="sort-link">
                        No. Perkara <i class="fas fa-{{ $sortBy=='nomor_perkara' ? ($sortDir=='asc'?'sort-up':'sort-down') : 'sort' }} sort-icon {{ $sortBy=='nomor_perkara'?'active':'' }}"></i>
                    </a></th>
                    <th><a href="{{ $baseUrl }}&sort_by=jenis_perkara_nama&sort_dir={{ $sortBy=='jenis_perkara_nama' && $sortDir=='asc' ? 'desc' : 'asc' }}" class="sort-link">
                        Jenis Perkara <i class="fas fa-{{ $sortBy=='jenis_perkara_nama' ? ($sortDir=='asc'?'sort-up':'sort-down') : 'sort' }} sort-icon {{ $sortBy=='jenis_perkara_nama'?'active':'' }}"></i>
                    </a></th>
                    <th><a href="{{ $baseUrl }}&sort_by=cara_daftar&sort_dir={{ $sortBy=='cara_daftar' && $sortDir=='asc' ? 'desc' : 'asc' }}" class="sort-link">
                        Cara Daftar <i class="fas fa-{{ $sortBy=='cara_daftar' ? ($sortDir=='asc'?'sort-up':'sort-down') : 'sort' }} sort-icon {{ $sortBy=='cara_daftar'?'active':'' }}"></i>
                    </a></th>
                    <th>Hakim PN</th>
                    <th><a href="{{ $baseUrl }}&sort_by=status_perkara&sort_dir={{ $sortBy=='status_perkara' && $sortDir=='asc' ? 'desc' : 'asc' }}" class="sort-link">
                        Status <i class="fas fa-{{ $sortBy=='status_perkara' ? ($sortDir=='asc'?'sort-up':'sort-down') : 'sort' }} sort-icon {{ $sortBy=='status_perkara'?'active':'' }}"></i>
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
                        'dalam_proses'     => ['label'=>'Dalam Proses', 'class'=>'badge-proses'],
                        'putus'            => ['label'=>'Putus',         'class'=>'badge-putus'],
                        'minutasi_selesai' => ['label'=>'Minutasi',      'class'=>'badge-minutasi'],
                    ];
                    $s = $statusMap[$row['status_perkara'] ?? 'dalam_proses'] ?? ['label'=>'-','class'=>''];
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
                    <td>{{ $row['hakim_nama'] ?? '-' }}</td>
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
        <i class="fas fa-inbox"></i>
        <p>{{ $search ? 'Tidak ditemukan: "'.$search.'"' : 'Tidak ada perkara pada periode ini.' }}</p>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
const _rekap = {
    jenisData: @json($jenisChartData),
    eCourt: {{ $eCourt }},
    manual: {{ $manual }},
    putus: {{ $putus }},
    minutasi: {{ $minutasi }},
    dalamProses: {{ $dalamProses }},
    total: {{ $totalMasuk }},
    filterState: {
        mode: "{{ $mode }}",
        tanggal: "{{ $tanggal ?? '' }}",
        bulan: "{{ $bulan ?? '' }}",
        tanggal_mulai: "{{ $tanggalMulai }}",
        tanggal_akhir: "{{ $tanggalAkhir }}"
    }
};
</script>
<script src="{{ asset('js/rekap-perkara.js') }}"></script>
@endpush