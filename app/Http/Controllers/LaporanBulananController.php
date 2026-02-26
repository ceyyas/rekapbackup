<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Perusahaan;
use App\Models\Departemen;
use App\Models\Inventori;
use App\Models\InventoriHistory;
use App\Models\RekapBackup;

use App\Exports\RekapBulananExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class LaporanBulananController extends Controller
{
    public function laporanbulanan(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = collect();

        if ($request->filled('perusahaan_id') && $request->filled('periode_bulanan')) {
            $departemens = Departemen::where('perusahaan_id', $request->perusahaan_id)
                ->with(['rekap_backup' => function($q) use ($request) {
                    $q->where('periode', $request->periode_bulanan);
                }])
                ->get();
        }

        return view('laporan.bulanan.index', compact('perusahaans', 'departemens'));
    }

    public function laporanbulanandata(Request $request)
    {
        $periode = $request->periode_bulanan;

        $perusahaans = Perusahaan::with(['departemen.inventori.rekap_backup' => function($q) use ($periode) {
            $q->whereRaw("DATE_FORMAT(periode, '%Y-%m') = ?", [$periode]);
        }])->get();

        $result = [];
        foreach ($perusahaans as $perusahaan) {
            $rekap = $perusahaan->departemen->flatMap(function($dept) {
                return $dept->inventori->flatMap->rekap_backup;
            });

            $dataSize = $rekap->sum('size_data');   
            $emailSize = $rekap->sum('size_email'); 
            $total = $dataSize + $emailSize;

            $jumlah_cd700 = $rekap->sum('jumlah_cd700');
            $jumlah_dvd47 = $rekap->sum('jumlah_dvd47');
            $jumlah_dvd85 = $rekap->sum('jumlah_dvd85');
            $total_cd_dvd = $jumlah_cd700 + $jumlah_dvd47 + $jumlah_dvd85;

            $result[] = [
                'perusahaan' => $perusahaan->nama_perusahaan,
                'data' => $dataSize,
                'email' => $emailSize,
                'total' => $total,
                'jumlah_cd700' => $jumlah_cd700,
                'jumlah_dvd47' => $jumlah_dvd47,
                'jumlah_dvd85' => $jumlah_dvd85,
                'total_cd_dvd' => $total_cd_dvd,
            ];
        }

        return response()->json($result);
    }

    public function exportBulanan(Request $request) 
    {
        $periode = $request->periode_bulanan;
        $periodeFormat = Carbon::createFromFormat('Y-m', $periode) ->translatedFormat('F Y');
        $perusahaans = Perusahaan::with(['departemen.inventori.rekap_backup' => function($q) use ($periode) {
            $q->whereRaw("DATE_FORMAT(periode, '%Y-%m') = ?", [$periode]);
        }])->get();

        $result = [];
        foreach ($perusahaans as $perusahaan) {
            $rekap = $perusahaan->departemen->flatMap(function($dept) {
                return $dept->inventori->flatMap->rekap_backup;
            });

            $dataSize = $rekap->sum('size_data');   
            $emailSize = $rekap->sum('size_email'); 
            $total = $dataSize + $emailSize;

            $jumlah_cd700 = $rekap->sum('jumlah_cd700');
            $jumlah_dvd47 = $rekap->sum('jumlah_dvd47');
            $jumlah_dvd85 = $rekap->sum('jumlah_dvd85');
            $total_cd_dvd = $jumlah_cd700 + $jumlah_dvd47 + $jumlah_dvd85;

            $result[] = [
                'perusahaan' => $perusahaan->nama_perusahaan,
                'data' => $dataSize,
                'email' => $emailSize,
                'total' => $total,
                'jumlah_cd700' => $jumlah_cd700,
                'jumlah_dvd47' => $jumlah_dvd47,
                'jumlah_dvd85' => $jumlah_dvd85,
                'total_cd_dvd' => $total_cd_dvd,
            ];
        }

        return Excel::download(
            new RekapBulananExport($result, $periode, $periodeFormat),
            'laporan_bulanan_all_pt_' . $periodeFormat . '.xlsx'
        );

    }
}
