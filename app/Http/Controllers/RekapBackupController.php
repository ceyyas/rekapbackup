<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Perusahaan;
use App\Models\Departemen;
use App\Models\Inventori;
use App\Models\RekapBackup;
use App\Exports\RekapExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapBackupController extends Controller
{
    public function index(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = collect();

        if ($request->filled(['perusahaan_id', 'periode_id'])) {
            // konversi ke tanggal awal bulan
            $periode = $request->periode_id . '-01';

            $$departemens = DB::table('departemen')
                ->leftJoin('inventori', 'inventori.departemen_id', '=', 'departemen.id')
                ->leftJoin('rekap_backup', function ($join) use ($periode) {
                    $join->on('rekap_backup.inventori_id', '=', 'inventori.id')
                        ->where('rekap_backup.periode', $periode);
                })
                ->where('departemen.perusahaan_id', $request->perusahaan_id)
                ->select(
                    'departemen.id',
                    'departemen.nama_departemen',
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
                ->groupBy('departemen.id', 'departemen.nama_departemen')
                ->orderBy('departemen.nama_departemen')
                ->get();

        }

        return view('rekap.index', compact('perusahaans', 'departemens'));
    }

    public function autoSave(Request $request)
    {
        // Pastikan departemen_id dikirim
        if (!$request->departemen_id) {
            return response()->json(['success' => false, 'message' => 'departemen_id kosong'], 400);
        }

        // Cari departemen
        $departemen = \App\Models\Departemen::find($request->departemen_id);
        if (!$departemen) {
            return response()->json(['success' => false, 'message' => 'Departemen tidak ditemukan'], 404);
        }

        // Update atau buat record rekapbackup
        \App\Models\RekapBackup::updateOrCreate(
            [
                'departemen_id' => $departemen->id,
                'periode_id'    => $request->periode_id,
                'perusahaan_id' => $request->perusahaan_id,
            ],
            [
                'cd700' => $request->cd700,
                'dvd47' => $request->dvd47,
                'dvd85' => $request->dvd85,
            ]
        );

        return response()->json(['success' => true]);
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
            ->groupBy('departemen.id', 'departemen.nama_departemen')
            ->orderBy('departemen.nama_departemen')
            ->get();

        return response()->json($departemens);
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
            return redirect()->back()->with('error', 'Filter belum lengkap');
        }

        $departemens = DB::table('departemen')
            ->leftJoin('inventori', 'inventori.departemen_id', '=', 'departemen.id')
            ->leftJoin('rekap_backup', function ($join) use ($request) {
                $join->on('rekap_backup.inventori_id', '=', 'inventori.id')
                    ->where('rekap_backup.periode_id', $request->periode_id);
            })
            ->where('departemen.perusahaan_id', $request->perusahaan_id)
            ->select(
                'departemen.nama_departemen',

                DB::raw('COALESCE(SUM(rekap_backup.size_data), 0) AS size_data'),
                DB::raw('COALESCE(SUM(rekap_backup.size_email), 0) AS size_email'),
                DB::raw('COALESCE(SUM(rekap_backup.size_data + rekap_backup.size_email), 0) AS total_size'),

                DB::raw("
                    CASE
                        WHEN COUNT(rekap_backup.id) = 0 THEN 'pending'
                        WHEN SUM(rekap_backup.status = 'completed') = COUNT(rekap_backup.id)
                            THEN 'completed'
                        ELSE 'partial'
                    END AS status_backup
                ")
            )
            ->groupBy('departemen.id', 'departemen.nama_departemen')
            ->orderBy('departemen.nama_departemen')
            ->get();

        return Excel::download(
            new RekapExport($departemens),
            'rekap_backup_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
 }
