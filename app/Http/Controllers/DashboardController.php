<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stok;
use App\Models\Inventori;
use App\Models\RekapBackup;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $rekap = RekapBackup::orderByDesc('updated_at')->get();

        $totalDataMB   = $rekap->sum('size_data');
        $totalEmailMB  = $rekap->sum('size_email');

        $totalDataGB   = number_format($totalDataMB / 1024, 2);
        $totalEmailGB  = number_format($totalEmailMB / 1024, 2);

        $stoks = Stok::orderByDesc('updated_at')->get();
        $totalTersisa = $stoks->sum('tersisa');
        $totalPemakaian = $stoks->sum('pemakaian');

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
            $labelBulan = $periode->format('F Y'); 

            if (!isset($dataChart[$labelBulan])) {
                $dataChart[$labelBulan] = [];
            }

            // konversi ke GB (asumsi size dalam MB)
            $dataChart[$labelBulan][$row->nama_perusahaan] = round($row->total_size / 1024, 2);
        }

        return view('dashboard', compact('totalDataGB', 'totalEmailGB','stoks','totalTersisa', 'totalPemakaian', 'dataChart'));
    }
}
