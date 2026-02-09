<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;

class RekapBulananExport implements 
    FromCollection, 
    WithHeadings, 
    WithStyles,
    WithCustomStartCell, 
    WithColumnWidths
{
    protected $data;
    protected $periode;

    public function __construct(array $data, $periode)
    {
        $this->data = $data;
        $this->periode = $periode;
    }

    public function startCell(): string { 
        return 'A2'; 
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return ['Perusahaan', 'Size Data', 'Size Email', 'Total Size'];
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
                
                $judul = "Laporan Rekap Backup Data ALL PT- {$this->periode}";                
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

    public function columnWidths(): array
    {
        return [
            'A' => 20,
        ];
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
}
