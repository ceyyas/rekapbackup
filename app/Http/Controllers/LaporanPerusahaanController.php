<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Perusahaan;
use App\Models\Departemen;
use App\Models\Inventori;
use App\Models\InventoriHistory;
use App\Models\RekapBackup;
use App\Traits\RekapBackupTrait;
use App\Exports\RekapPerusahaanExport;
use App\Exports\RekapPerusahaanMultiExport;

use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

class LaporanPerusahaanController extends Controller
{
    use RekapBackupTrait;

    private function getPerusahaanRekap($perusahaanId)
    {
        return DB::table('rekap_backup')
            ->join('inventori', 'rekap_backup.inventori_id', '=', 'inventori.id')
            ->join('departemen', 'inventori.departemen_id', '=', 'departemen.id')
            ->where('departemen.perusahaan_id', $perusahaanId);
    }

    private function getPeriodes($perusahaanId, $tahun = null)
    {
        $query = $this->getPerusahaanRekap($perusahaanId);

        if ($tahun) {
            $query->whereYear('rekap_backup.periode', $tahun);
        }

        return $query
            ->selectRaw("DATE_FORMAT(rekap_backup.periode,'%b-%Y') as periode")
            ->distinct()
            ->orderBy('rekap_backup.periode')
            ->pluck('periode')
            ->toArray();
    }

    private function getRekap($perusahaanId, $tahun = null)
    {
        $query = $this->getPerusahaanRekap($perusahaanId);

        if ($tahun) {
            $query->whereYear('rekap_backup.periode', $tahun);
        }

        return $query
            ->select(
                'departemen.nama_departemen',
                DB::raw("DATE_FORMAT(rekap_backup.periode, '%b-%Y') as periode"),
                DB::raw('SUM(rekap_backup.size_data) as size_data'),
                DB::raw('SUM(rekap_backup.size_email) as size_email'),
                DB::raw('SUM(rekap_backup.size_data + rekap_backup.size_email) as total_size')
            )
            ->groupBy('departemen.nama_departemen','periode')
            ->orderBy('departemen.nama_departemen')
            ->orderBy('periode')
            ->get();
    }

    private function getRekapComplete($perusahaanId, $tahun = null)
    {
        $periodes   = $this->getPeriodes($perusahaanId, $tahun);
        $departemen = Departemen::where('perusahaan_id', $perusahaanId)
                        ->pluck('nama_departemen');

        $rekap = $this->getRekap($perusahaanId, $tahun)
            ->keyBy(function($row) {
                return $row->nama_departemen.'_'.$row->periode;
            });

        $result = collect();

        foreach ($departemen as $dept) {
            foreach ($periodes as $periode) {
                $key = $dept.'_'.$periode;

                if (isset($rekap[$key])) {
                    $result->push($rekap[$key]);
                } else {
                    $result->push((object)[
                        'nama_departemen' => $dept,
                        'periode'         => $periode,
                        'size_data'       => 0,
                        'size_email'      => 0,
                        'total_size'      => 0,
                    ]);
                }
            }
        }

        return $result;
    }

    private function getTahunList($perusahaanId)
    {
        return $this->getPerusahaanRekap($perusahaanId)
            ->selectRaw("YEAR(rekap_backup.periode) as tahun")
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun')
            ->toArray();
    }

    public function laporanperusahaan(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        return view('laporan.perusahaan.index', compact('perusahaans'));
    }

    public function laporanPerusahaanPivot(Request $request)
    {
        $perusahaanId = $request->perusahaan_id;
        $tahun        = $request->tahun;

        $periodes = $this->getPeriodes($perusahaanId, $tahun);
        $rekap    = $this->getRekapComplete($perusahaanId, $tahun);
        $tahunList = $this->getTahunList($perusahaanId);

        $pivot = [];
        foreach ($rekap as $r) {
            $pivot[$r->nama_departemen][$r->periode] =
                number_format($r->total_size, 0, ',', '.') . ' MB';
        }

        return response()->json([
            'tahun_list' => $tahunList,
            'periodes'   => $periodes,
            'pivot'      => $pivot
        ]);
    }

    public function exportPerusahaan(Request $request)
{
    $perusahaanId   = $request->perusahaan_id;
    $tahun          = $request->tahun; 
    $perusahaanNama = Perusahaan::find($perusahaanId)->nama_perusahaan;

    $periodes = $this->getPeriodes($perusahaanId, $tahun);
    $rekap    = $this->getRekapComplete($perusahaanId, $tahun);

    // Pivot data
    $pivot = [];
    foreach ($rekap as $r) {
        $pivot[$r->nama_departemen][$r->periode] = $r->total_size.' MB';
    }

    $globalPivot = [
        'periodes' => $periodes,
        'pivot'    => $pivot,
    ];

    $detailPeriodes = [];

    foreach ($periodes as $p) {
        $periodeDate = \Carbon\Carbon::createFromFormat('M-Y', $p)
                        ->startOfMonth()
                        ->format('Y-m-d');

        $departemens = Departemen::where('perusahaan_id', $perusahaanId)->get();

        $rekapDetail = $departemens->map(function($dept) use ($periodeDate) {
            $detailInventori = $this->getInventoriDetailQuery($dept->id, $periodeDate)->get();

            return (object)[
                'nama_departemen' => $dept->nama_departemen,
                'detail_inventori'=> $detailInventori
            ];
        });

        $detailPeriodes[$p] = $rekapDetail;
    }

    return Excel::download(
        new RekapPerusahaanMultiExport(
            $globalPivot,
            $detailPeriodes,
            $perusahaanNama,
            $perusahaanId
        ),
        "laporan_perusahaan_{$perusahaanNama}_{$tahun}.xlsx"
    );
}
}