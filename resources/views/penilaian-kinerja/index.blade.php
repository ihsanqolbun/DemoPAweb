@extends('layouts.app')

@section('title', 'Penilaian Kinerja')
@section('page-title', 'Penilaian Kinerja')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/penilaian-kinerja.css') }}">
@endpush

@section('content')

{{-- FILTER --}}
<form method="GET" action="{{ route('penilaian-kinerja.index') }}" class="pk-filter">
    <div class="pk-filter-group">
        <label class="filter-label">Tahun</label>
        <input type="number" name="tahun" value="{{ $tahun }}"
               min="2020" max="{{ date('Y') }}" class="filter-input" style="width:100px">
    </div>
    <div class="pk-filter-group">
        <label class="filter-label">Triwulan</label>
        <select name="triwulan" class="filter-input form-select" style="width:200px">
            @foreach([1=>'Triwulan 1 (Jan – Mar)',2=>'Triwulan 2 (Apr – Jun)',3=>'Triwulan 3 (Jul – Sep)',4=>'Triwulan 4 (Okt – Des)'] as $tw => $label)
            <option value="{{ $tw }}" {{ $triwulan == $tw ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i> Tampilkan
    </button>
</form>

{{-- INFO BAR --}}
<div class="pk-info">
    <div class="pk-info-item">
        <i class="fas fa-calendar-alt" style="color:#1D9E75"></i>
        <span>Periode: <strong>{{ $tanggalAwal }} s/d {{ $tanggalAkhir }}</strong></span>
    </div>
    <div class="pk-info-dot"></div>
    <div class="pk-info-item">
        <i class="fas fa-layer-group" style="color:#1D9E75"></i>
        <span>Triwulan <strong>{{ $triwulan }}</strong> — {{ $labelTriwulan }} {{ $tahun }}</span>
    </div>
    <div class="pk-info-dot"></div>
    <div class="pk-info-item">
        <i class="fas fa-file-alt" style="color:#1D9E75"></i>
        <span>Total Perkara: <strong>{{ number_format($data['total_perkara']) }}</strong></span>
    </div>
</div>

{{-- NILAI BANNER --}}
<div class="pk-nilai-banner">
    <div>
        <div class="pk-nilai-label">Nilai Akhir Kinerja</div>
        <div class="pk-nilai-score">{{ $data['nilai_akhir'] }}</div>
    </div>
    <div class="pk-nilai-komponen">
        <div class="pk-komp-item">
            <div class="pk-komp-val">{{ $data['nilai_kinerja'] }}</div>
            <div class="pk-komp-label">Kinerja</div>
        </div>
        <div class="pk-komp-item">
            <div class="pk-komp-val">{{ $data['nilai_kepatuhan'] }}</div>
            <div class="pk-komp-label">Kepatuhan</div>
        </div>
        <div class="pk-komp-item">
            <div class="pk-komp-val">{{ $data['nilai_kelengkapan'] }}</div>
            <div class="pk-komp-label">Kelengkapan</div>
        </div>
        <div class="pk-komp-item neg">
            <div class="pk-komp-val">{{ $data['nilai_kesesuaian'] }}</div>
            <div class="pk-komp-label">Kesesuaian</div>
        </div>
    </div>
</div>

{{-- CARD GRID INDIKATOR --}}
@php
    $tw  = $triwulan;
    $thn = $tahun;
    $d   = $data;
    $p   = $persen;

    $cardGroups = [
        ['label' => 'Kinerja (50%)', 'class' => 'cg-kinerja', 'items' => [
            ['type'=>'putus',       'label'=>'Putus',        'bobot'=>'20%',   'count'=>$d['putus'],       'pct'=>$p['putus']],
            ['type'=>'minutasi',    'label'=>'Minutasi',     'bobot'=>'15%',   'count'=>$d['minutasi'],    'pct'=>$p['minutasi']],
            ['type'=>'publikasi',   'label'=>'Publikasi',    'bobot'=>'15%',   'count'=>$d['publikasi'],   'pct'=>$p['publikasi']],
        ]],
        ['label' => 'Input Data (40%)', 'class' => 'cg-input', 'items' => [
            ['type'=>'pendaftaran', 'label'=>'Pendaftaran',  'bobot'=>'2%',    'count'=>$d['pendaftaran'], 'pct'=>$p['pendaftaran']],
            ['type'=>'pen_pmh',     'label'=>'Pen. PMH',     'bobot'=>'2,5%',  'count'=>$d['pmh'],         'pct'=>$p['pmh']],
            ['type'=>'input_pmh',   'label'=>'Input PMH',    'bobot'=>'2%',    'count'=>$d['input_pmh'],   'pct'=>$p['input_pmh']],
            ['type'=>'pen_pp',      'label'=>'Pen. PP',      'bobot'=>'2,5%',  'count'=>$d['pen_pp'],      'pct'=>$p['pen_pp']],
            ['type'=>'input_pp',    'label'=>'Input PP',     'bobot'=>'2%',    'count'=>$d['input_pp'],    'pct'=>$p['input_pp']],
            ['type'=>'pen_js',      'label'=>'Pen. JS',      'bobot'=>'2,5%',  'count'=>$d['pen_js'],      'pct'=>$p['pen_js']],
            ['type'=>'pen_js_2',    'label'=>'Pen. JS (2)',  'bobot'=>'2%',    'count'=>$d['pen_js_2'],    'pct'=>$p['pen_js_2']],
            ['type'=>'pen_phs',     'label'=>'Pen. PHS',     'bobot'=>'2,5%',  'count'=>$d['pen_phs'],     'pct'=>$p['pen_phs']],
            ['type'=>'input_phs',   'label'=>'Input PHS',    'bobot'=>'2%',    'count'=>$d['input_phs'],   'pct'=>$p['input_phs']],
        ]],
        ['label' => 'Kepatuhan / Register (50%)', 'class' => 'cg-kepatuhan', 'items' => [
            ['type'=>'relaas',      'label'=>'Relaas',       'bobot'=>'2,5%',  'count'=>$d['relaas'],      'pct'=>$p['relaas']],
            ['type'=>'mediasi',     'label'=>'Mediasi',      'bobot'=>'2,5%',  'count'=>$d['mediasi'],     'pct'=>$p['mediasi']],
            ['type'=>'saksi',       'label'=>'Saksi',        'bobot'=>'2,5%',  'count'=>$d['saksi'],       'pct'=>$p['saksi']],
        ]],
        ['label' => 'Kelengkapan Dokumen (10%)', 'class' => 'cg-kelengkapan', 'items' => [
            ['type'=>'pbt',         'label'=>'PBT',          'bobot'=>'2,5%',  'count'=>$d['pbt'],         'pct'=>$p['pbt']],
            ['type'=>'bht',         'label'=>'BHT',          'bobot'=>'2,5%',  'count'=>$d['bht'],         'pct'=>$p['bht']],
            ['type'=>'sisa_panjar', 'label'=>'Sisa Panjar',  'bobot'=>'2,5%',  'count'=>$d['sisa_panjar'], 'pct'=>$p['sisa_panjar']],
            ['type'=>'arsip',       'label'=>'Arsip',        'bobot'=>'2,5%',  'count'=>$d['arsip'],       'pct'=>$p['arsip']],
            ['type'=>'delegasi',    'label'=>'Delegasi',     'bobot'=>'2,5%',  'count'=>$d['delegasi'],    'pct'=>$p['delegasi']],
            ['type'=>'petitum',     'label'=>'Petitum',      'bobot'=>'2,5%',  'count'=>$d['petitum'],     'pct'=>$p['petitum']],
            ['type'=>'relaas_kel',  'label'=>'Relaas',       'bobot'=>'2,5%',  'count'=>$d['relaas_kel'],  'pct'=>$p['relaas_kel']],
            ['type'=>'bas',         'label'=>'BAS',          'bobot'=>'3%',    'count'=>$d['bas'],         'pct'=>$p['bas']],
            ['type'=>'ac',          'label'=>'AC',           'bobot'=>'2%',    'count'=>$d['ac'],          'pct'=>$p['ac']],
        ]],
        ['label' => 'Kesesuaian (−10%)', 'class' => 'cg-kesesuaian', 'neg' => true, 'items' => [
            ['type'=>'sidang_akhir',        'label'=>'Sidang Akhir',        'bobot'=>'−2%', 'count'=>$d['sidang_akhir_terlambat'], 'pct'=>$p['sidang_akhir_terlambat']],
            ['type'=>'sinkron',             'label'=>'Sinkron',             'bobot'=>'−4%', 'count'=>$d['sinkron_tidak'],          'pct'=>$p['sinkron_tidak']],
            ['type'=>'permintaan_delegasi', 'label'=>'Perm. Delegasi',      'bobot'=>'−2%', 'count'=>$d['permintaan_delegasi'],    'pct'=>$p['permintaan_delegasi']],
            ['type'=>'status_persidangan',  'label'=>'Status Persidangan',  'bobot'=>'−2%', 'count'=>$d['status_persidangan'],     'pct'=>$p['status_persidangan']],
        ]],
    ];
@endphp

<div class="pk-card-section">
    <div class="pk-card-section-title">Navigasi Cepat per Indikator</div>
    @foreach($cardGroups as $group)
    <div class="pk-card-group">
        <div class="pk-card-group-label {{ $group['class'] }}">{{ $group['label'] }}</div>
        <div class="pk-card-grid">
            @foreach($group['items'] as $item)
            @php $isNeg = $group['neg'] ?? false; @endphp
            <a href="{{ route('penilaian-kinerja.detail', ['type'=>$item['type'],'tahun'=>$thn,'triwulan'=>$tw]) }}"
               class="pk-ind-card {{ $isNeg ? 'neg' : $group['class'] }}">
                <div class="pk-ind-label">{{ $item['label'] }} <span class="pk-ind-bobot">{{ $item['bobot'] }}</span></div>
                <div class="pk-ind-count">{{ number_format($item['count']) }}</div>
                <div class="pk-ind-pct">{{ ($isNeg && $item['pct'] != 0 ? '−' : '') . abs($item['pct']) }}%</div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- TABLE --}}
<div class="pk-table-card">
    <div class="pk-table-header">
        <div class="pk-table-title">Tabel Indikator Penilaian</div>
        <div class="pk-table-sub">Klik tiap cell untuk lihat detail perkara</div>
    </div>
    <div class="pk-table-scroll" id="pkTableScroll">
        <table class="pk-table">
            <thead>
                {{-- Row 1: Grup besar — Nilai Akhir TIDAK di sini --}}
                <tr>
                    <th class="th-info sticky" rowspan="3" style="min-width:160px;vertical-align:middle">Pengadilan</th>
                    <th class="th-kinerja" colspan="3">Kinerja (50%)</th>
                    <th class="th-input" colspan="8">Input Data (40%)</th>
                    <th class="th-kepatuhan" colspan="3">Kepatuhan / Register (50%)</th>
                    <th class="th-kelengkapan" colspan="9">Kelengkapan Dokumen (10%)</th>
                    <th class="th-kesesuaian" colspan="4">Kesesuaian (−10%)</th>
                    <th class="th-nilai" colspan="1"></th>
                </tr>
                {{-- Row 2 --}}
                <tr>
                    <th class="th-kinerja" colspan="3"></th>
                    <th class="th-input" colspan="8"></th>
                    <th class="th-kepatuhan" colspan="3"></th>
                    <th class="th-kelengkapan" colspan="9"></th>
                    <th class="th-kesesuaian" colspan="4"></th>
                    <th class="th-nilai"></th>
                </tr>
                {{-- Row 3: Label indikator, Nilai Akhir di ujung paling kanan --}}
                <tr>
                    <th class="th-kinerja">Putus<span class="bobot">(20%)</span></th>
                    <th class="th-kinerja">Minutasi<span class="bobot">(15%)</span></th>
                    <th class="th-kinerja">Publikasi<span class="bobot">(15%)</span></th>
                    <th class="th-input">Pendaftaran<span class="bobot">(2%)</span></th>
                    <th class="th-input">Pen. PMH<span class="bobot">(2,5%)</span></th>
                    <th class="th-input">Input PMH<span class="bobot">(2%)</span></th>
                    <th class="th-input">Pen. PP<span class="bobot">(2,5%)</span></th>
                    <th class="th-input">Input PP<span class="bobot">(2%)</span></th>
                    <th class="th-input">Pen. JS<span class="bobot">(2,5%)</span></th>
                    <th class="th-input">Pen. JS<span class="bobot">(2%)</span></th>
                    <th class="th-input">Pen. PHS<span class="bobot">(2,5%)</span></th>
                    <th class="th-input">Input PHS<span class="bobot">(2%)</span></th>
                    <th class="th-kepatuhan">Relaas<span class="bobot">(2,5%)</span></th>
                    <th class="th-kepatuhan">Mediasi<span class="bobot">(2,5%)</span></th>
                    <th class="th-kepatuhan">Saksi<span class="bobot">(2,5%)</span></th>
                    <th class="th-kelengkapan">PBT<span class="bobot">(2,5%)</span></th>
                    <th class="th-kelengkapan">BHT<span class="bobot">(2,5%)</span></th>
                    <th class="th-kelengkapan">Sisa Panjar<span class="bobot">(2,5%)</span></th>
                    <th class="th-kelengkapan">Arsip<span class="bobot">(2,5%)</span></th>
                    <th class="th-kelengkapan">Delegasi<span class="bobot">(2,5%)</span></th>
                    <th class="th-kelengkapan">Petitum<span class="bobot">(2,5%)</span></th>
                    <th class="th-kelengkapan">Relaas<span class="bobot">(2,5%)</span></th>
                    <th class="th-kelengkapan">BAS<span class="bobot">(3%)</span></th>
                    <th class="th-kelengkapan">AC<span class="bobot">(2%)</span></th>
                    <th class="th-kesesuaian">Sidang Akhir<span class="bobot">(−2%)</span></th>
                    <th class="th-kesesuaian">Sinkron<span class="bobot">(−4%)</span></th>
                    <th class="th-kesesuaian">Perm. Delegasi<span class="bobot">(−2%)</span></th>
                    <th class="th-kesesuaian">Status Persidangan<span class="bobot">(−2%)</span></th>
                    <th class="th-nilai" style="min-width:80px;position:sticky;right:0;z-index:3">Nilai Akhir</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $tw  = $triwulan;
                    $thn = $tahun;
                    $d   = $data;
                    $p   = $persen;
                    $cell = fn($type, $count, $pct, $neg = false) =>
                        '<a href="' . route('penilaian-kinerja.detail', ['type' => $type, 'tahun' => $thn, 'triwulan' => $tw]) . '" class="pk-cell' . ($neg ? ' neg' : '') . '">'
                        . '<span class="c-count">' . number_format($count) . '</span>'
                        . '<span class="c-pct">' . ($neg && $pct != 0 ? '−' : '') . abs($pct) . '%</span>'
                        . '</a>';
                @endphp
                <tr>
                    <td class="sticky">
                        <div class="pk-cell-info">
                            PA Tasikmalaya
                            <div style="font-size:11px;color:#999;font-weight:400;margin-top:1px">TW {{ $triwulan }} · {{ $tahun }}</div>
                        </div>
                    </td>
                    <td>{!! $cell('putus',               $d['putus'],                $p['putus']) !!}</td>
                    <td>{!! $cell('minutasi',            $d['minutasi'],             $p['minutasi']) !!}</td>
                    <td>{!! $cell('publikasi',           $d['publikasi'],            $p['publikasi']) !!}</td>
                    <td>{!! $cell('pendaftaran',         $d['pendaftaran'],          $p['pendaftaran']) !!}</td>
                    <td>{!! $cell('pen_pmh',             $d['pmh'],                  $p['pmh']) !!}</td>
                    <td>{!! $cell('input_pmh',           $d['input_pmh'],            $p['input_pmh']) !!}</td>
                    <td>{!! $cell('pen_pp',              $d['pen_pp'],               $p['pen_pp']) !!}</td>
                    <td>{!! $cell('input_pp',            $d['input_pp'],             $p['input_pp']) !!}</td>
                    <td>{!! $cell('pen_js',              $d['pen_js'],               $p['pen_js']) !!}</td>
                    <td>{!! $cell('pen_js_2',            $d['pen_js_2'],             $p['pen_js_2']) !!}</td>
                    <td>{!! $cell('pen_phs',             $d['pen_phs'],              $p['pen_phs']) !!}</td>
                    <td>{!! $cell('input_phs',           $d['input_phs'],            $p['input_phs']) !!}</td>
                    <td>{!! $cell('relaas',              $d['relaas'],               $p['relaas']) !!}</td>
                    <td>{!! $cell('mediasi',             $d['mediasi'],              $p['mediasi']) !!}</td>
                    <td>{!! $cell('saksi',               $d['saksi'],                $p['saksi']) !!}</td>
                    <td>{!! $cell('pbt',                 $d['pbt'],                  $p['pbt']) !!}</td>
                    <td>{!! $cell('bht',                 $d['bht'],                  $p['bht']) !!}</td>
                    <td>{!! $cell('sisa_panjar',         $d['sisa_panjar'],          $p['sisa_panjar']) !!}</td>
                    <td>{!! $cell('arsip',               $d['arsip'],                $p['arsip']) !!}</td>
                    <td>{!! $cell('delegasi',            $d['delegasi'],             $p['delegasi']) !!}</td>
                    <td>{!! $cell('petitum',             $d['petitum'],              $p['petitum']) !!}</td>
                    <td>{!! $cell('relaas_kel',          $d['relaas_kel'],           $p['relaas_kel']) !!}</td>
                    <td>{!! $cell('bas',                 $d['bas'],                  $p['bas']) !!}</td>
                    <td>{!! $cell('ac',                  $d['ac'],                   $p['ac']) !!}</td>
                    <td>{!! $cell('sidang_akhir',        $d['sidang_akhir_terlambat'],$p['sidang_akhir_terlambat'], true) !!}</td>
                    <td>{!! $cell('sinkron',             $d['sinkron_tidak'],        $p['sinkron_tidak'],          true) !!}</td>
                    <td>{!! $cell('permintaan_delegasi', $d['permintaan_delegasi'],  $p['permintaan_delegasi'],    true) !!}</td>
                    <td>{!! $cell('status_persidangan',  $d['status_persidangan'],   $p['status_persidangan'],     true) !!}</td>
                    {{-- NILAI AKHIR — paling ujung kanan --}}
                    <td class="td-nilai-akhir">
                        <div class="pk-cell-nilai">
                            <span class="v-score">{{ $data['nilai_akhir'] }}</span>
                            <span class="v-label">poin</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/penilaian-kinerja.js') }}"></script>
@endpush