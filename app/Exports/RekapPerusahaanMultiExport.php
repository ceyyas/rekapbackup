<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Departemen;
use App\Models\Inventori;

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
            $rekapDetail     = $this->transformDetail($data, $this->perusahaanId);

            $sheets[] = new RekapExport($rekapDepartemen, $rekapDetail, $this->namaPerusahaan, $periode);
        }

        return $sheets;
    }

    protected function transformDepartemen(array $data, $perusahaanId)
    {
        $allDept = Departemen::where('perusahaan_id', $perusahaanId)->get();
        $grouped = collect($data)->groupBy('departemen');

        return $allDept->map(function($dept) use ($grouped) {
            $items = $grouped->get($dept->nama_departemen, collect());

            $sizeData   = $items->sum('size_data');
            $sizeEmail  = $items->sum('size_email');
            $jumlah_cd700   = $items->sum('jumlah_cd700');
            $jumlah_dvd47 = $items->sum('jumlah_dvd47');
            $jumlah_dvd85 = $items->sum('jumlah_dvd85');
            $total_cd_dvd = $jumlah_cd700 + $jumlah_dvd47 + $jumlah_dvd85;

            return (object)[
                'nama_departemen' => $dept->nama_departemen,
                'size_data'       => $sizeData,
                'size_email'      => $sizeEmail,
                'total_size'      => $sizeData + $sizeEmail,
                'jumlah_cd700'    => $jumlah_cd700,
                'jumlah_dvd47'    => $jumlah_dvd47,
                'jumlah_dvd85'    => $jumlah_dvd85,
                'total_cd_dvd'    => $total_cd_dvd,
                'status_backup'   => $items->first()['status_backup'] ?? '',
                'status_data'     => $items->first()['status_data'] ?? '',
            ];
        });
    }


    protected function transformDetail(array $data, $perusahaanId)
    {
        $allDept = Departemen::where('perusahaan_id', $perusahaanId)->get();
        $grouped = collect($data)->groupBy('departemen');

        return $allDept->map(function($dept) use ($grouped) {
            $items = $grouped->get($dept->nama_departemen, collect());

            $inventories = Inventori::where('departemen_id', $dept->id)->get();

            $detailInventori = $inventories->map(function($inv) use ($items) {
                $row = $items->firstWhere('hostname', $inv->hostname);
                return (object)[
                    'hostname'   => $inv->hostname,
                    'username'   => $row['username'] ?? $inv->username ?? '-',
                    'email'      => $row['email'] ?? $inv->email ?? '-',
                    'size_data'  => $row['size_data'] ?? 0,
                    'size_email' => $row['size_email'] ?? 0,
                    'total_size' => ($row['size_data'] ?? 0) + ($row['size_email'] ?? 0),
                ];
            });

            return (object)[
                'nama_departemen' => $dept->nama_departemen,
                'detail_inventori'=> $detailInventori,
            ];
        });
    }
}

