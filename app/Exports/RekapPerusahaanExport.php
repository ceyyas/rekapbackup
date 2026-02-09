<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class RekapPerusahaanExport implements 
    FromCollection, 
    WithStyles, 
    WithColumnWidths, 
    WithEvents, 
    WithCustomStartCell
{
    protected $rekap;
    protected $perusahaan;
    protected $headingRows = [];

    public function __construct($rekap, $perusahaan)
    {
        $this->rekap = $rekap;
        $this->perusahaan = $perusahaan; 
    }

    public function startCell(): string { 
        return 'A2'; 
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

    public function styles(Worksheet $sheet)
    {
        // Header bold + background
        $sheet->getStyle('A2:F2')->applyFromArray([
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

        $sheet->getStyle("B3:N{$highestRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 12,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 12,
        ];
    }

    public function registerEvents(): array    
    {    
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach ($this->headingRows as $rowIndex) {
                    $sheet->mergeCells("A{$rowIndex}:F{$rowIndex}");
                    $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                $highestColumn = $sheet->getHighestColumn();
                
                $judul = "Laporan Rekap Backup Data - {$this->perusahaan}";                
                $event->sheet->setCellValue('A1', $judul);
                $event->sheet->mergeCells("A1:{$highestColumn}1");        
                $event->sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'], 
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '6499E9'], 
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}

