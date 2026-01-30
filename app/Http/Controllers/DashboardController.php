<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventori;
use App\Models\RekapBackup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ringkasan inventori
        $totalKomputer = Inventori::where('kategori', 'PC')->count();
        $totalLaptop   = Inventori::where('kategori', 'Laptop')->count();

        // Rekap backup per bulan per perusahaan
        $rekapBackup = DB::table('rekap_backup as rb')
            ->join('inventori as i', 'rb.inventori_id', '=', 'i.id')
            ->join('perusahaan as p', 'i.perusahaan_id', '=', 'p.id')
            ->selectRaw('YEAR(rb.periode) as tahun, MONTH(rb.periode) as bulan, p.nama_perusahaan, SUM(rb.size_data + rb.size_email) as total_size')
            ->groupBy('tahun','bulan','p.nama_perusahaan')
            ->orderBy('tahun','asc')
            ->orderBy('bulan','asc')
            ->get();

        $dataChart = [];
        foreach ($rekapBackup as $row) {
            // bikin tanggal lengkap dari tahun + bulan
            $periode = Carbon::createFromDate($row->tahun, $row->bulan, 1);
            $labelBulan = $periode->translatedFormat('F Y'); // contoh: Juni 2026
            $dataChart[$labelBulan][$row->nama_perusahaan] = round($row->total_size / 1024, 2);
        }        

        return view('dashboard', compact('totalKomputer', 'totalLaptop', 'dataChart'));
    }


}
