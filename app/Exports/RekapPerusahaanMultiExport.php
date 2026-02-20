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
        $sheets[] = new RekapPerusahaanExport($this->globalPivot, $this->namaPerusahaan);
        foreach ($this->detailPeriodes as $periode => $data) {
            $sheets[] = new RekapPeriodeExport($data, $periode, $this->namaPerusahaan);
        }

        return $sheets;
    }
}
