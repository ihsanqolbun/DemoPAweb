<?php

namespace App\Exports;

use App\Services\RekapPerkaraService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapPerkaraExport implements WithMultipleSheets
{
    public function __construct(
        private array  $data,
        private string $periode
    ) {}

    public function sheets(): array
    {
        $sheets = [];

        if (!empty($this->data['pdtg'])) {
            $sheets[] = new RekapPerkaraSheetExport(
                $this->data['pdtg'],
                'Pdt.G (Gugatan)',
                $this->periode,
                count($this->data['pdtg'])
            );
        }

        if (!empty($this->data['pdtp'])) {
            $sheets[] = new RekapPerkaraSheetExport(
                $this->data['pdtp'],
                'Pdt.P (Permohonan)',
                $this->periode,
                count($this->data['pdtp'])
            );
        }

        $sheets[] = new RekapPerkaraRingkasanExport($this->data, $this->periode);

        return $sheets;
    }
}