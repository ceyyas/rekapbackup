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

            $result[] = [
                'perusahaan' => $perusahaan->nama_perusahaan,
                'data' => $dataSize,
                'email' => $emailSize,
                'total' => $total,
            ];
        }

        return response()->json($result);
    }

    public function exportBulanan(Request $request) 
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

            $result[] = [
                'perusahaan' => $perusahaan->nama_perusahaan,
                'data' => $dataSize,
                'email' => $emailSize,
                'total' => $total,
            ];
        }

        return Excel::download(new RekapBulananExport($result, $periode), 'laporan_bulanan_ALL_PT.xlsx');
    }
}
