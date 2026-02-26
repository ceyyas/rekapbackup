<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class RekapExport implements 
        FromCollection, 
        WithStyles, 
        WithColumnWidths, 
        WithEvents, 
        WithTitle,
        WithCustomStartCell
{
    protected $rekapDepartemen;        
    protected $rekapDetail;  
    protected $perusahaan;
    protected $periode;
    protected $headingRows = [];

    public function __construct($rekapDepartemen, $rekapDetail, $perusahaan, $periode)
    {
        $this->rekapDepartemen = $rekapDepartemen;              
        $this->rekapDetail = $rekapDetail;  
        $this->perusahaan = $perusahaan; 
        $this->periode = $periode;
    }
    public function title(): string { 
        return \Carbon\Carbon::parse($this->periode)->translatedFormat('F Y'); 
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
            'CD 700 MB','DVD 4.7 GB','DVD 8.5 GB','Total CD/DVD',
            'Status Backup','Status Data'
        ]);

        // --- Isi Global ---
        foreach ($this->rekapDepartemen as $dept) {
            $rows->push([
                $no++,
                $dept->nama_departemen,
                $dept->size_data . ' MB',
                round($dept->size_data / 1024, 2) . ' GB',
                $dept->size_email . ' MB',
                round($dept->size_email / 1024, 2) . ' GB',
                $dept->total_size . ' MB',
                round($dept->total_size / 1024, 2) . ' GB',
                $dept->jumlah_cd700,
                $dept->jumlah_dvd47,
                $dept->jumlah_dvd85,
                $dept->total_cd_dvd,
                $dept->status_backup,
                $dept->status_data,
            ]);
        }

        // --- Total Global ---
        $rows->push([
            '',
            'TOTAL',
            $this->rekapDepartemen->sum('size_data') . ' MB',
            round($this->rekapDepartemen->sum('size_data')/1024,2).' GB',
            $this->rekapDepartemen->sum('size_email') . ' MB',
            round($this->rekapDepartemen->sum('size_email')/1024,2).' GB',
            $this->rekapDepartemen->sum('total_size') . ' MB',
            round($this->rekapDepartemen->sum('total_size')/1024,2).' GB',
            $this->rekapDepartemen->sum('jumlah_cd700'),
            $this->rekapDepartemen->sum('jumlah_dvd47'),
            $this->rekapDepartemen->sum('jumlah_dvd85'),
            $this->rekapDepartemen->sum('total_cd_dvd'),
            '',
            ''
        ]);

        // --- Tambahkan 2 baris kosong sebelum detail ---
        $rows->push(['','','','','','','','','','','','','','']);
        $rows->push(['','','','','','','','','','','','','','']);


        foreach ($this->rekapDetail as $dept) {
        
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

            foreach ($dept->detail_inventori as $inv) {
                $dataMb = $inv->size_data; 
                $emailMb = $inv->size_email; 
                $burningMb = $inv->total_size;

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

            $rows[] = ['', '', '', '', '', '', ''];
        }

        return $rows;
    }

    public function registerEvents(): array    
    {    
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Judul laporan
                $periodeFormat = \Carbon\Carbon::parse($this->periode)->translatedFormat('F Y');
                $judul = "Laporan Rekap Backup Data - {$this->perusahaan} - Periode {$periodeFormat}";                
                $event->sheet->setCellValue('A1', $judul);
                $event->sheet->mergeCells('A1:N1');          
                $event->sheet->getStyle('A1:N1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '276522']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(25);

                $startRow = null;
                $isDetail = false;

                for ($row = 2; $row <= $highestRow; $row++) {
                    $valA = $sheet->getCell("A{$row}")->getValue();
                    $valB = $sheet->getCell("B{$row}")->getValue();
                    $valC = $sheet->getCell("C{$row}")->getValue();

                    // Heading departemen detail
                    if (!empty($valA) && empty($valB)) {
                        $sheet->mergeCells("A{$row}:J{$row}");
                        $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2a6099']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                    }

                    // Awal tabel
                    if ($valA === 'No') {
                        $startRow = $row;
                        // cek apakah tabel detail (kolom B = Hostname)
                        $isDetail = ($valB === 'Hostname');

                        // style header
                        $endCol = $isDetail ? 'J' : 'N';
                        $sheet->getStyle("A{$row}:{$endCol}{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9EAF7']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                    }

                    // Akhir tabel (TOTAL)
                    if ($valB === 'TOTAL' || $valC === 'TOTAL') {
                        if ($startRow !== null) {
                            $endCol = $isDetail ? 'J' : 'N';
                            // border tabel
                            $sheet->getStyle("A{$startRow}:{$endCol}{$row}")->applyFromArray([
                                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
                            ]);
                            // style TOTAL
                            $sheet->getStyle("A{$row}:{$endCol}{$row}")->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF933']],
                            ]);
                            $startRow = null;
                            $isDetail = false;
                        }
                    }
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Kolom No rata tengah
        $sheet->getStyle("A3:A{$highestRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Kolom angka global (C–N)
        $sheet->getStyle("C3:N{$highestRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Kolom angka detail (C–J)
        $sheet->getStyle("C3:J{$highestRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

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
            'I' => 12,
            'J' => 12,
            'K' => 12,
            'L' => 14,
            'M' => 15,
            'N' => 20,
        ];
    }

}
