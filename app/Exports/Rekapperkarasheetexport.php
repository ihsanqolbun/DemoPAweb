<?php

namespace App\Exports;

use App\Services\RekapPerkaraService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapPerkaraSheetExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        private array  $rows,
        private string $kategori,
        private string $periode,
        private int    $total
    ) {}

    public function title(): string
    {
        return $this->kategori;
    }

    public function array(): array
    {
        $data = [];

        // Header info
        $data[] = ['LAPORAN PERKARA ' . strtoupper($this->kategori)];
        $data[] = ['Periode: ' . $this->periode];
        $data[] = ['Dicetak: ' . now()->locale('id')->isoFormat('D MMMM Y') . ' ' . now()->format('H:i:s')];
        $data[] = ['Total: ' . $this->total . ' perkara'];
        $data[] = [];

        // Header tabel
        $data[] = [
            'No',
            'Tanggal',
            'No. Perkara',
            'Jenis Perkara',
            'Cara Daftar',
            'Pihak 1',
            'Pihak 2',
            'Hakim',
            'E-Filing ID',
            'Status',
            'Tgl Putusan',
            'Tgl Minutasi',
        ];

        // Baris data
        $no = 1;
        foreach ($this->rows as $row) {
            $data[] = [
                $no++,
                $row->tanggal_pendaftaran
                    ? Carbon::parse($row->tanggal_pendaftaran)->format('d/m/Y')
                    : '-',
                $row->nomor_perkara    ?? '-',
                $row->jenis_perkara_nama ?? '-',
                $row->cara_daftar === 'e-court' ? 'E-Court' : 'Manual',
                $row->pihak1_text      ?? '-',
                $row->pihak2_text      ?? '-',
                $row->hakim_nama       ?? '-',
                $row->efiling_id       ?? '-',
                RekapPerkaraService::formatStatus($row->status_perkara ?? 'dalam_proses'),
                $row->tanggal_putusan
                    ? Carbon::parse($row->tanggal_putusan)->format('d/m/Y')
                    : '-',
                $row->tanggal_minutasi
                    ? Carbon::parse($row->tanggal_minutasi)->format('d/m/Y')
                    : '-',
            ];
        }

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 14,
            'C' => 28,
            'D' => 30,
            'E' => 12,
            'F' => 35,
            'G' => 35,
            'H' => 30,
            'I' => 18,
            'J' => 18,
            'K' => 14,
            'L' => 14,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->rows) + 7; // 5 baris header info + 1 header tabel + data

        // Judul bold
        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');
        $sheet->mergeCells('A4:L4');

        // Style header tabel (baris 6)
        $sheet->getStyle('A6:L6')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0a7a5e'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Style data
        if ($lastRow >= 7) {
            $sheet->getStyle("A7:L{$lastRow}")->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']],
                ],
            ]);
        }

        // Style judul
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
        ]);
        $sheet->getStyle('A2:A4')->applyFromArray([
            'font' => ['size' => 10],
        ]);

        return [];
    }
}