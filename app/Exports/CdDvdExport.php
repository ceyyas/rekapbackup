<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Carbon\Carbon;

class CdDvdExport implements 
    FromCollection, 
    WithHeadings,
    WithEvents, 
    WithCustomStartCell,
    WithColumnWidths,
    WithStyles
{
    protected $rekap;

    public function __construct($rekap)
    {
        $this->rekap = $rekap;
    }

    public function startCell(): string { 
        return 'A2'; 
    }

    public function collection()
    {
        $rows = collect();
        $no = 1;

        foreach ($this->rekap as $dept) {
            $rows->push([
                'No' => $no++,
                'Departemen' => $dept->nama_departemen,
                'Size Data GB' => round($dept->size_data / 1024, 2) . ' GB',
                'Size Email GB' => round($dept->size_email / 1024, 2) . ' GB',
                'Total Size GB' => round($dept->total_size / 1024, 2) . ' GB',
                'CD 700 MB' => $dept->total_cd700,
                'DVD 4.7 GB' => $dept->total_dvd47,
                'DVD 8.5 GB' => $dept->total_dvd85,
            ]);
        }

        // total keseluruhan
        $rows->push([
            'No' => '',
            'Departemen' => 'TOTAL',
            'Size Data GB' => round($this->rekap->sum('size_data')/1024, 2) . ' GB',
            'Size Email GB' => round($this->rekap->sum('size_email')/1024, 2) . ' GB',
            'Total Size GB' => round($this->rekap->sum('total_size')/1024, 2) . ' GB',
            'CD 700 MB' => $this->rekap->sum('total_cd700'),
            'DVD 4.7 GB' => $this->rekap->sum('total_dvd47'),
            'DVD 8.5 GB' => $this->rekap->sum('total_dvd85'),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Departemen',
            'Size Data',
            'Size Email',
            'Total Size',
            'CD 700 MB',
            'DVD 4.7 GB',
            'DVD 8.5 GB'
        ];
    }

    public function registerEvents(): array    
    {
         return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                for ($row = 1; $row <= $highestRow; $row++) {
                    $value = $sheet->getCell("A{$row}")->getValue();
                    $colB  = $sheet->getCell("B{$row}")->getValue();

                    if (!empty($value) && empty($colB)) {
                        $sheet->mergeCells("A{$row}:H{$row}");
                        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '2a6099'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                    }
                }

                // judul laporan di baris 1
                $periodeFormat = \Carbon\Carbon::parse($this->periode)->translatedFormat('F Y');
                $judul = "Laporan Rekap Backup Data - {$this->perusahaan} - Periode {$periodeFormat}";                
                $event->sheet->setCellValue('A1', $judul);
                $event->sheet->mergeCells('A1:H1');          
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'], 
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4E8BC9'], 
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 10,
            'G' => 10,
            'H' => 10,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header bold 
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID
            ],
            'alignment' => [ 'horizontal' => Alignment::HORIZONTAL_CENTER, 
            'vertical' => Alignment::VERTICAL_CENTER, ]
        ]);

        // Border untuk semua sel
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A2:{$highestColumn}{$highestRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);

        $sheet->getStyle("A3:A{$highestRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("C3:H{$highestRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        return [];
    }

}
