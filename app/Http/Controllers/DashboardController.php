<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventori;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            $periode = Carbon::createFromDate($row->tahun, $row->bulan, 1);
            // gunakan format bahasa Inggris agar konsisten dengan JS
            $labelBulan = $periode->format('F Y'); // contoh: February 2026

            if (!isset($dataChart[$labelBulan])) {
                $dataChart[$labelBulan] = [];
            }

            // konversi ke GB (asumsi size dalam MB)
            $dataChart[$labelBulan][$row->nama_perusahaan] = round($row->total_size / 1024, 2);
        }

        return view('dashboard', compact('totalKomputer', 'totalLaptop', 'dataChart'));
    }
}
