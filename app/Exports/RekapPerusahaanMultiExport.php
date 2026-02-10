<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapPerusahaanMultiExport implements WithMultipleSheets
{
    protected $globalPivot;
    protected $detailPeriodes;
    protected $namaPerusahaan;

    public function __construct(array $globalPivot, array $detailPeriodes, string $namaPerusahaan)
    {
        $this->globalPivot    = $globalPivot;
        $this->detailPeriodes = $detailPeriodes;
        $this->namaPerusahaan = $namaPerusahaan;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Global pivot
        $sheets[] = new RekapPerusahaanExport($this->globalPivot, $this->namaPerusahaan);

        // Sheet 2,3,...: Detail per periode
        foreach ($this->detailPeriodes as $periode => $data) {
            $sheets[] = new RekapPeriodeExport($data, $periode, $this->namaPerusahaan);
        }

        return $sheets;
    }
}
