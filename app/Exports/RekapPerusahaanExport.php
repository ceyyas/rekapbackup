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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
            $header[] = 'TOTAL'; 
        }
        $rows[] = $header;

        // isi data per departemen
        foreach ($this->rekap['pivot'] as $dept => $data) {
            $row = [$dept];
            $totalDept = 0;

            foreach ($this->rekap['periodes'] as $p) {
                $val = $data[$p] ?? 0;
                $numVal = is_string($val) ? (float) str_replace([' MB','.'], ['',''], $val) : $val;
                $row[] = $val ?: '-';
                $totalDept += $numVal;
            }

            $row[] = $totalDept . ' MB'; 
            $rows[] = $row;
        }

        // baris total per periode
        $totalRow = ['TOTAL'];
        foreach ($this->rekap['periodes'] as $p) {
            $sumPeriode = 0;
            foreach ($this->rekap['pivot'] as $dept => $data) {
                $val = $data[$p] ?? 0;
                $numVal = is_string($val) ? (float) str_replace([' MB','.'], ['',''], $val) : $val;
                $sumPeriode += $numVal;
            }
            $totalRow[] = $sumPeriode . ' MB';
        }

        // total keseluruhan
        $grandTotal = 0;
        foreach ($this->rekap['pivot'] as $dept => $data) {
            foreach ($this->rekap['periodes'] as $p) {
                $val = $data[$p] ?? 0;
                $numVal = is_string($val) ? (float) str_replace([' MB','.'], ['',''], $val) : $val;
                $grandTotal += $numVal;
            }
        }
        $totalRow[] = $grandTotal . ' MB';
        $rows[] = $totalRow;

        return new Collection($rows);
    }

    public function styles(Worksheet $sheet)
    {
        $colCount = 1 + count($this->rekap['periodes']) + 1; // Departemen + periodes + TOTAL
        $highestColumn = Coordinate::stringFromColumnIndex($colCount);
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle("A2:{$highestColumn}2")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        $sheet->getStyle("A2:{$highestColumn}{$highestRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);

        $sheet->getStyle("B3:{$highestColumn}{$highestRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 20];
        for ($i = 2; $i <= 20; $i++) { 
            $col = Coordinate::stringFromColumnIndex($i);
            $widths[$col] = 12;
        }
        return $widths;
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

                $colCount = 1 + count($this->rekap['periodes']) + 1;
                $highestColumn = Coordinate::stringFromColumnIndex($colCount);

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