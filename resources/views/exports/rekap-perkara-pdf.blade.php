<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 8pt; color: #333; }

        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #0a7a5e; padding-bottom: 8px; }
        .header h2 { font-size: 13pt; color: #0a7a5e; font-weight: bold; margin-bottom: 3px; }
        .header p  { font-size: 9pt; color: #555; }

        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 8pt; color: #555; }

        .stat-grid { display: flex; gap: 6px; margin-bottom: 12px; }
        .stat-box {
            flex: 1; border: 1px solid #ddd; border-radius: 6px;
            padding: 6px 4px; text-align: center;
        }
        .stat-box .val { font-size: 18pt; font-weight: bold; color: #0a7a5e; }
        .stat-box .lbl { font-size: 7pt; color: #777; margin-top: 2px; }

        .section-title {
            background: #0a7a5e; color: white; font-weight: bold;
            font-size: 9pt; padding: 5px 8px; margin: 10px 0 5px; border-radius: 4px;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        thead th {
            background: #0a7a5e; color: white;
            padding: 4px 3px; font-size: 7pt; text-align: center;
            border: 1px solid #088;
        }
        tbody td {
            padding: 3px; font-size: 7pt;
            border: 1px solid #ddd; vertical-align: top;
        }
        tbody tr:nth-child(even) { background: #f8fafb; }

        .ringkasan table { width: 60%; margin: 0 auto; }
        .ringkasan thead th { background: #333; }
        .ringkasan .total-row td { background: #e8f5f0; font-weight: bold; }

        .page-break { page-break-before: always; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .no-col          { width: 22px; }
        .tgl-col         { width: 52px; }
        .no-perkara-col  { width: 95px; }
        .jenis-col       { width: 90px; }
        .cara-col        { width: 42px; }
        .hakim-col       { width: 80px; }
        .status-col      { width: 58px; }
        .tgl-small       { width: 50px; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <h2>LAPORAN REKAPITULASI PERKARA</h2>
    <p>{{ $periode }}</p>
    <p>Pengadilan Agama Tasikmalaya Kelas 1A</p>
</div>

<div class="info-row">
    <span>Tanggal cetak: {{ $tanggalCetak }} {{ $jamCetak }}</span>
    <span>Total perkara: {{ $totalMasuk }} perkara</span>
</div>

{{-- Statistik --}}
<div class="stat-grid">
    <div class="stat-box">
        <div class="val">{{ $countPdtg }}</div>
        <div class="lbl">Gugatan (Pdt.G)</div>
    </div>
    <div class="stat-box">
        <div class="val">{{ $countPdtp }}</div>
        <div class="lbl">Permohonan (Pdt.P)</div>
    </div>
    <div class="stat-box">
        <div class="val">{{ $eCourt }}</div>
        <div class="lbl">E-Court</div>
    </div>
    <div class="stat-box">
        <div class="val">{{ $manual }}</div>
        <div class="lbl">Manual</div>
    </div>
    <div class="stat-box">
        <div class="val">{{ $dalamProses }}</div>
        <div class="lbl">Dalam Proses</div>
    </div>
    <div class="stat-box">
        <div class="val">{{ $putus }}</div>
        <div class="lbl">Putus</div>
    </div>
    <div class="stat-box">
        <div class="val">{{ $minutasi }}</div>
        <div class="lbl">Minutasi</div>
    </div>
</div>

{{-- Tabel Pdt.G --}}
@if(!empty($pdtg) && count($pdtg) > 0)
<div class="section-title">PERKARA GUGATAN (Pdt.G) — {{ count($pdtg) }} Perkara</div>
<table>
    <thead>
        <tr>
            <th class="no-col">No</th>
            <th class="tgl-col">Tanggal</th>
            <th class="no-perkara-col">No. Perkara</th>
            <th class="jenis-col">Jenis Perkara</th>
            <th class="cara-col">Cara</th>
            <th>Pihak 1</th>
            <th>Pihak 2</th>
            <th class="hakim-col">Hakim</th>
            <th class="status-col">Status</th>
            <th class="tgl-small">Tgl Putusan</th>
            <th class="tgl-small">Tgl Minutasi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pdtg as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">
                {{ $row->tanggal_pendaftaran ? \Carbon\Carbon::parse($row->tanggal_pendaftaran)->format('d/m/Y') : '-' }}
            </td>
            <td>{{ $row->nomor_perkara ?? '-' }}</td>
            <td>{{ $row->jenis_perkara_nama ?? '-' }}</td>
            <td class="text-center">{{ ($row->cara_daftar ?? '') === 'e-court' ? 'E-Court' : 'Manual' }}</td>
            <td>{{ $row->pihak1_text ?? '-' }}</td>
            <td>{{ $row->pihak2_text ?? '-' }}</td>
            <td>{{ $row->hakim_nama ?? '-' }}</td>
            <td class="text-center">
                {{ \App\Services\RekapPerkaraService::formatStatus($row->status_perkara ?? 'dalam_proses') }}
            </td>
            <td class="text-center">
                {{ ($row->tanggal_putusan ?? '') ? \Carbon\Carbon::parse($row->tanggal_putusan)->format('d/m/Y') : '-' }}
            </td>
            <td class="text-center">
                {{ ($row->tanggal_minutasi ?? '') ? \Carbon\Carbon::parse($row->tanggal_minutasi)->format('d/m/Y') : '-' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Tabel Pdt.P --}}
@if(!empty($pdtp) && count($pdtp) > 0)
<div class="section-title" style="margin-top:14px">PERKARA PERMOHONAN (Pdt.P) — {{ count($pdtp) }} Perkara</div>
<table>
    <thead>
        <tr>
            <th class="no-col">No</th>
            <th class="tgl-col">Tanggal</th>
            <th class="no-perkara-col">No. Perkara</th>
            <th class="jenis-col">Jenis Perkara</th>
            <th class="cara-col">Cara</th>
            <th>Pihak 1</th>
            <th>Pihak 2</th>
            <th class="hakim-col">Hakim</th>
            <th class="status-col">Status</th>
            <th class="tgl-small">Tgl Putusan</th>
            <th class="tgl-small">Tgl Minutasi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pdtp as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">
                {{ $row->tanggal_pendaftaran ? \Carbon\Carbon::parse($row->tanggal_pendaftaran)->format('d/m/Y') : '-' }}
            </td>
            <td>{{ $row->nomor_perkara ?? '-' }}</td>
            <td>{{ $row->jenis_perkara_nama ?? '-' }}</td>
            <td class="text-center">{{ ($row->cara_daftar ?? '') === 'e-court' ? 'E-Court' : 'Manual' }}</td>
            <td>{{ $row->pihak1_text ?? '-' }}</td>
            <td>{{ $row->pihak2_text ?? '-' }}</td>
            <td>{{ $row->hakim_nama ?? '-' }}</td>
            <td class="text-center">
                {{ \App\Services\RekapPerkaraService::formatStatus($row->status_perkara ?? 'dalam_proses') }}
            </td>
            <td class="text-center">
                {{ ($row->tanggal_putusan ?? '') ? \Carbon\Carbon::parse($row->tanggal_putusan)->format('d/m/Y') : '-' }}
            </td>
            <td class="text-center">
                {{ ($row->tanggal_minutasi ?? '') ? \Carbon\Carbon::parse($row->tanggal_minutasi)->format('d/m/Y') : '-' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Ringkasan --}}
<div class="section-title page-break" style="margin-top:14px">RINGKASAN LAPORAN</div>
<div class="ringkasan">
    <table>
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Kategori Perkara</th>
                <th style="width:60px">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr><td class="text-center">1</td><td>Gugatan (Pdt.G)</td><td class="text-center">{{ $countPdtg }}</td></tr>
            <tr><td class="text-center">2</td><td>Permohonan (Pdt.P)</td><td class="text-center">{{ $countPdtp }}</td></tr>
            <tr><td class="text-center">3</td><td>E-Court</td><td class="text-center">{{ $eCourt }}</td></tr>
            <tr><td class="text-center">4</td><td>Manual</td><td class="text-center">{{ $manual }}</td></tr>
            <tr><td class="text-center">5</td><td>Dalam Proses</td><td class="text-center">{{ $dalamProses }}</td></tr>
            <tr><td class="text-center">6</td><td>Putus</td><td class="text-center">{{ $putus }}</td></tr>
            <tr><td class="text-center">7</td><td>Minutasi Selesai</td><td class="text-center">{{ $minutasi }}</td></tr>
            <tr class="total-row">
                <td colspan="2" class="text-center"><strong>TOTAL SELURUH PERKARA</strong></td>
                <td class="text-center"><strong>{{ $totalMasuk }}</strong></td>
            </tr>
        </tbody>
    </table>
</div>

</body>
</html>