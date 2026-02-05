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
                            THEN 'completed'
                        ELSE 'partial'
                    END AS status_backup
                "),
                DB::raw("
                    CASE
                        WHEN COUNT(rekap_backup.id) = 0 
                            THEN 'data belum di backup'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) < COUNT(rekap_backup.id) 
                            THEN 'proses backup'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) = COUNT(rekap_backup.id) 
                            AND (COALESCE(SUM(rekap_backup.jumlah_cd700),0) = 0 
                                AND COALESCE(SUM(rekap_backup.jumlah_dvd47),0) = 0 
                                AND COALESCE(SUM(rekap_backup.jumlah_dvd85),0) = 0)
                            THEN 'file di main folder aman'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) = COUNT(rekap_backup.id) 
                            AND (COALESCE(SUM(rekap_backup.jumlah_cd700),0) > 0 
                                OR COALESCE(SUM(rekap_backup.jumlah_dvd47),0) > 0 
                                OR COALESCE(SUM(rekap_backup.jumlah_dvd85),0) > 0)
                            THEN 'file di cd aman'
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

            $departemens = DB::table('departemen')
                ->leftJoin('inventori', 'inventori.departemen_id', '=', 'departemen.id')
                ->leftJoin('rekap_backup', function ($join) use ($periode) {
                    $join->on('rekap_backup.inventori_id', '=', 'inventori.id')
                        ->where('rekap_backup.periode', $periode);
                })
                ->where('departemen.perusahaan_id', $request->perusahaan_id)
                ->select(
                    'departemen.id',
                    'departemen.nama_departemen',
                    'inventori.id as inventori_id',
                    DB::raw('COALESCE(SUM(rekap_backup.size_data), 0) AS size_data'),
                    DB::raw('COALESCE(SUM(rekap_backup.size_email), 0) AS size_email'),
                    DB::raw('COALESCE(SUM(rekap_backup.size_data + rekap_backup.size_email), 0) AS total_size'),
                    DB::raw('COALESCE(SUM(rekap_backup.jumlah_cd700), 0) AS jumlah_cd700'),
                    DB::raw('COALESCE(SUM(rekap_backup.jumlah_dvd47), 0) AS jumlah_dvd47'),
                    DB::raw('COALESCE(SUM(rekap_backup.jumlah_dvd85), 0) AS jumlah_dvd85'),
                    DB::raw("
                        CASE
                            WHEN COUNT(rekap_backup.id) = 0 THEN 'pending'
                            WHEN SUM(rekap_backup.status = 'completed') = COUNT(rekap_backup.id)
                                THEN 'completed'
                            ELSE 'partial'
                        END AS status_backup
                    ")
                )
                ->groupBy('departemen.id','departemen.nama_departemen')
                ->orderBy('departemen.nama_departemen')
                ->get();

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


    public function detailPage($departemenId)
    {
        $departemen = Departemen::findOrFail($departemenId);

        return view('rekap.detail-page', compact('departemen'));
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
        if (!$request->filled(['perusahaan_id', 'periode_id'])) {
            return response()->json([]);
        }

        $request->validate([
            'periode_id' => 'date_format:Y-m',
            'perusahaan_id' => 'required|exists:perusahaan,id'
        ]);

        $periode = $request->periode_id . '-01';
        $departemens = $this->getDepartemenQuery($request->perusahaan_id, $periode)->get();

        return Excel::download(new RekapExport($departemens), 'rekap_backup_' . now()->format('Ymd_His') . '.xlsx');
    }
 }
