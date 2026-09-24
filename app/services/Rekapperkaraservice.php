<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RekapPerkaraService
{
    private static array $kategoriMapping = [
        'Cerai Gugat'                         => 'cerai_gugat',
        'Gugatan Cerai'                       => 'cerai_gugat',
        'Perceraian'                          => 'cerai_gugat',
        'Cerai Talak'                         => 'cerai_talak',
        'Talak'                               => 'cerai_talak',
        'Dispensasi Kawin'                    => 'dispensasi_kawin',
        'Dispensasi Nikah'                    => 'dispensasi_kawin',
        'Dispensasi'                          => 'dispensasi_kawin',
        'Izin Kawin'                          => 'dispensasi_kawin',
        'Asal Usul Anak'                      => 'asal_usul_anak',
        'Penetapan Asal Usul'                 => 'asal_usul_anak',
        'Asal-Usul'                           => 'asal_usul_anak',
        'P3HP'                                => 'p3hp',
        'P3PH'                                => 'p3hp',
        'Penetapan Ahli Waris'                => 'p3hp',
        'Ahli Waris'                          => 'p3hp',
        'Pengesahan Perkawinan'               => 'pengesahan_nikah',
        'Istbat Nikah'                        => 'pengesahan_nikah',
        'Pengesahan Nikah'                    => 'pengesahan_nikah',
        'Istbat'                              => 'pengesahan_nikah',
        'Isbat Nikah'                         => 'pengesahan_nikah',
        'Isbat'                               => 'pengesahan_nikah',
        'Wali Adhol'                          => 'perwalian',
        'Perwalian'                           => 'perwalian',
        'Penetapan Wali'                      => 'perwalian',
        'Penunjukkan orang lain sebagai Wali' => 'perwalian',
        'Pencabutan Kekuasaan Wali'           => 'perwalian',
        'Ganti Rugi Terhadap Wali'            => 'perwalian',
        'Harta Bersama'                       => 'harta_bersama',
        'Pembagian Harta Bersama'             => 'harta_bersama',
        'Izin Poligami'                       => 'izin_poligami',
        'Poligami'                            => 'izin_poligami',
        'Kewarisan'                           => 'kewarisan',
        'Sengketa Waris'                      => 'kewarisan',
        'Warisan'                             => 'kewarisan',
        'Wakaf'                               => 'wakaf',
        'Sengketa Wakaf'                      => 'wakaf',
        'Perwakafan'                          => 'wakaf',
        'Penguasaan Anak'                     => 'penguasaan_anak',
        'Hadhanah'                            => 'penguasaan_anak',
        'Hak Asuh Anak'                       => 'penguasaan_anak',
        'Hibah'                               => 'hibah',
        'Sengketa Hibah'                      => 'hibah',
        'Pembatalan Hibah'                    => 'hibah',
        'Pembatalan Perkawinan'               => 'pembatalan_perkawinan',
        'Pembatalan Nikah'                    => 'pembatalan_perkawinan',
        'Penolakan Perkawinan'                => 'pembatalan_perkawinan',
        'Pencegahan Perkawinan'               => 'pembatalan_perkawinan',
        'Ekonomi Syariah'                     => 'ekonomi_syariah',
        'Sengketa Ekonomi Syariah'            => 'ekonomi_syariah',
        "Ekonomi Syari'ah"                    => 'ekonomi_syariah',
        'Lain-Lain'                           => 'lain_lain',
        'Lainnya'                             => 'lain_lain',
        'Lain-lain'                           => 'lain_lain',
        'LAIN-LAIN'                           => 'lain_lain',
        'LAIN-LAIN *'                         => 'lain_lain',
    ];

    public static array $kategoriLabels = [
        'cerai_gugat'           => 'Cerai Gugat',
        'cerai_talak'           => 'Cerai Talak',
        'dispensasi_kawin'      => 'Dispensasi Kawin',
        'asal_usul_anak'        => 'Asal Usul Anak',
        'p3hp'                  => 'P3HP / Ahli Waris',
        'pengesahan_nikah'      => 'Isbat Nikah',
        'perwalian'             => 'Perwalian',
        'harta_bersama'         => 'Harta Bersama',
        'izin_poligami'         => 'Izin Poligami',
        'kewarisan'             => 'Kewarisan',
        'wakaf'                 => 'Wakaf',
        'penguasaan_anak'       => 'Hadhanah',
        'hibah'                 => 'Hibah',
        'pembatalan_perkawinan' => 'Pembatalan Perkawinan',
        'ekonomi_syariah'       => 'Ekonomi Syariah',
        'lain_lain'             => 'Lain-lain',
    ];

    private static array $sortableColumns = [
        'tanggal_pendaftaran' => 'p.tanggal_pendaftaran',
        'nomor_perkara'       => 'p.nomor_perkara',
        'jenis_perkara_nama'  => 'p.jenis_perkara_nama',
        'cara_daftar'         => 'cara_daftar',
        'status_perkara'      => 'status_perkara',
    ];

    // ================================================
    // getStats — untuk halaman index (aggregate only)
    // ================================================
    public function getStats(string $mulai, string $akhir): array
    {
        $stats = DB::selectOne("
            SELECT
                COUNT(DISTINCT p.perkara_id) as total_masuk,
                SUM(CASE WHEN pe.efiling_id IS NOT NULL AND pe.efiling_id != '' THEN 1 ELSE 0 END) as e_court,
                SUM(CASE WHEN pe.efiling_id IS NULL OR pe.efiling_id = '' THEN 1 ELSE 0 END) as manual,
                SUM(CASE WHEN pp.tanggal_minutasi IS NOT NULL AND pp.tanggal_minutasi != '' THEN 1 ELSE 0 END) as minutasi,
                SUM(CASE
                    WHEN (pp.tanggal_minutasi IS NULL OR pp.tanggal_minutasi = '')
                     AND pp.tanggal_putusan IS NOT NULL AND pp.tanggal_putusan != '' THEN 1
                    ELSE 0 END) as putus,
                SUM(CASE
                    WHEN (pp.tanggal_putusan  IS NULL OR pp.tanggal_putusan  = '')
                     AND (pp.tanggal_minutasi IS NULL OR pp.tanggal_minutasi = '') THEN 1
                    ELSE 0 END) as dalam_proses
            FROM perkara p
            LEFT JOIN perkara_efiling_id pe ON p.perkara_id = pe.perkara_id
            LEFT JOIN perkara_putusan pp    ON p.perkara_id = pp.perkara_id
            WHERE p.tanggal_pendaftaran BETWEEN ? AND ?
        ", [$mulai, $akhir]);

        $jenisRaw = DB::select("
            SELECT jenis_perkara_nama, COUNT(*) as jumlah
            FROM perkara
            WHERE tanggal_pendaftaran BETWEEN ? AND ?
              AND jenis_perkara_nama IS NOT NULL AND jenis_perkara_nama != ''
            GROUP BY jenis_perkara_nama
            ORDER BY jumlah DESC
        ", [$mulai, $akhir]);

        $kategoriCount = [];
        foreach ($jenisRaw as $row) {
            $slug = self::resolveKategori($row->jenis_perkara_nama);
            $kategoriCount[$slug] = ($kategoriCount[$slug] ?? 0) + (int) $row->jumlah;
        }
        arsort($kategoriCount);

        $jenisCards = [];
        foreach ($kategoriCount as $slug => $jumlah) {
            $jenisCards[] = [
                'slug'   => $slug,
                'label'  => self::$kategoriLabels[$slug] ?? $slug,
                'jumlah' => $jumlah,
            ];
        }

        $totalMasuk = (int) ($stats->total_masuk ?? 0);

        return [
            'totalMasuk'     => $totalMasuk,
            'eCourt'         => (int) ($stats->e_court ?? 0),
            'manual'         => (int) ($stats->manual ?? 0),
            'putus'          => (int) ($stats->putus ?? 0),
            'minutasi'       => (int) ($stats->minutasi ?? 0),
            'dalamProses'    => (int) ($stats->dalam_proses ?? 0),
            'jenisCards'     => $jenisCards,
            'jenisChartData' => array_slice($jenisCards, 0, 10),
            'jumlahHari'     => Carbon::parse($mulai)->diffInDays(Carbon::parse($akhir)) + 1,
        ];
    }

    // ================================================
    // getData — untuk export (kompatibel dengan ExportService)
    // Load semua row + pisah pdtg/pdtp
    // ================================================
    public function getData(string $mulai, string $akhir): array
    {
        $stats = $this->getStats($mulai, $akhir);

        // Load semua row untuk export (tanpa paginate)
        $rows = DB::select("
            SELECT
                p.perkara_id,
                p.nomor_perkara,
                p.jenis_perkara_nama,
                p.jenis_perkara_kode,
                p.tanggal_pendaftaran,
                p.pihak1_text,
                p.pihak2_text,
                pe.efiling_id,
                ph.hakim_nama,
                pp.tanggal_putusan,
                pp.tanggal_minutasi,
                CASE
                    WHEN pe.efiling_id IS NOT NULL AND pe.efiling_id != '' THEN 'e-court'
                    ELSE 'manual'
                END as cara_daftar,
                CASE
                    WHEN pp.tanggal_minutasi IS NOT NULL AND pp.tanggal_minutasi != '' THEN 'minutasi_selesai'
                    WHEN pp.tanggal_putusan  IS NOT NULL AND pp.tanggal_putusan  != '' THEN 'putus'
                    ELSE 'dalam_proses'
                END as status_perkara
            FROM perkara p
            LEFT JOIN perkara_efiling_id pe ON p.perkara_id = pe.perkara_id
            LEFT JOIN perkara_putusan pp    ON p.perkara_id = pp.perkara_id
            LEFT JOIN perkara_hakim_pn ph   ON p.perkara_id = ph.perkara_id
                AND ph.urutan = '1' AND ph.aktif = 'Y'
            WHERE p.tanggal_pendaftaran BETWEEN ? AND ?
            ORDER BY p.jenis_perkara_nama, p.tanggal_pendaftaran DESC, p.nomor_perkara
        ", [$mulai, $akhir]);

        // Pisah Pdt.G dan Pdt.P
        $pdtg = [];
        $pdtp = [];
        foreach ($rows as $row) {
            $kode      = $row->jenis_perkara_kode ?? '';
            $namaLower = strtolower($row->jenis_perkara_nama ?? '');
            if (
                str_contains($kode, 'Pdt.G') ||
                str_contains($row->nomor_perkara ?? '', 'Pdt.G') ||
                str_contains($namaLower, 'cerai gugat') ||
                str_contains($namaLower, 'gugatan')
            ) {
                $pdtg[] = $row;
            } else {
                $pdtp[] = $row;
            }
        }

        return array_merge($stats, [
            'pdtg'      => $pdtg,
            'pdtp'      => $pdtp,
            'countPdtg' => count($pdtg),
            'countPdtp' => count($pdtp),
            'data'      => collect($rows),
        ]);
    }

    // ================================================
    // getTableData — paginate untuk index
    // ================================================
    public function getTableData(
        string $mulai, string $akhir,
        string $search = '', int $perPage = 10,
        string $sortBy = 'tanggal_pendaftaran', string $sortDir = 'desc'
    ) {
        $query = $this->buildBaseQuery($mulai, $akhir);
        if ($search) {
            $like = '%' . $search . '%';
            $query->where(fn($q) => $q
                ->where('p.nomor_perkara', 'LIKE', $like)
                ->orWhere('p.pihak1_text',  'LIKE', $like)
                ->orWhere('p.pihak2_text',  'LIKE', $like)
                ->orWhere('pe.efiling_id',  'LIKE', $like));
        }
        $this->applySort($query, $sortBy, $sortDir);
        return $query->paginate($perPage)->withQueryString();
    }

    // ================================================
    // getDetailData — paginate untuk detail
    // ================================================
    public function getDetailData(
        string $mulai, string $akhir,
        string $kategori = 'semua', string $search = '',
        int $perPage = 10,
        string $sortBy = 'tanggal_pendaftaran', string $sortDir = 'desc'
    ) {
        $query = $this->buildBaseQuery($mulai, $akhir);
        $this->applyKategoriFilter($query, $kategori);
        if ($search) {
            $like = '%' . $search . '%';
            $query->where(fn($q) => $q
                ->where('p.nomor_perkara', 'LIKE', $like)
                ->orWhere('p.pihak1_text',  'LIKE', $like)
                ->orWhere('p.pihak2_text',  'LIKE', $like)
                ->orWhere('pe.efiling_id',  'LIKE', $like));
        }
        $this->applySort($query, $sortBy, $sortDir);
        return $query->paginate($perPage)->withQueryString();
    }

    // ================================================
    // Apply sort
    // ================================================
    private function applySort($query, string $sortBy, string $sortDir): void
    {
        $col = self::$sortableColumns[$sortBy] ?? 'p.tanggal_pendaftaran';
        $dir = $sortDir === 'asc' ? 'asc' : 'desc';
        if (in_array($sortBy, ['cara_daftar', 'status_perkara'])) {
            $query->orderByRaw("$col $dir");
        } else {
            $query->orderBy($col, $dir);
        }
        if ($sortBy !== 'nomor_perkara') {
            $query->orderBy('p.nomor_perkara', 'asc');
        }
    }

    // ================================================
    // Base query
    // ================================================
    private function buildBaseQuery(string $mulai, string $akhir)
    {
        return DB::table('perkara as p')
            ->select([
                'p.perkara_id', 'p.nomor_perkara', 'p.jenis_perkara_nama',
                'p.jenis_perkara_kode', 'p.tanggal_pendaftaran',
                'p.pihak1_text', 'p.pihak2_text', 'pe.efiling_id',
                'ph.hakim_nama', 'pp.tanggal_putusan', 'pp.tanggal_minutasi',
                DB::raw("CASE WHEN pe.efiling_id IS NOT NULL AND pe.efiling_id != '' THEN 'e-court' ELSE 'manual' END as cara_daftar"),
                DB::raw("CASE
                    WHEN pp.tanggal_minutasi IS NOT NULL AND pp.tanggal_minutasi != '' THEN 'minutasi_selesai'
                    WHEN pp.tanggal_putusan  IS NOT NULL AND pp.tanggal_putusan  != '' THEN 'putus'
                    ELSE 'dalam_proses' END as status_perkara"),
            ])
            ->leftJoin('perkara_efiling_id as pe', 'p.perkara_id', '=', 'pe.perkara_id')
            ->leftJoin('perkara_putusan as pp',     'p.perkara_id', '=', 'pp.perkara_id')
            ->leftJoin('perkara_hakim_pn as ph', function ($j) {
                $j->on('p.perkara_id', '=', 'ph.perkara_id')
                  ->where('ph.urutan', '=', '1')->where('ph.aktif', '=', 'Y');
            })
            ->whereBetween('p.tanggal_pendaftaran', [$mulai, $akhir]);
    }

    // ================================================
    // Filter kategori
    // ================================================
    private function applyKategoriFilter($query, string $kategori): void
    {
        match ($kategori) {
            'gugatan'    => $query->where('p.nomor_perkara', 'LIKE', '%Pdt.G%'),
            'permohonan' => $query->where('p.nomor_perkara', 'LIKE', '%Pdt.P%'),
            'ecourt'     => $query->whereNotNull('pe.efiling_id')->where('pe.efiling_id', '!=', ''),
            'manual'     => $query->where(fn($q) => $q->whereNull('pe.efiling_id')->orWhere('pe.efiling_id', '')),
            'putus'      => $query->whereNotNull('pp.tanggal_putusan')->where('pp.tanggal_putusan', '!=', '')
                                  ->where(fn($q) => $q->whereNull('pp.tanggal_minutasi')->orWhere('pp.tanggal_minutasi', '')),
            'minutasi'     => $query->whereNotNull('pp.tanggal_minutasi')->where('pp.tanggal_minutasi', '!=', ''),
            'dalam_proses' => $query
                ->where(fn($q) => $q->whereNull('pp.tanggal_putusan')->orWhere('pp.tanggal_putusan', ''))
                ->where(fn($q) => $q->whereNull('pp.tanggal_minutasi')->orWhere('pp.tanggal_minutasi', '')),
            'cerai_gugat'  => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Cerai Gugat%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Gugatan Cerai%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Perceraian%')),
            'cerai_talak' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Cerai Talak%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Talak%')),
            'dispensasi_kawin' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Dispensasi Kawin%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Dispensasi Nikah%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Dispensasi%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Izin Kawin%')),
            'asal_usul_anak' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Asal Usul Anak%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Penetapan Asal Usul%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Asal-Usul%')),
            'p3hp' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%P3HP%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%P3PH%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Penetapan Ahli Waris%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Ahli Waris%')),
            'pengesahan_nikah' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Pengesahan Perkawinan%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Istbat Nikah%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Pengesahan Nikah%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Isbat Nikah%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Isbat%')),
            'perwalian' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Wali Adhol%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Perwalian%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Penetapan Wali%')),
            'harta_bersama' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Harta Bersama%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Pembagian Harta Bersama%')),
            'izin_poligami' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Izin Poligami%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Poligami%')),
            'kewarisan' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Kewarisan%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Sengketa Waris%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Warisan%')),
            'wakaf' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Wakaf%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Perwakafan%')),
            'penguasaan_anak' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Penguasaan Anak%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Hadhanah%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Hak Asuh Anak%')),
            'hibah' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Hibah%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Sengketa Hibah%')),
            'pembatalan_perkawinan' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Pembatalan Perkawinan%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Pembatalan Nikah%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Penolakan Perkawinan%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Pencegahan Perkawinan%')),
            'ekonomi_syariah' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Ekonomi Syariah%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Ekonomi Syari%')),
            'lain_lain' => $query->where(fn($q) => $q
                ->where('p.jenis_perkara_nama', 'LIKE', '%Lain-Lain%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Lainnya%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%Lain-lain%')
                ->orWhere('p.jenis_perkara_nama', 'LIKE', '%LAIN-LAIN%')),
            default => null,
        };
    }

    public static function resolveKategori(string $nama): string
    {
        foreach (self::$kategoriMapping as $keyword => $slug) {
            if (strcasecmp($nama, $keyword) === 0) return $slug;
        }
        foreach (self::$kategoriMapping as $keyword => $slug) {
            if (stripos($nama, $keyword) !== false) return $slug;
        }
        return 'lain_lain';
    }

    public static function formatTanggal(?string $tanggal): string
    {
        if (!$tanggal || $tanggal === '') return '-';
        return Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM Y');
    }

    public static function formatStatus(string $status): string
    {
        return match ($status) {
            'minutasi_selesai' => 'Minutasi Selesai',
            'putus'            => 'Putus',
            default            => 'Dalam Proses',
        };
    }
}