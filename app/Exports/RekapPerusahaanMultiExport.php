<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapPerusahaanMultiExport implements WithMultipleSheets
{
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

        foreach ($this->detailPeriodes as $periode => $data) {
            $rekapDepartemen = $this->transformDepartemen($data, $this->perusahaanId);
            $rekapDetail     = $this->transformDetail($data);

            $sheets[] = new RekapExport($rekapDepartemen, $rekapDetail, $this->namaPerusahaan, $periode);
        }

        return $sheets;
    }

    protected function transformDepartemen(array $data)
    {
        $grouped = collect($data)->groupBy('departemen');

        return $grouped->map(function($items, $dept) {
            $sizeData  = collect($items)->sum('size_data');
            $sizeEmail = collect($items)->sum('size_email');
            return (object)[
                'nama_departemen' => $dept,
                'size_data'       => $sizeData,
                'size_email'      => $sizeEmail,
                'total_size'      => $sizeData + $sizeEmail,
                'status_backup'   => '',
                'status_data'     => '',
            ];
        });
    }

    protected function transformDetail(array $data)
    {
        return collect($data)->groupBy('departemen')->map(function($items, $dept) {
            return (object)[
                'nama_departemen' => $dept,
                'detail_inventori'=> collect($items)->map(function($row) {
                    return (object)[
                        'hostname'   => $row['hostname'],
                        'username'   => $row['username'],
                        'email'      => $row['email'],
                        'size_data'  => $row['size_data'],
                        'size_email' => $row['size_email'],
                        'total_size' => $row['size_data'] + $row['size_email'],
                    ];
                }),
            ];
        });
    }
}

