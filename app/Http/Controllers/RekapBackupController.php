<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Perusahaan;
use App\Models\Departemen;
use App\Models\Inventori;
use App\Models\RekapBackup;
use App\Models\Stok;

use App\Exports\RekapExport;
use App\Exports\RekapPerusahaanExport;
use App\Exports\RekapPerusahaanMultiExport;

use App\Exports\RekapBulananExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

class RekapBackupController extends Controller
{
    private function getDepartemenQuery($perusahaanId, $periode)
    {
        return DB::table('departemen')
            ->leftJoin('inventori', 'inventori.departemen_id', '=', 'departemen.id')
            ->leftJoin('rekap_backup', function ($join) use ($periode) {
                $join->on('rekap_backup.inventori_id', '=', 'inventori.id')
                    ->where('rekap_backup.periode', $periode);
            })
            ->where('departemen.perusahaan_id', $perusahaanId)
            ->where(function($q) use ($periode) {
                $periodeDate = DB::raw("STR_TO_DATE('$periode','%Y-%m-%d')");
                $q->where('inventori.status', 'active')
                ->orWhere($periodeDate, '<', DB::raw('inventori.updated_at'));
            })
            ->select(
                'departemen.id',
                'departemen.nama_departemen',
                DB::raw('MAX(inventori.id) as inventori_id'),
                DB::raw('COALESCE(SUM(rekap_backup.size_data), 0) AS size_data'),
                DB::raw('COALESCE(SUM(rekap_backup.size_email), 0) AS size_email'),
                DB::raw('COALESCE(SUM(rekap_backup.size_data + rekap_backup.size_email), 0) AS total_size'),
                DB::raw('COALESCE(SUM(rekap_backup.jumlah_cd700), 0) AS jumlah_cd700'),
                DB::raw('COALESCE(SUM(rekap_backup.jumlah_dvd47), 0) AS jumlah_dvd47'),
                DB::raw('COALESCE(SUM(rekap_backup.jumlah_dvd85), 0) AS jumlah_dvd85'),
                DB::raw("
                    CASE
                        WHEN COUNT(rekap_backup.id) = 0 THEN 'pending'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) = COUNT(rekap_backup.id)
                            AND COALESCE(SUM(rekap_backup.size_data),0) > 0
                            AND (
                                (SUM(CASE WHEN inventori.email IS NOT NULL AND inventori.email <> '' THEN 1 ELSE 0 END) > 0
                                AND COALESCE(SUM(rekap_backup.size_email),0) > 0)
                                OR SUM(CASE WHEN inventori.email IS NOT NULL AND inventori.email <> '' THEN 1 ELSE 0 END) = 0
                            )
                        THEN 'completed'
                        ELSE 'partial'
                    END AS status_backup
                "),
                DB::raw("
                    CASE
                        WHEN COUNT(rekap_backup.id) = 0 THEN 'data belum di backup'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) < COUNT(rekap_backup.id) 
                            THEN 'proses backup'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) = COUNT(rekap_backup.id) 
                            AND (COALESCE(SUM(rekap_backup.jumlah_cd700),0) = 0 
                                AND COALESCE(SUM(rekap_backup.jumlah_dvd47),0) = 0 
                                AND COALESCE(SUM(rekap_backup.jumlah_dvd85),0) = 0)
                            THEN 'file di main folder aman'
                        ELSE 'file di cd aman'
                    END AS status_data
                ")
            )
            ->groupBy('departemen.id','departemen.nama_departemen')
            ->orderBy('departemen.nama_departemen');
    }



    public function index(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = collect();

        if ($request->filled(['perusahaan_id', 'periode_id'])) {
            $periode = $request->periode_id . '-01';

            $departemens = $this->getDepartemenQuery($request->perusahaan_id, $periode)->get();
        }

        return view('rekap.index', compact('perusahaans', 'departemens'));
    }


    public function filter(Request $request)
    {
        if (!$request->filled(['perusahaan_id', 'periode_id'])) {
            return response()->json([]);
        }

        $request->validate([
            'periode_id' => 'date_format:Y-m',
            'perusahaan_id' => 'required|exists:perusahaan,id'
        ]);

        $periode = $request->periode_id . '-01';
        $departemens = $this->getDepartemenQuery($request->perusahaan_id, $periode)->get();

        return response()->json($departemens);
    }


    public function cdDvd(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = collect();

        if ($request->filled(['perusahaan_id', 'periode_id'])) {
            $periode = $request->periode_id . '-01';
            $departemens = $this->getDepartemenQuery($request->perusahaan_id, $periode)->get();

        }   
        return view('rekap.cd_dvd', compact('perusahaans','departemens'));
    }

    public function autoSave(Request $request)
    {
        $request->validate([
            'inventori_id' => 'required|exists:inventori,id',
            'periode_id'   => 'required|date_format:Y-m',
            'cd700'        => 'nullable|integer|min:0',
            'dvd47'        => 'nullable|integer|min:0',
            'dvd85'        => 'nullable|integer|min:0',
        ]);

        $periode = $request->periode_id . '-01';

        $rekap = RekapBackup::firstOrNew([
            'inventori_id' => $request->inventori_id,
            'periode'      => $periode,
        ]);

        if ($request->cd700 !== null) {
            $rekap->jumlah_cd700 = (int) $request->cd700;
        }
        if ($request->dvd47 !== null) {
            $rekap->jumlah_dvd47 = (int) $request->dvd47;
        }
        if ($request->dvd85 !== null) {
            $rekap->jumlah_dvd85 = (int) $request->dvd85;
        }

        $rekap->save();

        return response()->json(['success' => true]);
    }


    public function detailPage(Request $request, $departemenId)
    {
        $request->validate([
            'periode_id' => 'required|date_format:Y-m'
        ]);

        $departemen = Departemen::findOrFail($departemenId);
        $periode = \Carbon\Carbon::createFromFormat('Y-m', $request->periode_id);

        $periodeFormatted = $periode->translatedFormat('F Y');

        return view('rekap.detail-page', compact('departemen', 'periodeFormatted'));
    }

    public function detailData(Request $request, $departemenId)
    {
        $request->validate([
            'periode_id' => 'required|date_format:Y-m'
        ]);

        $periode = $request->periode_id . '-01';

        $inventoris = DB::table('inventori')
            ->leftJoin('rekap_backup', function ($join) use ($periode) {
                $join->on('rekap_backup.inventori_id', '=', 'inventori.id')
                     ->where('rekap_backup.periode', $periode);
            })
            ->where('inventori.departemen_id', $departemenId)
            ->where(function($q) use ($periode) {
                $periodeDate = DB::raw("STR_TO_DATE('$periode','%Y-%m-%d')");
                $q->where('inventori.status', 'active')
                ->orWhere($periodeDate, '<', DB::raw('inventori.updated_at'));
            })

            ->select(
                'inventori.id',
                'inventori.hostname',
                'inventori.username',
                'inventori.email',
                DB::raw('COALESCE(SUM(rekap_backup.size_data), 0) AS size_data'),
                DB::raw('COALESCE(SUM(rekap_backup.size_email), 0) AS size_email'),
                DB::raw('COALESCE(SUM(rekap_backup.size_data + rekap_backup.size_email), 0) AS total_size')
            )
            ->groupBy(
                'inventori.id',
                'inventori.hostname',
                'inventori.username',
                'inventori.email'
            )
            ->orderBy('inventori.hostname')
            ->get();

        return view('rekap.detail-table', [
            'inventoris' => $inventoris,
            'periodeId' => $periode
        ]);
    }
    
    public function saveDetail(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|date_format:Y-m',
            'data' => 'required|array'
        ]);

        $periode = $request->periode_id . '-01';

        foreach ($request->data as $inventoriId => $val) {
            RekapBackup::updateOrCreate(
                [
                    'inventori_id' => $inventoriId,
                    'periode' => $periode
                ],
                [
                    'size_data' => $val['size_data'] ?? 0,
                    'size_email' => $val['size_email'] ?? 0,
                    'status' => (
                        ($val['size_data'] ?? 0) + ($val['size_email'] ?? 0)
                    ) > 0 ? 'completed' : 'pending'
                ]
            );
        }

        return back()->with('success', 'Data backup berhasil disimpan');
    }
    
    public function export(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|date_format:Y-m',
            'perusahaan_id' => 'required|exists:perusahaan,id'
        ]);

        $perusahaanId = $request->input('perusahaan_id'); 
        $periode = $request->periode_id . '-01'; 

        $perusahaan = Perusahaan::find($perusahaanId)->nama_perusahaan;

        // gunakan fungsi getDepartemenQuery
        $rekap = $this->getDepartemenQuery($perusahaanId, $periode)->get();

        // ambil relasi departemen + inventori + rekap_backup untuk detail
        $departemens = Departemen::with(['inventori.rekap_backup' => function($q) use ($periode) {
            $q->where('periode', $periode);
        }])
        ->where('perusahaan_id', $perusahaanId)
        ->get();

        return Excel::download(
            new RekapExport($rekap, $departemens, $perusahaan, $periode),
            'rekap_backup_' . now()->format('Ymd_His') . '.xlsx'
        );
    }


    public function laporanperusahaan(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();

        return view('laporan.perusahaan.index', compact('perusahaans'));
    }

    public function laporanPerusahaanPivot(Request $request)
    {
        $perusahaanId = $request->perusahaan_id;
        $periodes = DB::table('rekap_backup')
            ->join('inventori', 'rekap_backup.inventori_id', '=', 'inventori.id')
            ->join('departemen', 'inventori.departemen_id', '=', 'departemen.id')
            ->where('departemen.perusahaan_id', $perusahaanId)
            ->select(DB::raw("DATE_FORMAT(rekap_backup.periode, '%b-%Y') as periode"))
            ->distinct()
            ->orderBy('periode')
            ->pluck('periode')
            ->toArray();

        $rekap = DB::table('rekap_backup')
            ->join('inventori', 'rekap_backup.inventori_id', '=', 'inventori.id')
            ->join('departemen', 'inventori.departemen_id', '=', 'departemen.id')
            ->where('departemen.perusahaan_id', $perusahaanId)
            ->select(
                'departemen.nama_departemen',
                DB::raw("DATE_FORMAT(rekap_backup.periode, '%b-%Y') as periode"),
                DB::raw('COALESCE(SUM(rekap_backup.size_data),0) as size_data'),
                DB::raw('COALESCE(SUM(rekap_backup.size_email),0) as size_email'),
                DB::raw('COALESCE(SUM(rekap_backup.size_data + rekap_backup.size_email),0) as total_size')
            )
            ->groupBy('departemen.nama_departemen','periode')
            ->orderBy('departemen.nama_departemen')
            ->orderBy('periode')
            ->get();

        $pivot = [];
        foreach ($rekap as $r) {
            $pivot[$r->nama_departemen][$r->periode] = number_format($r->total_size, 0, ',', '.') . ' MB';
        }

        return response()->json([
            'periodes' => $periodes,
            'pivot' => $pivot 
        ]);
    }

    public function exportPerusahaan(Request $request)
    {
        $perusahaanId   = $request->perusahaan_id;
        $perusahaanNama = Perusahaan::find($perusahaanId)->nama_perusahaan;

        // Global pivot (Departemen × Periode)
        $periodes = DB::table('rekap_backup')
            ->join('inventori','rekap_backup.inventori_id','=','inventori.id')
            ->join('departemen','inventori.departemen_id','=','departemen.id')
            ->where('departemen.perusahaan_id',$perusahaanId)
            ->selectRaw("DATE_FORMAT(rekap_backup.periode,'%b-%Y') as periode")
            ->distinct()
            ->orderBy('periode')
            ->pluck('periode')
            ->toArray();

        $rekap = DB::table('rekap_backup')
            ->join('inventori', 'rekap_backup.inventori_id', '=', 'inventori.id')
            ->join('departemen', 'inventori.departemen_id', '=', 'departemen.id')
            ->where('departemen.perusahaan_id', $perusahaanId)
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

        $pivot = [];
        foreach ($rekap as $r) {
            $pivot[$r->nama_departemen][$r->periode] = $r->total_size.' MB';
        }
        $globalPivot = [
            'periodes' => $periodes,
            'pivot'    => $pivot,
        ];

        // Detail per periode
        $detailPeriodes = [];
        foreach ($periodes as $p) {
            $rekapDetail = DB::table('rekap_backup')
                ->join('inventori', 'rekap_backup.inventori_id', '=', 'inventori.id')
                ->join('departemen', 'inventori.departemen_id', '=', 'departemen.id')
                ->where('departemen.perusahaan_id', $perusahaanId)
                ->whereRaw("DATE_FORMAT(rekap_backup.periode,'%b-%Y') = ?", [$p])
                ->select(
                    'departemen.nama_departemen as departemen',
                    'inventori.hostname',
                    'inventori.username',
                    'inventori.email',
                    'rekap_backup.size_data',
                    'rekap_backup.size_email'
                )
                ->get()
                ->map(function($row) {
                    return [
                        'departemen' => $row->departemen,
                        'hostname'   => $row->hostname,
                        'username'   => $row->username,
                        'email'      => $row->email,
                        'size_data'  => $row->size_data,
                        'size_email' => $row->size_email,
                    ];
                })
                ->toArray();

            $detailPeriodes[$p] = $rekapDetail;


        }

        return Excel::download(
            new RekapPerusahaanMultiExport($globalPivot, $detailPeriodes, $perusahaanNama),
            "laporan_perusahaan_{$perusahaanNama}.xlsx"
        );
    }

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
