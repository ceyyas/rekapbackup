<?php

namespace App\Exports;

use App\Models\Departemen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapExport implements FromCollection, WithHeadings
{
    protected $departemens;

    public function __construct($departemens)
    {
        $this->departemens = $departemens;
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->departemens as $dept) {
            // baris ringkasan departemen
            $rows->push([
                'Departemen'     => $dept->nama_departemen,
                'Komputer/Laptop'=> '-', // kosong untuk ringkasan
                'Size Data (MB)' => $dept->size_data,
                'Size Email (MB)'=> $dept->size_email,
                'Total Size (MB)'=> $dept->total_size,
                'Status Backup'  => ucfirst($dept->status_backup),
            ]);

            // detail per komputer/laptop
            foreach ($dept->inventoris as $inv) {
                $rows->push([
                    'Departemen'     => $dept->nama_departemen,
                    'Komputer/Laptop'=> $inv->nama_inventori,
                    'Size Data (MB)' => $inv->size_data,
                    'Size Email (MB)'=> $inv->size_email,
                    'Total Size (MB)'=> $inv->total_size,
                    'Status Backup'  => ucfirst($inv->status_backup),
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Departemen',
            'Komputer/Laptop',
            'Size Data (MB)',
            'Size Email (MB)',
            'Total Size (MB)',
            'Status Backup'
        ];
    }
}
