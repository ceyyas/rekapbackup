<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Perusahaan;
use App\Models\Departemen;
use App\Models\Inventori;
use App\Models\InventoriHistory;
use App\Models\RekapBackup;

use App\Exports\RekapPerusahaanExport;
use App\Exports\RekapPerusahaanMultiExport;

use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

class LaporanPerusahaanController extends Controller
{
    private function getPerusahaanRekap($perusahaanId)
    {
        return DB::table('rekap_backup')
            ->join('inventori', 'rekap_backup.inventori_id', '=', 'inventori.id')
            ->join('departemen', 'inventori.departemen_id', '=', 'departemen.id')
            ->where('departemen.perusahaan_id', $perusahaanId);
    }

    private function getPeriodes($perusahaanId)
    {
        return $this->getPerusahaanRekap($perusahaanId)
            ->selectRaw("DATE_FORMAT(rekap_backup.periode,'%b-%Y') as periode")
            ->distinct()
            ->orderBy('rekap_backup.periode')
            ->pluck('periode')
            ->toArray();
    }

    private function getRekap($perusahaanId)
    {
        return $this->getPerusahaanRekap($perusahaanId)
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

    private function getRekapComplete($perusahaanId)
    {
        $periodes = $this->getPeriodes($perusahaanId);
        $departemen = Departemen::where('perusahaan_id', $perusahaanId)->pluck('nama_departemen');

        $rekap = $this->getRekap($perusahaanId)->keyBy(function($row) {
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
                        'jumlah_cd700'    => 0,
                        'jumlah_dvd47'    => 0,
                        'jumlah_dvd85'    => 0,
                        'total_cd_dvd'    => 0,

                    ]);
                }
            }
        }

        return $result;
    }

    public function laporanperusahaan(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        return view('laporan.perusahaan.index', compact('perusahaans'));
    }

    public function laporanPerusahaanPivot(Request $request)
    {
        $perusahaanId = $request->perusahaan_id;
        $periodes = $this->getPeriodes($perusahaanId);
        $rekap    = $this->getRekapComplete($perusahaanId);

        $pivot = [];
        foreach ($rekap as $r) {
            $pivot[$r->nama_departemen][$r->periode] = number_format($r->total_size, 0, ',', '.') . ' MB';
        }

        return response()->json([
            'periodes' => $periodes,
            'pivot'    => $pivot
        ]);
    }

    public function exportPerusahaan(Request $request)
    {
        $perusahaanId   = $request->perusahaan_id;
        $perusahaanNama = Perusahaan::find($perusahaanId)->nama_perusahaan;

        $periodes = $this->getPeriodes($perusahaanId);
        $rekap    = $this->getRekapComplete($perusahaanId);

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
            $periodeDate = \Carbon\Carbon::createFromFormat('M-Y', $p)->format('Y-m-01');
            $rekapDetail = DB::table('rekap_backup')
                ->join('inventori', 'rekap_backup.inventori_id', '=', 'inventori.id')
                ->join('departemen', 'inventori.departemen_id', '=', 'departemen.id')
                ->leftJoin(DB::raw("
                    (
                        SELECT ih.*
                        FROM inventori_history ih
                        JOIN (
                            SELECT inventori_id, MIN(effective_date) as last_date
                            FROM inventori_history
                            WHERE effective_date >= STR_TO_DATE('$periodeDate','%Y-%m-%d')
                            GROUP BY inventori_id
                        ) latest
                        ON ih.inventori_id = latest.inventori_id
                        AND ih.effective_date = latest.last_date
                    ) as snapshot
                "), 'snapshot.inventori_id', '=', 'inventori.id')
                ->where('departemen.perusahaan_id', $perusahaanId)
                ->whereRaw("DATE_FORMAT(rekap_backup.periode,'%b-%Y') = ?", [$p])
                ->where(function($q) use ($periodeDate) {
                    $q->where(function($sub) use ($periodeDate) {
                        $sub->where('inventori.status', 'active')
                            ->orWhere('inventori.updated_at', '>', $periodeDate);
                    })
                    ->where('inventori.created_at', '<=', $periodeDate); 
                })
                   ->select(
                    'departemen.nama_departemen as departemen',
                    DB::raw("CASE 
                        WHEN snapshot.id IS NOT NULL 
                            AND STR_TO_DATE('$periodeDate','%Y-%m-%d') < snapshot.effective_date 
                        THEN snapshot.hostname 
                        ELSE inventori.hostname 
                    END as hostname"),
                    DB::raw("CASE 
                        WHEN snapshot.id IS NOT NULL 
                            AND STR_TO_DATE('$periodeDate','%Y-%m-%d') < snapshot.effective_date 
                        THEN snapshot.username 
                        ELSE inventori.username 
                    END as username"),
                    DB::raw("CASE 
                        WHEN snapshot.id IS NOT NULL 
                            AND STR_TO_DATE('$periodeDate','%Y-%m-%d') < snapshot.effective_date 
                        THEN snapshot.email 
                        ELSE inventori.email 
                    END as email"),
                    'rekap_backup.size_data',
                    'rekap_backup.size_email'
                )
                ->get()
                ->map(fn($row) => [
                    'departemen' => $row->departemen,
                    'hostname'   => $row->hostname,
                    'username'   => $row->username,
                    'email'      => $row->email,
                    'size_data'  => $row->size_data,
                    'size_email' => $row->size_email,
                ])
                ->toArray();

            $detailPeriodes[$p] = $rekapDetail;
        }

        return Excel::download(
            new RekapPerusahaanMultiExport($globalPivot, $detailPeriodes, $perusahaanNama, $perusahaanId),
            "laporan_perusahaan_{$perusahaanNama}.xlsx"
        );
    }
}
