<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Traits\RekapBAckupTrait;

class RekapPerusahaanMultiExport implements WithMultipleSheets
{
    use RekapBackupTrait;

    protected $globalPivot;
    protected $detailPeriodes;
    protected $namaPerusahaan;
    protected $perusahaanId;

    public function __construct(array $globalPivot, array $detailPeriodes, string $namaPerusahaan, int $perusahaanId)
    {
        $this->globalPivot    = $globalPivot;
        $this->detailPeriodes = $detailPeriodes;
        $this->namaPerusahaan = $namaPerusahaan;
        $this->perusahaanId   = $perusahaanId;
    }

    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new RekapPerusahaanExport($this->globalPivot, $this->namaPerusahaan);

        foreach ($this->detailPeriodes as $periode => $rekapDetail) {
            $rekapDepartemen = $this->getDepartemenQuery(
                $this->perusahaanId,
                \Carbon\Carbon::createFromFormat('M-Y', $periode)->format('Y-m-01')
            )->get();

            $sheets[] = new RekapExport($rekapDepartemen, $rekapDetail, $this->namaPerusahaan, $periode);
        }

        return $sheets;
    }

}

