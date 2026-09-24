<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PenilaianKinerjaService
{
    public function getTanggalTriwulan(int $tahun, int $triwulan): array
    {
        $bulanAwal    = (($triwulan - 1) * 3) + 1;
        $bulanAkhir   = $bulanAwal + 2;
        $tanggalAwal  = sprintf('%d-%02d-01', $tahun, $bulanAwal);
        $tanggalAkhir = date('Y-m-t', strtotime(sprintf('%d-%02d-01', $tahun, $bulanAkhir)));
        return [$tanggalAwal, $tanggalAkhir];
    }

    public function getData(string $tanggalAwal, string $tanggalAkhir): array
    {
        $cacheKey = 'pk_data_' . $tanggalAwal . '_' . $tanggalAkhir;

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($tanggalAwal, $tanggalAkhir) {

            // Query utama — semua count sekaligus
            $main = DB::connection('mysql')->selectOne("
                SELECT
                    COUNT(DISTINCT p.perkara_id) as total_perkara,
                    COUNT(DISTINCT CASE WHEN pp.tanggal_putusan IS NOT NULL THEN p.perkara_id END) as putus,
                    COUNT(DISTINCT CASE WHEN pp.tanggal_minutasi IS NOT NULL THEN p.perkara_id END) as minutasi,
                    COUNT(DISTINCT CASE WHEN pp.amar_putusan_anonimisasi_dok IS NOT NULL THEN p.perkara_id END) as publikasi,
                    COUNT(DISTINCT CASE WHEN pm.perkara_id IS NOT NULL THEN p.perkara_id END) as pmh,
                    COUNT(DISTINCT CASE WHEN pddm.perkara_id IS NOT NULL THEN p.perkara_id END) as input_pmh,
                    COUNT(DISTINCT CASE WHEN pp.amar_putusan_dok IS NOT NULL THEN p.perkara_id END) as input_pp,
                    COUNT(DISTINCT CASE WHEN pjs.tanggal_sidang IS NOT NULL THEN p.perkara_id END) as pen_js,
                    COUNT(DISTINCT CASE WHEN pjs.agenda LIKE '%pembuktian%' THEN p.perkara_id END) as saksi,
                    COUNT(DISTINCT CASE WHEN pbd.perkara_id IS NOT NULL THEN p.perkara_id END) as delegasi,
                    COUNT(DISTINCT CASE WHEN pp.status_putusan_kode IN ('DKB','DKBSEBAGIAN') THEN p.perkara_id END) as petitum,
                    COUNT(DISTINCT CASE WHEN p.jenis_perkara_nama LIKE '%cerai%' AND pp.tanggal_putusan IS NOT NULL THEN p.perkara_id END) as ac,
                    COUNT(DISTINCT CASE WHEN pjs.agenda LIKE '%putusan%' AND DATEDIFF(pjs.tanggal_sidang, p.tanggal_pendaftaran) > 60 THEN p.perkara_id END) as sidang_akhir_terlambat,
                    COUNT(DISTINCT CASE WHEN pjs.ditunda = 1 THEN p.perkara_id END) as status_persidangan
                FROM perkara p
                LEFT JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id
                LEFT JOIN perkara_mediasi pm ON p.perkara_id = pm.perkara_id
                LEFT JOIN perkara_data_dukung_mediasi pddm ON p.perkara_id = pddm.perkara_id
                LEFT JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id
                LEFT JOIN perkara_banding_detil pbd ON p.perkara_id = pbd.perkara_id
                WHERE p.tanggal_pendaftaran BETWEEN ? AND ?
            ", [$tanggalAwal, $tanggalAkhir]);

            // Query mediasi
            $mediasi = DB::connection('mysql')->selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN hasil_mediasi = 'S'  THEN pm.perkara_id END) as berhasil_akta,
                    COUNT(DISTINCT CASE WHEN hasil_mediasi = 'Y2' THEN pm.perkara_id END) as berhasil_pencabutan,
                    COUNT(DISTINCT CASE WHEN hasil_mediasi = 'Y1' THEN pm.perkara_id END) as berhasil_sebagian,
                    COUNT(DISTINCT pm.perkara_id) as total_mediasi
                FROM perkara_mediasi pm
                JOIN perkara p ON pm.perkara_id = p.perkara_id
                WHERE p.tanggal_pendaftaran BETWEEN ? AND ?
            ", [$tanggalAwal, $tanggalAkhir]);

            // Query biaya — pakai JOIN lebih efisien dari EXISTS subquery
            $biaya = DB::connection('mysql')->selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN pb.jenis_transaksi = 1 OR pb.uraian = 'PANJAR' THEN pb.perkara_id END) as pbt,
                    COUNT(DISTINCT CASE WHEN pb.kategori_id = 12 THEN pb.perkara_id END) as bht,
                    COUNT(DISTINCT CASE WHEN pb.sisa > 0 THEN pb.perkara_id END) as sisa_panjar
                FROM perkara_biaya pb
                JOIN perkara p ON pb.perkara_id = p.perkara_id
                WHERE p.tanggal_pendaftaran BETWEEN ? AND ?
            ", [$tanggalAwal, $tanggalAkhir]);

            // Query sinkronisasi
            $sinkron = DB::connection('mysql')->selectOne("
                SELECT COUNT(DISTINCT p.perkara_id) as sinkron_tidak
                FROM perkara p
                JOIN perkara_banding_detil pbd ON p.perkara_id = pbd.perkara_id
                JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id
                WHERE p.tanggal_pendaftaran BETWEEN ? AND ?
                AND pbd.tanggal_kirim_salinan_putusan IS NOT NULL
                AND pp.tanggal_putusan IS NOT NULL
                AND DATEDIFF(pbd.tanggal_kirim_salinan_putusan, pp.tanggal_putusan) > 70
            ", [$tanggalAwal, $tanggalAkhir]);

            // Hitung persentase mediasi
            $totalMediasi  = $mediasi->total_mediasi ?: 1;
            $persenMediasi = (
                ($mediasi->berhasil_akta * 100) +
                ($mediasi->berhasil_pencabutan * 100) +
                ($mediasi->berhasil_sebagian * 50)
            ) / $totalMediasi;

            $tp         = $main->total_perkara ?: 1;
            $putus      = (int) $main->putus;
            $penJs      = (int) $main->pen_js;
            $sinkronTdk = (int) $sinkron->sinkron_tidak;

            // Hitung nilai komponen — logika sama persis legacy
            $nilaiKinerja =
                ($putus / $tp) * 20 +
                ($main->minutasi / $tp) * 15 +
                ($main->publikasi / $tp) * 15 +
                ($main->total_perkara / $tp) * 2 +
                ($main->pmh / $tp) * 2.5 +
                ($main->input_pmh / $tp) * 2 +
                ($putus / $tp) * 2.5 +
                ($main->input_pp / $tp) * 2 +
                ($penJs / $tp) * 2.5 +
                ($penJs / $tp) * 2 +
                ($penJs / $tp) * 2.5 +
                ($penJs / $tp) * 2;

            $nilaiMediasi   = ($persenMediasi / 100) * 2.5;
            $nilaiKepatuhan =
                ($penJs / $tp) * 2.5 +
                $nilaiMediasi +
                ($main->saksi / $tp) * 2.5 +
                ($biaya->pbt / $tp) * 2.5 +
                ($biaya->bht / $tp) * 2.5 +
                ($biaya->sisa_panjar / $tp) * 2.5 +
                ($main->input_pp / $tp) * 2.5;

            $nilaiKelengkapan =
                ($main->delegasi / $tp) * 2.5 +
                ($main->petitum / $tp) * 2.5 +
                ($penJs / $tp) * 2.5 +
                ($penJs / $tp) * 3 +
                ($main->ac / $tp) * 2;

            $nilaiKesesuaian = -1 * (
                ($main->sidang_akhir_terlambat / $tp) * 2 +
                ($sinkronTdk / $tp) * 4 +
                ($main->delegasi / $tp) * 2 +
                ($main->status_persidangan / $tp) * 2
            );

            $nilaiAkhir = $nilaiKinerja + $nilaiKepatuhan + $nilaiKelengkapan + $nilaiKesesuaian;

            return [
                'total_perkara'           => (int) $main->total_perkara,
                'putus'                   => $putus,
                'minutasi'                => (int) $main->minutasi,
                'publikasi'               => (int) $main->publikasi,
                'pendaftaran'             => (int) $main->total_perkara,
                'pmh'                     => (int) $main->pmh,
                'input_pmh'               => (int) $main->input_pmh,
                'pen_pp'                  => $putus,
                'input_pp'                => (int) $main->input_pp,
                'pen_js'                  => $penJs,
                'pen_js_2'                => $penJs,
                'pen_phs'                 => $penJs,
                'input_phs'               => $penJs,
                'relaas'                  => $penJs,
                'mediasi'                 => round($persenMediasi, 2),
                'saksi'                   => (int) $main->saksi,
                'pbt'                     => (int) $biaya->pbt,
                'bht'                     => (int) $biaya->bht,
                'sisa_panjar'             => (int) $biaya->sisa_panjar,
                'arsip'                   => (int) $main->input_pp,
                'delegasi'                => (int) $main->delegasi,
                'petitum'                 => (int) $main->petitum,
                'relaas_kel'              => $penJs,
                'bas'                     => $penJs,
                'ac'                      => (int) $main->ac,
                'sidang_akhir_terlambat'  => (int) $main->sidang_akhir_terlambat,
                'sinkron_tidak'           => $sinkronTdk,
                'permintaan_delegasi'     => (int) $main->delegasi,
                'status_persidangan'      => (int) $main->status_persidangan,
                // 2 desimal saja
                'nilai_kinerja'           => round($nilaiKinerja, 2),
                'nilai_kepatuhan'         => round($nilaiKepatuhan, 2),
                'nilai_kelengkapan'       => round($nilaiKelengkapan, 2),
                'nilai_kesesuaian'        => round($nilaiKesesuaian, 2),
                'nilai_akhir'             => round($nilaiAkhir, 2),
            ];
        });
    }

    public function getPersen(array $d): array
    {
        $tp  = $d['total_perkara'] ?: 1;
        $pct = fn($val, $bobot, $neg = false) =>
            round(($val / $tp) * $bobot * ($neg ? -1 : 1), 2);

        return [
            'putus'                  => $pct($d['putus'], 20),
            'minutasi'               => $pct($d['minutasi'], 15),
            'publikasi'              => $pct($d['publikasi'], 15),
            'pendaftaran'            => $pct($d['pendaftaran'], 2),
            'pmh'                    => $pct($d['pmh'], 2.5),
            'input_pmh'              => $pct($d['input_pmh'], 2),
            'pen_pp'                 => $pct($d['pen_pp'], 2.5),
            'input_pp'               => $pct($d['input_pp'], 2),
            'pen_js'                 => $pct($d['pen_js'], 2.5),
            'pen_js_2'               => $pct($d['pen_js_2'], 2),
            'pen_phs'                => $pct($d['pen_phs'], 2.5),
            'input_phs'              => $pct($d['input_phs'], 2),
            'relaas'                 => $pct($d['relaas'], 2.5),
            'mediasi'                => round(($d['mediasi'] / 100) * 2.5, 2),
            'saksi'                  => $pct($d['saksi'], 2.5),
            'pbt'                    => $pct($d['pbt'], 2.5),
            'bht'                    => $pct($d['bht'], 2.5),
            'sisa_panjar'            => $pct($d['sisa_panjar'], 2.5),
            'arsip'                  => $pct($d['arsip'], 2.5),
            'delegasi'               => $pct($d['delegasi'], 2.5),
            'petitum'                => $pct($d['petitum'], 2.5),
            'relaas_kel'             => $pct($d['relaas_kel'], 2.5),
            'bas'                    => $pct($d['bas'], 3),
            'ac'                     => $pct($d['ac'], 2),
            'sidang_akhir_terlambat' => $pct($d['sidang_akhir_terlambat'], 2, true),
            'sinkron_tidak'          => $pct($d['sinkron_tidak'], 4, true),
            'permintaan_delegasi'    => $pct($d['permintaan_delegasi'], 2, true),
            'status_persidangan'     => $pct($d['status_persidangan'], 2, true),
        ];
    }
}