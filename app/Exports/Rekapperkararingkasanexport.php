<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapPerkaraRingkasanExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        private array  $data,
        private string $periode
    ) {}

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function array(): array
    {
        $d = $this->data;

        $rows = [];
        $rows[] = ['RINGKASAN LAPORAN REKAPITULASI PERKARA'];
        $rows[] = ['Periode: ' . $this->periode];
        $rows[] = ['Dicetak: ' . now()->locale('id')->isoFormat('D MMMM Y') . ' ' . now()->format('H:i:s')];
        $rows[] = [];

        // Header
        $rows[] = ['No', 'Kategori', 'Jumlah'];

        // Jenis Perkara
        $rows[] = ['', 'JENIS PERKARA', ''];
        $rows[] = [1, 'Gugatan (Pdt.G)', $d['countPdtg']];
        $rows[] = [2, 'Permohonan (Pdt.P)', $d['countPdtp']];
        $rows[] = ['', 'Total Perkara Masuk', $d['totalMasuk']];
        $rows[] = [];

        // Cara Daftar
        $rows[] = ['', 'CARA PENDAFTARAN', ''];
        $rows[] = [1, 'E-Court', $d['eCourt']];
        $rows[] = [2, 'Manual', $d['manual']];
        $rows[] = [];

        // Status
        $rows[] = ['', 'STATUS PERKARA', ''];
        $rows[] = [1, 'Dalam Proses', $d['dalamProses']];
        $rows[] = [2, 'Putus', $d['putus']];
        $rows[] = [3, 'Minutasi Selesai', $d['minutasi']];
        $rows[] = [];

        $rows[] = ['', 'TOTAL SELURUH PERKARA', $d['totalMasuk']];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 40,
            'C' => 15,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Merge judul
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('A3:C3');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
        ]);

        // Header tabel baris 5
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0a7a5e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Sub-header sections
        foreach ([6, 11, 14] as $row) {
            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '444444']],
            ]);
        }

        // Total rows style
        foreach ([9, 20] as $row) {
            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5F0']],
            ]);
        }

        return [];
    }
}