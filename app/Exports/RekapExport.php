<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class RekapExport implements 
        FromCollection, 
        WithStyles, 
        WithColumnWidths, 
        WithEvents, 
        WithCustomStartCell
{
    protected $rekap;        
    protected $departemens;  
    protected $perusahaan;
    protected $periode;
    protected $headingRows = [];

    public function __construct($rekap, $departemens, $perusahaan, $periode)
    {
        $this->rekap = $rekap;              
        $this->departemens = $departemens;  
        $this->perusahaan = $perusahaan; 
        $this->periode = $periode;
    }

    public function startCell(): string { 
        return 'A2'; 
    }
    
    public function collection()
    {
        $rows = collect();
        $no = 1;

        // --- Heading Global ---
        $rows->push([
            'No','Departemen',
            'SIZE DATA (MB)','SIZE DATA (GB)',
            'SIZE EMAIL (MB)','SIZE EMAIL (GB)',
            'Total Size (MB)','Total Size (GB)',
            'Status Backup','Status Data'
        ]);

        // --- Isi Global ---
        foreach ($this->rekap as $dept) {
            $rows->push([
                $no++,
                $dept->nama_departemen,
                $dept->size_data_mb . ' MB',
                $dept->size_data_gb . ' GB',
                $dept->size_email_mb . ' MB',
                $dept->size_email_gb . ' GB',
                $dept->total_size_mb . ' MB',
                $dept->total_size_gb . ' GB',
                $dept->status_backup,
                $dept->status_data,
            ]);
        }

        // --- Total Global ---
        $rows->push([
            '',
            'TOTAL',
            $this->rekap->sum('size_data_mb') . ' MB',
            round($this->rekap->sum('size_data_mb')/1024,2).' GB',
            $this->rekap->sum('size_email_mb') . ' MB',
            round($this->rekap->sum('size_email_mb')/1024,2).' GB',
            $this->rekap->sum('total_size_mb') . ' MB',
            round($this->rekap->sum('total_size_mb')/1024,2).' GB',
            '',
            ''
        ]);

        // --- Detail per Departemen (per inventori) ---
        foreach ($this->departemens as $dept) {
        
            // heading nama departemen
            $rows->push([$dept->nama_departemen]);
            $this->headingRows[] = $rows->count() +1;

            // heading tabel detail
            $rows->push([
                'No','Hostname','Username','Email',
                'SIZE DATA (MB)','SIZE DATA (GB)',
                'SIZE EMAIL (MB)','SIZE EMAIL (GB)',
                'Total Size (MB)','Total Size (GB)'
            ]);

            $noDetail = 1;
            $totalDataMb = 0;
            $totalEmailMb = 0;
            $totalBurningMb = 0;

            foreach ($dept->inventori as $inv) {
                // jumlahkan semua backup milik inventori ini
                $dataMb   = $inv->rekap_backup->sum('size_data');
                $emailMb  = $inv->rekap_backup->sum('size_email');
                $burningMb = $dataMb + $emailMb;

                $rows->push([
                    $noDetail++,
                    $inv->hostname,
                    $inv->username ?? '-', 
                    $inv->email ?? '-',
                    $dataMb . ' MB',
                    round($dataMb/1024,2) . ' GB',
                    $emailMb . ' MB',
                    round($emailMb/1024,2) . ' GB',
                    $burningMb . ' MB',
                    round($burningMb/1024,2) . ' GB',
                ]);

                $totalDataMb   += $dataMb;
                $totalEmailMb  += $emailMb;
                $totalBurningMb += $burningMb;
            }

            // total per departemen
            $rows->push([
                '',
                '',
                'TOTAL',
                '',
                $totalDataMb . ' MB',
                round($totalDataMb/1024,2) . ' GB',
                $totalEmailMb . ' MB',
                round($totalEmailMb/1024,2) . ' GB',
                $totalBurningMb . ' MB',
                round($totalBurningMb/1024,2) . ' GB',
            ]);
        }

        return $rows;
    }


    public function registerEvents(): array    
    {    
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // merge semua baris heading departemen
                foreach ($this->headingRows as $rowIndex) {
                    $sheet->mergeCells("A{$rowIndex}:J{$rowIndex}");
                    $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // judul laporan di baris 1
                $periodeFormat = \Carbon\Carbon::parse($this->periode)->translatedFormat('F Y');
                $judul = "Laporan Rekap Backup Data - {$this->perusahaan} - Periode {$periodeFormat}";                
                $event->sheet->setCellValue('A1', $judul);
                $event->sheet->mergeCells('A1:J1');          
                $event->sheet->getStyle('A1:J1')->applyFromArray([
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

    
      public function styles(Worksheet $sheet)
    {
        // Header bold + background
        $sheet->getStyle('A2:J2')->applyFromArray([
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

        $sheet->getStyle("C3:J{$highestRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
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
