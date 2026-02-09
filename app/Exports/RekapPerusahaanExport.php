<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapPerusahaanExport implements FromCollection
{
    protected $rekap;

    public function __construct($rekap)
    {
        $this->rekap = $rekap;
    }

    public function collection()
    {
        $rows = [];

        // header
        $header = ['Departemen'];
        if (!empty($this->rekap['periodes'])) {
            $header = array_merge($header, $this->rekap['periodes']);
        }
        $rows[] = $header;

        // isi data
        foreach ($this->rekap['pivot'] as $dept => $data) {
            $row = [$dept];
            foreach ($this->rekap['periodes'] as $p) {
                $row[] = $data[$p] ?? '-';
            }
            $rows[] = $row;
        }

        return new Collection($rows);
    }
}

