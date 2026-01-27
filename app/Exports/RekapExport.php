<?php

namespace App\Exports;

use App\Models\RekapBackup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $departemens;

    public function __construct($departemens)
    {
        $this->departemens = $departemens;
    }

    public function collection()
    {
        return collect($this->departemens)->map(function ($dept) {
            return [
                'Departemen'     => $dept->nama_departemen,
                'Size Data (MB)' => $dept->size_data,
                'Size Email (MB)' => $dept->size_email,
                'Total Size (MB)' => $dept->total_size,
                'Status Backup' => ucfirst($dept->status_backup),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Departemen',
            'Size Data (MB)',
            'Size Email (MB)',
            'Total Size (MB)',
            'Status Backup'
        ];
    }
}
