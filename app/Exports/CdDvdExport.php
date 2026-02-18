<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class CdDvdExport implements 
    FromCollection, 
    WithHeadings,
    WithEvents, 
    WithCustomStartCell,
    WithColumnWidths,
    WithStyles
{
    protected $periode;
    protected $perusahaan;
    protected $rekap;

    public function __construct($periode, $perusahaan, $rekap)
    {
        $this->periode = $periode;
        $this->perusahaan = $perusahaan; 
        $this->rekap = $rekap;
    }

    public function collection()
    {
        $rows = collect();
        $no = 1;

        foreach ($this->rekap as $dept) {
            $rows->push([
                'No' => $no++,
                'Departemen' => $dept->nama_departemen,
                'Size Data MB' => $dept->size_data . ' MB',
                'Size Email MB' => $dept->size_email . ' MB',
                'Total Size MB' => $dept->total_size . ' MB',
                'CD 700 MB' => $dept->total_cd700,
                'DVD 4.7 GB' => $dept->total_dvd47,
                'DVD 8.5 GB' => $dept->total_dvd85,
            ]);
        }

        // total keseluruhan
        $rows->push([
            '',
            'TOTAL',
            round($this->rekap->sum('size_data')) . ' MB',
            round($this->rekap->sum('size_email')) . ' MB',
            round($this->rekap->sum('total_size')) . ' MB',
            $this->rekap->sum('total_cd700'),
            $this->rekap->sum('total_dvd47'),
            $this->rekap->sum('total_dvd85'),
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

    public function startCell(): string { 
        return 'A2'; 
    }

    public function registerEvents(): array    
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $periodeFormat = \Carbon\Carbon::parse($this->periode)->translatedFormat('F Y');
                $judul = "Laporan Penggunaan CD DVD - {$this->perusahaan} - Periode {$periodeFormat}";                
                
                $sheet->setCellValue('A1', $judul);
                $sheet->mergeCells('A1:H1');          
                $sheet->getStyle('A1:H1')->applyFromArray([
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
