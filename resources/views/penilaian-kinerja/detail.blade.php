@extends('layouts.app')

@section('title', $title)
@section('page-title', 'Penilaian Kinerja — Detail')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/penilaian-kinerja.css') }}">
@endpush

@section('content')

{{-- BREADCRUMB & BACK --}}
<div class="d-flex align-center justify-between mb-2" style="flex-wrap:wrap;gap:10px">
    <div class="d-flex align-center gap-1" style="font-size:13px;color:#7f8c8d">
        <a href="{{ route('penilaian-kinerja.index', ['tahun'=>$tahun,'triwulan'=>$triwulan]) }}"
           style="color:#1D9E75;text-decoration:none">Penilaian Kinerja</a>
        <i class="fas fa-chevron-right" style="font-size:10px"></i>
        <span style="color:#2c3e50;font-weight:500">{{ $title }}</span>
    </div>
    <a href="{{ route('penilaian-kinerja.index', ['tahun'=>$tahun,'triwulan'=>$triwulan]) }}"
       class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

{{-- INFO HEADER --}}
<div class="pk-detail-header">
    <div class="pk-detail-title-wrap">
        <div class="pk-detail-category">
            @php
                $catColor = match($category) {
                    'Kinerja'     => '#085041',
                    'Kepatuhan'   => '#1e40af',
                    'Kelengkapan' => '#3730a3',
                    'Kesesuaian'  => '#991b1b',
                    default       => '#555',
                };
                $catBg = match($category) {
                    'Kinerja'     => '#E1F5EE',
                    'Kepatuhan'   => '#EFF6FF',
                    'Kelengkapan' => '#F5F3FF',
                    'Kesesuaian'  => '#FEF2F2',
                    default       => '#f1f5f9',
                };
            @endphp
            <span style="background:{{ $catBg }};color:{{ $catColor }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">
                {{ $category }}
            </span>
        </div>
        <h2 class="pk-detail-title">{{ $title }}</h2>
        <div class="pk-detail-desc">{{ $desc }}</div>
    </div>
    <div class="pk-detail-meta">
        <div class="pk-info" style="margin-bottom:0">
            <div class="pk-info-item">
                <i class="fas fa-calendar-alt" style="color:#1D9E75"></i>
                <span>TW <strong>{{ $triwulan }}</strong> · {{ $tahun }} ({{ $labelTriwulan }})</span>
            </div>
            <div class="pk-info-dot"></div>
            <div class="pk-info-item">
                <i class="fas fa-list" style="color:#1D9E75"></i>
                <span>Total: <strong>{{ number_format($rows->total()) }}</strong> perkara</span>
            </div>
        </div>
    </div>
</div>

{{-- TABLE CARD --}}
<div class="card" style="margin-top:16px">
    {{-- Toolbar --}}
    <div class="d-flex align-center justify-between mb-2" style="flex-wrap:wrap;gap:10px">
        <div style="font-size:13px;color:#7f8c8d">
            Menampilkan {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }}
            dari {{ number_format($rows->total()) }} data
        </div>
        <div class="d-flex align-center gap-1">
            <label style="font-size:12px;color:#7f8c8d">Per halaman:</label>
            <select class="filter-input form-select" style="width:80px;padding:6px 10px"
                    onchange="changePerPage(this.value)">
                @foreach([10,25,50] as $pp)
                <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-wrapper" id="detailTableScroll">
        <table>
            <thead>
                <tr>
                    <th style="width:44px;text-align:center">#</th>
                    <th>No. Perkara</th>
                    <th>Tgl Daftar</th>
                    <th>Jenis Perkara</th>
                    <th>Pihak 1</th>
                    <th>Pihak 2</th>
                    @foreach($columns as $col)
                    <th>{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $row)
                <tr>
                    <td style="text-align:center;color:#aaa;font-size:12px">
                        {{ $rows->firstItem() + $i }}
                    </td>
                    <td style="font-weight:500;font-size:12px;white-space:nowrap">
                        {{ $row->nomor_perkara ?? '-' }}
                    </td>
                    <td style="white-space:nowrap;font-size:12px">
                        {{ $row->tanggal_pendaftaran ? \Carbon\Carbon::parse($row->tanggal_pendaftaran)->format('d/m/Y') : '-' }}
                    </td>
                    <td style="font-size:12px">{{ $row->jenis_perkara_nama ?? '-' }}</td>
                    <td style="font-size:12px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                        title="{{ $row->pihak1_text ?? '' }}">
                        {{ $row->pihak1_text ?? '-' }}
                    </td>
                    <td style="font-size:12px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                        title="{{ $row->pihak2_text ?? '' }}">
                        {{ $row->pihak2_text ?? '-' }}
                    </td>

                    {{-- Kolom dinamis per type --}}
                    @switch($type)
                        @case('putus')
                        @case('pen_pp')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_putusan) ? \Carbon\Carbon::parse($row->tanggal_putusan)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px">{{ $row->status_putusan_nama ?? '-' }}</td>
                            @if($type === 'putus')
                            <td style="font-size:12px;text-align:center">
                                @if(isset($row->durasi_hari))
                                    <span class="badge {{ $row->durasi_hari > 150 ? 'badge-danger' : ($row->durasi_hari > 90 ? 'badge-warning' : 'badge-success') }}">
                                        {{ $row->durasi_hari }} hr
                                    </span>
                                @else -
                                @endif
                            </td>
                            @endif
                            @break

                        @case('minutasi')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_putusan) ? \Carbon\Carbon::parse($row->tanggal_putusan)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_minutasi) ? \Carbon\Carbon::parse($row->tanggal_minutasi)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px;text-align:center">
                                @if(isset($row->selisih_hari))
                                    <span class="badge {{ $row->selisih_hari > 14 ? 'badge-danger' : 'badge-success' }}">
                                        {{ $row->selisih_hari }} hr
                                    </span>
                                @else - @endif
                            </td>
                            @break

                        @case('publikasi')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_putusan) ? \Carbon\Carbon::parse($row->tanggal_putusan)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_minutasi) ? \Carbon\Carbon::parse($row->tanggal_minutasi)->format('d/m/Y') : '-' }}</td>
                            <td><span class="badge badge-success" style="font-size:11px">Dipublikasi</span></td>
                            @break

                        @case('pendaftaran')
                            <td style="font-size:12px">{{ $row->tahapan_terakhir_text ?? '-' }}</td>
                            <td style="font-size:12px">{{ $row->proses_terakhir_text ?? '-' }}</td>
                            @break

                        @case('pen_pmh')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->penetapan_tanggal_mediasi) ? \Carbon\Carbon::parse($row->penetapan_tanggal_mediasi)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px">{{ $row->mediator_text ?? '-' }}</td>
                            @break

                        @case('pen_js')
                        @case('pen_js_2')
                        @case('pen_phs')
                        @case('input_phs')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->jadwal_pertama) ? \Carbon\Carbon::parse($row->jadwal_pertama)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px;text-align:center">{{ isset($row->jumlah_sidang) ? $row->jumlah_sidang . 'x' : '-' }}</td>
                            @break

                        @case('mediasi')
                            <td style="font-size:12px">{{ $row->mediator_text ?? '-' }}</td>
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->penetapan_tanggal_mediasi) ? \Carbon\Carbon::parse($row->penetapan_tanggal_mediasi)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->dimulai_mediasi) ? \Carbon\Carbon::parse($row->dimulai_mediasi)->format('d/m/Y') : '-' }}</td>
                            <td>
                                @php
                                    $hasilMediasi = $row->hasil_mediasi ?? '';
                                    $badgeHasil = $hasilMediasi === 'S' || $hasilMediasi === 'Y2' ? 'badge-success' : ($hasilMediasi === 'Y1' ? 'badge-warning' : 'badge-secondary');
                                @endphp
                                <span class="badge {{ $badgeHasil }}" style="font-size:11px">{{ $row->status_mediasi ?? '-' }}</span>
                            </td>
                            @break

                        @case('saksi')
                        @case('status_persidangan')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_sidang) ? \Carbon\Carbon::parse($row->tanggal_sidang)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px">{{ $row->agenda ?? '-' }}</td>
                            @break

                        @case('pbt')
                            <td style="font-size:12px;text-align:right">Rp {{ number_format($row->panjar_sipp ?? 0, 0, ',', '.') }}</td>
                            @break

                        @case('bht')
                            <td style="font-size:12px;text-align:right">Rp {{ number_format($row->bht_sipp ?? 0, 0, ',', '.') }}</td>
                            @break

                        @case('sisa_panjar')
                            <td style="font-size:12px;text-align:right">Rp {{ number_format($row->sisa_sipp ?? 0, 0, ',', '.') }}</td>
                            @break

                        @case('arsip')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_putusan) ? \Carbon\Carbon::parse($row->tanggal_putusan)->format('d/m/Y') : '-' }}</td>
                            <td><span class="badge badge-success" style="font-size:11px">Ada</span></td>
                            @break

                        @case('delegasi')
                        @case('permintaan_delegasi')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->permohonan_banding) ? \Carbon\Carbon::parse($row->permohonan_banding)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_kirim_salinan_putusan) ? \Carbon\Carbon::parse($row->tanggal_kirim_salinan_putusan)->format('d/m/Y') : '-' }}</td>
                            @break

                        @case('petitum')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_putusan) ? \Carbon\Carbon::parse($row->tanggal_putusan)->format('d/m/Y') : '-' }}</td>
                            <td><span class="badge badge-primary" style="font-size:11px">{{ $row->status_putusan_nama ?? '-' }}</span></td>
                            @break

                        @case('bas')
                            <td style="font-size:12px;text-align:center">{{ isset($row->jumlah_sidang) ? $row->jumlah_sidang . 'x' : '-' }}</td>
                            @break

                        @case('ac')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_putusan) ? \Carbon\Carbon::parse($row->tanggal_putusan)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px">{{ $row->status_putusan_nama ?? '-' }}</td>
                            @break

                        @case('sidang_akhir')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_sidang) ? \Carbon\Carbon::parse($row->tanggal_sidang)->format('d/m/Y') : '-' }}</td>
                            <td style="text-align:center">
                                @if(isset($row->selisih_hari))
                                    <span class="badge {{ $row->selisih_hari > 90 ? 'badge-danger' : 'badge-warning' }}" style="font-size:11px">
                                        {{ $row->selisih_hari }} hr
                                    </span>
                                @else - @endif
                            </td>
                            @break

                        @case('sinkron')
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_putusan) ? \Carbon\Carbon::parse($row->tanggal_putusan)->format('d/m/Y') : '-' }}</td>
                            <td style="font-size:12px;white-space:nowrap">{{ isset($row->tanggal_kirim_salinan_putusan) ? \Carbon\Carbon::parse($row->tanggal_kirim_salinan_putusan)->format('d/m/Y') : '-' }}</td>
                            <td style="text-align:center">
                                @if(isset($row->selisih_hari))
                                    <span class="badge {{ $row->selisih_hari > 100 ? 'badge-danger' : 'badge-warning' }}" style="font-size:11px">
                                        {{ $row->selisih_hari }} hr
                                    </span>
                                @else - @endif
                            </td>
                            @break
                    @endswitch
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 6 + count($columns) + 1 }}">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>Tidak ada data</h3>
                            <p>Tidak ada data untuk periode TW {{ $triwulan }} · {{ $tahun }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($rows->lastPage() > 1)
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Halaman {{ $rows->currentPage() }} dari {{ $rows->lastPage() }}
        </div>
        <ul class="pagination">
            {{-- Prev --}}
            <li class="page-item {{ $rows->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $rows->onFirstPage() ? '#' : $rows->previousPageUrl() }}">←</a>
            </li>

            @php
                $cur   = $rows->currentPage();
                $last  = $rows->lastPage();
                $pages = [];
                if ($cur > 2) $pages[] = 1;
                if ($cur > 3) $pages[] = '...';
                for ($i = max(1, $cur - 1); $i <= min($last, $cur + 1); $i++) $pages[] = $i;
                if ($cur < $last - 2) $pages[] = '...';
                if ($cur < $last - 1) $pages[] = $last;
            @endphp

            @foreach($pages as $pg)
                @if($pg === '...')
                <li class="page-item disabled"><span class="page-link" style="border:none;background:none">…</span></li>
                @else
                <li class="page-item {{ $cur == $pg ? 'active' : '' }}">
                    <a class="page-link" href="{{ $rows->url($pg) }}">{{ $pg }}</a>
                </li>
                @endif
            @endforeach

            {{-- Next --}}
            <li class="page-item {{ $rows->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $rows->hasMorePages() ? $rows->nextPageUrl() : '#' }}">→</a>
            </li>
        </ul>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/penilaian-kinerja.js') }}"></script>
<script>
function changePerPage(value) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', value);
    params.set('page', '1');
    window.location.search = params.toString();
}
</script>
@endpush