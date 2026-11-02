<?php

namespace App\Http\Controllers;

use App\Services\PenilaianKinerjaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenilaianKinerjaController extends Controller
{
    public function __construct(private PenilaianKinerjaService $service) {}

    public function index(Request $request)
    {
        $tahun    = (int) $request->get('tahun', date('Y'));
        $triwulan = (int) $request->get('triwulan', (int) ceil(date('n') / 3));

        $tahun    = max(2020, min((int) date('Y'), $tahun));
        $triwulan = max(1, min(4, $triwulan));

        [$tanggalAwal, $tanggalAkhir] = $this->service->getTanggalTriwulan($tahun, $triwulan);

        $data   = $this->service->getData($tanggalAwal, $tanggalAkhir);
        $persen = $this->service->getPersen($data);

        $labelTriwulan = [1=>'Jan – Mar', 2=>'Apr – Jun', 3=>'Jul – Sep', 4=>'Okt – Des'];

        return view('penilaian-kinerja.index', [
            'tahun'         => $tahun,
            'triwulan'      => $triwulan,
            'tanggalAwal'   => $tanggalAwal,
            'tanggalAkhir'  => $tanggalAkhir,
            'labelTriwulan' => $labelTriwulan[$triwulan],
            'data'          => $data,
            'persen'        => $persen,
        ]);
    }

    public function detail(Request $request)
    {
        $type     = $request->get('type', 'putus');
        $tahun    = (int) $request->get('tahun', date('Y'));
        $triwulan = (int) $request->get('triwulan', (int) ceil(date('n') / 3));
        $perPage  = in_array((int) $request->get('per_page'), [10, 25, 50]) ? (int) $request->get('per_page') : 10;

        $tahun    = max(2020, min((int) date('Y'), $tahun));
        $triwulan = max(1, min(4, $triwulan));

        [$tanggalAwal, $tanggalAkhir] = $this->service->getTanggalTriwulan($tahun, $triwulan);

        $labelTriwulan = [1=>'Jan – Mar', 2=>'Apr – Jun', 3=>'Jul – Sep', 4=>'Okt – Des'];

        // Konfigurasi per type: [title, desc, category, query_count, query_data, columns]
        $config = $this->getDetailConfig($type);

        if (!$config) {
            abort(404, 'Tipe indikator tidak ditemukan.');
        }

        // Hitung total
        $total = DB::connection('mysql')->selectOne(
            $config['query_count'],
            ['awal' => $tanggalAwal, 'akhir' => $tanggalAkhir]
        )->total ?? 0;

        // Ambil data dengan paginate manual
        $rows = DB::connection('mysql')->select(
            $config['query_data'],
            [
                'awal'   => $tanggalAwal,
                'akhir'  => $tanggalAkhir,
                'limit'  => $perPage,
                'offset' => (((int) $request->get('page', 1)) - 1) * $perPage,
            ]
        );

        // Bungkus jadi LengthAwarePaginator untuk pagination view
        $currentPage = (int) $request->get('page', 1);
        $paginator   = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('penilaian-kinerja.detail', [
            'type'          => $type,
            'tahun'         => $tahun,
            'triwulan'      => $triwulan,
            'tanggalAwal'   => $tanggalAwal,
            'tanggalAkhir'  => $tanggalAkhir,
            'labelTriwulan' => $labelTriwulan[$triwulan],
            'title'         => $config['title'],
            'desc'          => $config['desc'],
            'category'      => $config['category'],
            'columns'       => $config['columns'],
            'rows'          => $paginator,
            'perPage'       => $perPage,
        ]);
    }

    // ============================================================
    // Konfigurasi query per type — port dari detail_publikasi.php
    // ============================================================
    private function getDetailConfig(string $type): ?array
    {
        $configs = [
            'putus' => [
                'title' => 'Detail Putus', 'desc' => 'Perkara Yang Sudah Diputus (Bobot 20%)', 'category' => 'Kinerja',
                'columns' => ['Tgl Putusan', 'Status Putusan', 'Durasi (hari)'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.tanggal_putusan IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pp.tanggal_putusan, pp.status_putusan_nama, DATEDIFF(pp.tanggal_putusan, p.tanggal_pendaftaran) as durasi_hari FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.tanggal_putusan IS NOT NULL ORDER BY pp.tanggal_putusan DESC LIMIT :limit OFFSET :offset",
            ],
            'minutasi' => [
                'title' => 'Detail Minutasi', 'desc' => 'Perkara Yang Sudah Diminutasi (Bobot 15%)', 'category' => 'Kinerja',
                'columns' => ['Tgl Putusan', 'Tgl Minutasi', 'Selisih (hari)'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.tanggal_minutasi IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pp.tanggal_putusan, pp.tanggal_minutasi, DATEDIFF(pp.tanggal_minutasi, pp.tanggal_putusan) as selisih_hari FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.tanggal_minutasi IS NOT NULL ORDER BY pp.tanggal_minutasi DESC LIMIT :limit OFFSET :offset",
            ],
            'publikasi' => [
                'title' => 'Detail Publikasi Putusan', 'desc' => 'Perkara Yang Sudah Dipublikasi (Bobot 15%)', 'category' => 'Kinerja',
                'columns' => ['Tgl Putusan', 'Tgl Minutasi', 'Status Publikasi'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.amar_putusan_anonimisasi_dok IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pp.tanggal_putusan, pp.tanggal_minutasi, pp.amar_putusan_anonimisasi_dok as status_publikasi FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.amar_putusan_anonimisasi_dok IS NOT NULL ORDER BY p.tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'pendaftaran' => [
                'title' => 'Detail Pendaftaran', 'desc' => 'Perkara Yang Terdaftar (Bobot 2%)', 'category' => 'Kinerja',
                'columns' => ['Tahapan Terakhir', 'Proses Terakhir'],
                'query_count' => "SELECT COUNT(DISTINCT perkara_id) as total FROM perkara WHERE tanggal_pendaftaran BETWEEN :awal AND :akhir",
                'query_data'  => "SELECT DISTINCT perkara_id, nomor_perkara, tanggal_pendaftaran, jenis_perkara_nama, pihak1_text, pihak2_text, tahapan_terakhir_text, proses_terakhir_text FROM perkara WHERE tanggal_pendaftaran BETWEEN :awal AND :akhir ORDER BY tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'pen_pmh' => [
                'title' => 'Detail Penetapan PMH', 'desc' => 'Perkara Dengan Penetapan Mediasi (Bobot 2.5%)', 'category' => 'Kinerja',
                'columns' => ['Tgl Penetapan Mediasi', 'Mediator'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_mediasi pm ON p.perkara_id = pm.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pm.penetapan_tanggal_mediasi, pm.mediator_text FROM perkara p JOIN perkara_mediasi pm ON p.perkara_id = pm.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir ORDER BY pm.penetapan_tanggal_mediasi DESC LIMIT :limit OFFSET :offset",
            ],
            'input_pmh' => [
                'title' => 'Detail Input PMH', 'desc' => 'Perkara Dengan Data Dukung Mediasi (Bobot 2%)', 'category' => 'Kinerja',
                'columns' => [],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_data_dukung_mediasi pddm ON p.perkara_id = pddm.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text FROM perkara p JOIN perkara_data_dukung_mediasi pddm ON p.perkara_id = pddm.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir ORDER BY p.tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'pen_pp' => [
                'title' => 'Detail Penetapan PP', 'desc' => 'Perkara Dengan Penetapan Putusan (Bobot 2.5%)', 'category' => 'Kinerja',
                'columns' => ['Tgl Putusan', 'Status Putusan'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.tanggal_putusan IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pp.tanggal_putusan, pp.status_putusan_nama FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.tanggal_putusan IS NOT NULL ORDER BY pp.tanggal_putusan DESC LIMIT :limit OFFSET :offset",
            ],
            'input_pp' => [
                'title' => 'Detail Input PP', 'desc' => 'Perkara Dengan Dokumen Putusan (Bobot 2%)', 'category' => 'Kinerja',
                'columns' => ['Tgl Putusan', 'Status Dokumen'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.amar_putusan_dok IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pp.tanggal_putusan, pp.amar_putusan_dok as status_dokumen FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.amar_putusan_dok IS NOT NULL ORDER BY pp.tanggal_putusan DESC LIMIT :limit OFFSET :offset",
            ],
            'pen_js' => [
                'title' => 'Detail Penetapan Jadwal Sidang', 'desc' => 'Perkara Dengan Jadwal Sidang (Bobot 2.5%)', 'category' => 'Kinerja',
                'columns' => ['Jadwal Pertama', 'Jumlah Sidang'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.tanggal_sidang IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, MIN(pjs.tanggal_sidang) as jadwal_pertama, COUNT(pjs.perkara_id) as jumlah_sidang FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.tanggal_sidang IS NOT NULL GROUP BY p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text ORDER BY p.tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'pen_js_2'  => null, // sama dengan pen_js
            'pen_phs'   => null, // sama dengan pen_js
            'input_phs' => null, // sama dengan pen_js
            'relaas' => [
                'title' => 'Detail Relaas', 'desc' => 'Kelengkapan Relaas (Bobot 2.5%)', 'category' => 'Kepatuhan',
                'columns' => [],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.tanggal_sidang IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.tanggal_sidang IS NOT NULL ORDER BY p.tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'mediasi' => [
                'title' => 'Detail Mediasi', 'desc' => 'Data Mediasi (Bobot 2.5%)', 'category' => 'Kepatuhan',
                'columns' => ['Mediator', 'Tgl Penetapan', 'Tgl Mulai', 'Hasil'],
                'query_count' => "SELECT COUNT(DISTINCT pm.perkara_id) as total FROM perkara_mediasi pm JOIN perkara p ON pm.perkara_id = p.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pm.mediator_text, pm.penetapan_tanggal_mediasi, pm.dimulai_mediasi, pm.hasil_mediasi, pm.status_mediasi_nama as status_mediasi FROM perkara p JOIN perkara_mediasi pm ON p.perkara_id = pm.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir ORDER BY pm.penetapan_tanggal_mediasi DESC LIMIT :limit OFFSET :offset",
            ],
            'saksi' => [
                'title' => 'Detail Saksi / Pembuktian', 'desc' => 'Perkara Dengan Sidang Pembuktian (Bobot 2.5%)', 'category' => 'Kepatuhan',
                'columns' => ['Tgl Sidang', 'Agenda'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.agenda LIKE '%pembuktian%'",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pjs.tanggal_sidang, pjs.agenda FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.agenda LIKE '%pembuktian%' ORDER BY pjs.tanggal_sidang DESC LIMIT :limit OFFSET :offset",
            ],
            'pbt' => [
                'title' => 'Detail PBT (Panjar Biaya)', 'desc' => 'Perkara Dengan Panjar Biaya (Bobot 2.5%)', 'category' => 'Kepatuhan',
                'columns' => ['Panjar (Rp)'],
                'query_count' => "SELECT COUNT(DISTINCT perkara_id) as total FROM (SELECT DISTINCT p.perkara_id FROM perkara p JOIN perkara_biaya pb ON p.perkara_id = pb.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND (pb.jenis_transaksi = 1 OR pb.uraian = 'PANJAR')) as combined",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, (SELECT SUM(CASE WHEN pb2.uraian = 'PANJAR' THEN pb2.jumlah ELSE 0 END) FROM perkara_biaya pb2 WHERE pb2.perkara_id = p.perkara_id) as panjar_sipp FROM perkara p WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND EXISTS (SELECT 1 FROM perkara_biaya pb WHERE pb.perkara_id = p.perkara_id AND (pb.jenis_transaksi = 1 OR pb.uraian = 'PANJAR')) ORDER BY p.tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'bht' => [
                'title' => 'Detail BHT (Biaya Hukum Tunai)', 'desc' => 'Perkara Dengan BHT (Bobot 2.5%)', 'category' => 'Kepatuhan',
                'columns' => ['BHT (Rp)'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_biaya pb ON p.perkara_id = pb.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pb.kategori_id = 12",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, (SELECT SUM(pb2.jumlah) FROM perkara_biaya pb2 WHERE pb2.perkara_id = p.perkara_id AND pb2.kategori_id = 12) as bht_sipp FROM perkara p JOIN perkara_biaya pb ON p.perkara_id = pb.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pb.kategori_id = 12 ORDER BY p.tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'sisa_panjar' => [
                'title' => 'Detail Sisa Panjar', 'desc' => 'Perkara Dengan Sisa Panjar (Bobot 2.5%)', 'category' => 'Kepatuhan',
                'columns' => ['Sisa Panjar (Rp)'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_biaya pb ON p.perkara_id = pb.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pb.sisa > 0",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, (SELECT MAX(pb2.sisa) FROM perkara_biaya pb2 WHERE pb2.perkara_id = p.perkara_id) as sisa_sipp FROM perkara p JOIN perkara_biaya pb ON p.perkara_id = pb.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pb.sisa > 0 ORDER BY p.tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'arsip' => [
                'title' => 'Detail Arsip', 'desc' => 'Perkara Dengan Dokumen Arsip (Bobot 2.5%)', 'category' => 'Kepatuhan',
                'columns' => ['Tgl Putusan', 'Status Arsip'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.amar_putusan_dok IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pp.tanggal_putusan, pp.amar_putusan_dok as status_arsip FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.amar_putusan_dok IS NOT NULL ORDER BY pp.tanggal_putusan DESC LIMIT :limit OFFSET :offset",
            ],
            'delegasi' => [
                'title' => 'Detail Delegasi', 'desc' => 'Perkara Banding (Bobot 2.5%)', 'category' => 'Kelengkapan',
                'columns' => ['Tgl Permohonan', 'Tgl Kirim Salinan'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_banding_detil pbd ON p.perkara_id = pbd.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pbd.permohonan_banding, pbd.tanggal_kirim_salinan_putusan FROM perkara p JOIN perkara_banding_detil pbd ON p.perkara_id = pbd.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir ORDER BY pbd.permohonan_banding DESC LIMIT :limit OFFSET :offset",
            ],
            'petitum' => [
                'title' => 'Detail Petitum', 'desc' => 'Perkara Dengan Status DKB (Bobot 2.5%)', 'category' => 'Kelengkapan',
                'columns' => ['Tgl Putusan', 'Status'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.status_putusan_kode IN ('DKB', 'DKBSEBAGIAN')",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pp.tanggal_putusan, pp.status_putusan_nama FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pp.status_putusan_kode IN ('DKB', 'DKBSEBAGIAN') ORDER BY pp.tanggal_putusan DESC LIMIT :limit OFFSET :offset",
            ],
            'relaas_kel' => [
                'title' => 'Detail Relaas (Kelengkapan)', 'desc' => 'Kelengkapan Relaas (Bobot 2.5%)', 'category' => 'Kelengkapan',
                'columns' => [],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.tanggal_sidang IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.tanggal_sidang IS NOT NULL ORDER BY p.tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'bas' => [
                'title' => 'Detail BAS (Berita Acara Sidang)', 'desc' => 'Kelengkapan BAS (Bobot 3%)', 'category' => 'Kelengkapan',
                'columns' => ['Jml Sidang'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.tanggal_sidang IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, COUNT(pjs.perkara_id) as jumlah_sidang FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.tanggal_sidang IS NOT NULL GROUP BY p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text ORDER BY p.tanggal_pendaftaran DESC LIMIT :limit OFFSET :offset",
            ],
            'ac' => [
                'title' => 'Detail AC (Akta Cerai)', 'desc' => 'Perkara Cerai Dengan Putusan (Bobot 2%)', 'category' => 'Kelengkapan',
                'columns' => ['Tgl Putusan', 'Status'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND p.jenis_perkara_nama LIKE '%cerai%' AND pp.tanggal_putusan IS NOT NULL",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pp.tanggal_putusan, pp.status_putusan_nama FROM perkara p JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND p.jenis_perkara_nama LIKE '%cerai%' AND pp.tanggal_putusan IS NOT NULL ORDER BY pp.tanggal_putusan DESC LIMIT :limit OFFSET :offset",
            ],
            'sidang_akhir' => [
                'title' => 'Detail Sidang Akhir Terlambat', 'desc' => 'Sidang Akhir Lebih Dari 60 Hari (Bobot -2%)', 'category' => 'Kesesuaian',
                'columns' => ['Tgl Sidang', 'Selisih (hari)'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.agenda LIKE '%putusan%' AND DATEDIFF(pjs.tanggal_sidang, p.tanggal_pendaftaran) > 60",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pjs.tanggal_sidang, DATEDIFF(pjs.tanggal_sidang, p.tanggal_pendaftaran) as selisih_hari FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.agenda LIKE '%putusan%' AND DATEDIFF(pjs.tanggal_sidang, p.tanggal_pendaftaran) > 60 ORDER BY selisih_hari DESC LIMIT :limit OFFSET :offset",
            ],
            'sinkron' => [
                'title' => 'Detail Sinkronisasi', 'desc' => 'Kirim Salinan > 70 Hari (Bobot -4%)', 'category' => 'Kesesuaian',
                'columns' => ['Tgl Putusan', 'Tgl Kirim Salinan', 'Selisih (hari)'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_banding_detil pbd ON p.perkara_id = pbd.perkara_id JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pbd.tanggal_kirim_salinan_putusan IS NOT NULL AND pp.tanggal_putusan IS NOT NULL AND DATEDIFF(pbd.tanggal_kirim_salinan_putusan, pp.tanggal_putusan) > 70",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pp.tanggal_putusan, pbd.tanggal_kirim_salinan_putusan, DATEDIFF(pbd.tanggal_kirim_salinan_putusan, pp.tanggal_putusan) as selisih_hari FROM perkara p JOIN perkara_banding_detil pbd ON p.perkara_id = pbd.perkara_id JOIN perkara_putusan pp ON p.perkara_id = pp.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pbd.tanggal_kirim_salinan_putusan IS NOT NULL AND pp.tanggal_putusan IS NOT NULL AND DATEDIFF(pbd.tanggal_kirim_salinan_putusan, pp.tanggal_putusan) > 70 ORDER BY selisih_hari DESC LIMIT :limit OFFSET :offset",
            ],
            'permintaan_delegasi' => [
                'title' => 'Detail Permintaan Delegasi', 'desc' => 'Perkara Banding (Bobot -2%)', 'category' => 'Kesesuaian',
                'columns' => ['Tgl Permohonan', 'Tgl Kirim Salinan'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_banding_detil pbd ON p.perkara_id = pbd.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pbd.permohonan_banding, pbd.tanggal_kirim_salinan_putusan FROM perkara p JOIN perkara_banding_detil pbd ON p.perkara_id = pbd.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir ORDER BY pbd.permohonan_banding DESC LIMIT :limit OFFSET :offset",
            ],
            'status_persidangan' => [
                'title' => 'Detail Status Persidangan', 'desc' => 'Sidang Ditunda (Bobot -2%)', 'category' => 'Kesesuaian',
                'columns' => ['Tgl Sidang', 'Agenda'],
                'query_count' => "SELECT COUNT(DISTINCT p.perkara_id) as total FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.ditunda = 1",
                'query_data'  => "SELECT DISTINCT p.perkara_id, p.nomor_perkara, p.tanggal_pendaftaran, p.jenis_perkara_nama, p.pihak1_text, p.pihak2_text, pjs.tanggal_sidang, pjs.agenda FROM perkara p JOIN perkara_jadwal_sidang pjs ON p.perkara_id = pjs.perkara_id WHERE p.tanggal_pendaftaran BETWEEN :awal AND :akhir AND pjs.ditunda = 1 ORDER BY pjs.tanggal_sidang DESC LIMIT :limit OFFSET :offset",
            ],
        ];

        // Type yang sama dengan pen_js
        foreach (['pen_js_2', 'pen_phs', 'input_phs'] as $alias) {
            $configs[$alias] = array_merge($configs['pen_js'], [
                'title' => match($alias) {
                    'pen_js_2'  => 'Detail Penetapan JS (2)',
                    'pen_phs'   => 'Detail Penetapan PHS',
                    'input_phs' => 'Detail Input PHS',
                },
            ]);
        }

        return $configs[$type] ?? null;
    }
}