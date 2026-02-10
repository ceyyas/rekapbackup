<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Events\AfterSheet;

class RekapPeriodeExport implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $data;
    protected $periode;
    protected $perusahaan;

    public function __construct(array $data, string $periode, string $perusahaan)
    {
        $this->data       = $data;
        $this->periode    = $periode;
        $this->perusahaan = $perusahaan;
    }

    public function collection()
    {
        $rows = [];

        $grouped = collect($this->data)->groupBy('departemen');

        foreach ($grouped as $dept => $items) {
            $rows[] = [$dept, '', '', '', '', '', ''];
            $rows[] = ['No','Hostname','Username','Email','Size Data','Size Email','Total Size'];

            $no = 1;
            $totalData = 0;
            $totalEmail = 0;

            foreach ($items as $row) {
                $rows[] = [
                    $no++,
                    $row['hostname'],
                    $row['username'],
                    $row['email'],
                    $row['size_data'].' MB',
                    $row['size_email'].' MB',
                    ($row['size_data'] + $row['size_email']).' MB',
                ];

                $totalData  += $row['size_data'];
                $totalEmail += $row['size_email'];
            }

            $rows[] = [
                '', '', '', 'TOTAL',
                $totalData.' MB',
                $totalEmail.' MB',
                ($totalData + $totalEmail).' MB',
            ];

            $rows[] = ['', '', '', '', '', '', ''];
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return $this->periode;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Atur lebar kolom
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(12);
                $sheet->getColumnDimension('F')->setWidth(12);
                $sheet->getColumnDimension('G')->setWidth(12);

                // Border untuk semua tabel
                $sheet->getStyle("A1:G{$highestRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ]
                    ]);

                // Styling heading departemen
                for ($row = 1; $row <= $highestRow; $row++) {
                    $value = $sheet->getCell("A{$row}")->getValue();
                    $colB  = $sheet->getCell("B{$row}")->getValue();

                    if (!empty($value) && empty($colB)) {
                        $sheet->mergeCells("A{$row}:G{$row}");
                        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
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

                // Styling heading kolom tabel
                for ($row = 1; $row <= $highestRow; $row++) {
                    $value = $sheet->getCell("A{$row}")->getValue();
                    if ($value === 'No') {
                        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'D9EAF7'],
                            ],
                        ]);
                    }
                }

                // Styling baris TOTAL
                for ($row = 1; $row <= $highestRow; $row++) {
                    $value = $sheet->getCell("D{$row}")->getValue();
                    if ($value === 'TOTAL') {
                        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'f8f731'], 
                            ],
                        ]);
                    }
                }
            },
        ];
    }

}

