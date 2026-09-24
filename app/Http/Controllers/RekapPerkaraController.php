<?php

namespace App\Http\Controllers;

use App\Services\RekapPerkaraService;
use App\Services\RekapPerkaraExportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekapPerkaraController extends Controller
{
    public function __construct(
        private RekapPerkaraService       $service,
        private RekapPerkaraExportService $exportService,
    ) {}

    private function parseTanggal(Request $request): array
    {
        $mode  = $request->get('mode', 'range');
        $today = today()->toDateString();

        return match ($mode) {
            'hari' => (function () use ($request, $today) {
                $t = $request->get('tanggal', $today);
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $t)) $t = $today;
                return [$t, $t, 'hari', $t, null];
            })(),
            'bulan' => (function () use ($request) {
                $b     = $request->get('bulan', now()->format('Y-m'));
                $start = Carbon::parse($b . '-01')->startOfMonth()->toDateString();
                $end   = Carbon::parse($b . '-01')->endOfMonth()->toDateString();
                return [$start, $end, 'bulan', null, $b];
            })(),
            default => (function () use ($request, $today) {
                $mulai = $request->get('tanggal_mulai', Carbon::now()->startOfMonth()->toDateString());
                $akhir = $request->get('tanggal_akhir', $today);
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $mulai)) $mulai = $today;
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $akhir))  $akhir = $today;
                if ($mulai > $akhir) [$mulai, $akhir] = [$akhir, $mulai];
                return [$mulai, $akhir, 'range', null, null];
            })(),
        };
    }

    public function index(Request $request)
    {
        [$mulai, $akhir, $mode, $tanggal, $bulan] = $this->parseTanggal($request);

        $search  = $request->get('search', '');
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50]) ? (int)$request->get('per_page') : 10;
        $sortBy  = in_array($request->get('sort_by'), ['tanggal_pendaftaran', 'nomor_perkara', 'jenis_perkara_nama', 'cara_daftar', 'status_perkara']) ? $request->get('sort_by') : 'tanggal_pendaftaran';
        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';

        $stats = $this->service->getStats($mulai, $akhir);
        $rows  = $this->service->getTableData($mulai, $akhir, $search, $perPage, $sortBy, $sortDir);

        return view('rekap-perkara.index', array_merge($stats, [
            'tanggalMulai' => $mulai,
            'tanggalAkhir' => $akhir,
            'mode'         => $mode,
            'tanggal'      => $tanggal,
            'bulan'        => $bulan,
            'search'       => $search,
            'rows'         => $rows,
            'perPage'      => $perPage,
            'sortBy'       => $sortBy,
            'sortDir'      => $sortDir,
        ]));
    }

    public function detail(Request $request)
    {
        $today    = today()->toDateString();
        $mulai    = $request->get('tanggal_mulai', $today);
        $akhir    = $request->get('tanggal_akhir', $today);
        $kategori = $request->get('kategori', 'semua');
        $search   = $request->get('search', '');
        $perPage  = in_array((int)$request->get('per_page'), [10, 25, 50]) ? (int)$request->get('per_page') : 10;
        $sortBy   = in_array($request->get('sort_by'), ['tanggal_pendaftaran', 'nomor_perkara', 'jenis_perkara_nama', 'cara_daftar', 'status_perkara']) ? $request->get('sort_by') : 'tanggal_pendaftaran';
        $sortDir  = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $mulai)) $mulai = $today;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $akhir))  $akhir = $today;
        if ($mulai > $akhir) [$mulai, $akhir] = [$akhir, $mulai];

        $kategoriLabels = [
            'semua'                 => 'Semua Perkara',
            'gugatan'               => 'Gugatan (Pdt.G)',
            'permohonan'            => 'Permohonan (Pdt.P)',
            'ecourt'                => 'E-Court',
            'manual'                => 'Manual',
            'putus'                 => 'Putus',
            'minutasi'              => 'Minutasi Selesai',
            'dalam_proses'          => 'Dalam Proses',
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
            'penguasaan_anak'       => 'Penguasaan Anak',
            'hibah'                 => 'Hibah',
            'pembatalan_perkawinan' => 'Pembatalan Perkawinan',
            'ekonomi_syariah'       => 'Ekonomi Syariah',
            'lain_lain'             => 'Lain-lain',
        ];

        $rows       = $this->service->getDetailData($mulai, $akhir, $kategori, $search, $perPage, $sortBy, $sortDir);
        $jumlahHari = Carbon::parse($mulai)->diffInDays(Carbon::parse($akhir)) + 1;

        return view('rekap-perkara.detail', [
            'rows'          => $rows,
            'totalData'     => $rows->total(),
            'kategori'      => $kategori,
            'judulKategori' => $kategoriLabels[$kategori] ?? 'Detail Perkara',
            'tanggalMulai'  => $mulai,
            'tanggalAkhir'  => $akhir,
            'jumlahHari'    => $jumlahHari,
            'search'        => $search,
            'perPage'       => $perPage,
            'sortBy'        => $sortBy,
            'sortDir'       => $sortDir,
        ]);
    }

    public function exportPdf(Request $request)
    {
        [$mulai, $akhir, $mode] = $this->parseTanggal($request);
        return $this->exportService->pdf($mulai, $akhir, $mode);
    }

    public function exportExcel(Request $request)
    {
        [$mulai, $akhir, $mode] = $this->parseTanggal($request);
        return $this->exportService->excel($mulai, $akhir, $mode);
    }
}