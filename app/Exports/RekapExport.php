<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapExport implements 
        FromCollection, 
        WithHeadings, 
        WithStyles, 
        WithColumnWidths
{
    protected $rekap;        
    protected $departemens;  

    public function __construct($rekap, $departemens)
    {
        $this->rekap = $rekap;              
        $this->departemens = $departemens;  
    }

    public function collection()
    {
        $rows = collect();
        $no = 1;

        foreach ($this->rekap as $dept) {
            $rows->push([
                'No' => $no++,
                'Departemen' => $dept->nama_departemen,
                'DATA (MB)' => $dept->size_data_mb,
                'DATA (GB)' => $dept->size_data_gb,
                'EMAIL (MB)' => $dept->size_email_mb,
                'EMAIL (GB)' => $dept->size_email_gb,
                'Total Size Burning (MB)' => $dept->total_size_mb,
                'Total Size Burning (GB)' => $dept->total_size_gb,
                'Status Backup' => $dept->status_backup,
                'Status Data' => $dept->status_data,
            ]);
        }

        // baris total
        $rows->push([
            'No' => '',
            'Departemen' => 'TOTAL',
            'DATA (MB)' => $this->rekap->sum('size_data_mb'),
            'DATA (GB)' => round($this->rekap->sum('size_data_mb')/1024, 2),
            'EMAIL (MB)' => $this->rekap->sum('size_email_mb'),
            'EMAIL (GB)' => round($this->rekap->sum('size_email_mb')/1024, 2),
            'Total Size Burning (MB)' => $this->rekap->sum('total_size_mb'),
            'Total Size Burning (GB)' => round($this->rekap->sum('total_size_mb')/1024, 2),
            'Status Backup' => '',
            'Status Data' => '',
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Departemen',
            'DATA (MB)',
            'DATA (GB)',
            'EMAIL (MB)',
            'EMAIL (GB)',
            'Total Size (MB)',
            'Total Size (GB)',
            'Status Backup',
            'Status Data'
        ];
    }
      public function styles(Worksheet $sheet)
    {
        // Header bold + background
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'] // biru
            ],
            'alignment' => ['horizontal' => 'center']
        ]);

        // Border untuk semua sel
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 13,
            'J' => 22,
        ];
    }

}
