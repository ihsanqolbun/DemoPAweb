<?php

namespace App\Services;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapPerkaraExport;

class RekapPerkaraExportService
{
    public function __construct(
        private RekapPerkaraService $service
    ) {}

    /**
     * Export PDF
     */
    public function pdf(string $mulai, string $akhir, string $mode): \Symfony\Component\HttpFoundation\Response
    {
        $data     = $this->service->getData($mulai, $akhir);
        $periode  = $this->formatPeriode($mulai, $akhir, $mode);
        $filename = $this->generateFilename($mulai, $akhir, $mode, 'pdf');

        $pdf = Pdf::loadView('exports.rekap-perkara-pdf', array_merge($data, [
            'periode'      => $periode,
            'tanggalCetak' => RekapPerkaraService::formatTanggal(now()->toDateString()),
            'jamCetak'     => now()->format('H:i:s'),
        ]))->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    /**
     * Export Excel
     */
    public function excel(string $mulai, string $akhir, string $mode): \Symfony\Component\HttpFoundation\Response
    {
        $data     = $this->service->getData($mulai, $akhir);
        $periode  = $this->formatPeriode($mulai, $akhir, $mode);
        $filename = $this->generateFilename($mulai, $akhir, $mode, 'xlsx');

        return Excel::download(
            new RekapPerkaraExport($data, $periode),
            $filename
        );
    }

    /**
     * Format label periode sesuai mode
     */
    private function formatPeriode(string $mulai, string $akhir, string $mode): string
    {
        if ($mulai === $akhir) {
            return RekapPerkaraService::formatTanggal($mulai);
        }

        $jumlahHari = Carbon::parse($mulai)->diffInDays(Carbon::parse($akhir)) + 1;

        return RekapPerkaraService::formatTanggal($mulai)
            . ' - '
            . RekapPerkaraService::formatTanggal($akhir)
            . ' (' . $jumlahHari . ' hari)';
    }

    /**
     * Generate nama file export
     */
    private function generateFilename(string $mulai, string $akhir, string $mode, string $ext): string
    {
        $mulaiStr = str_replace('-', '', $mulai);
        $akhirStr = str_replace('-', '', $akhir);

        if ($mulai === $akhir) {
            return "Laporan_Rekap_Perkara_{$mulaiStr}.{$ext}";
        }

        return "Laporan_Rekap_Perkara_{$mulaiStr}_sd_{$akhirStr}.{$ext}";
    }
}