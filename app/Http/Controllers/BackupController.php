<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use App\Models\Perusahaan;
use App\Models\Inventori;
use App\Models\Periode;
use App\Models\RekapBackup;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function detailKomputer($perusahaanId, $departemenId, Request $request)
    {
        $periodeId = $request->periode_id;

        $inventori = Inventori::where('perusahaan_id', $perusahaanId)
            ->where('departemen_id', $departemenId)
            ->get();

        return view('backup.detail-komputer', compact(
            'inventori', 'perusahaanId', 'departemenId', 'periodeId'
        ));
    }

    public function rekapDepartemen($perusahaanId, Request $request)
    {
        $periodeId = $request->periode_id;

        $data = RekapBackup::select(
                'departemen_id',
                DB::raw('SUM(size_data) as total_data'),
                DB::raw('SUM(size_email) as total_email')
            )
            ->where('perusahaan_id', $perusahaanId)
            ->where('periode_id', $periodeId)
            ->groupBy('departemen_id')
            ->with('departemen')
            ->get();

        return view('mcp.rekap-departemen', compact(
            'data', 'perusahaanId', 'periodeId'
        ));
    }


    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        foreach ($request->backup as $inventoriId => $data) {
            RekapBackup::updateOrCreate(
                [
                    'inventori_id' => $inventoriId,
                    'periode_id'   => $request->periode_id,
                ],
                [
                    'perusahaan_id' => $request->perusahaan_id,
                    'departemen_id' => $request->departemen_id,
                    'size_data'     => $data['size_data'] ?? 0,
                    'size_email'    => $data['size_email'] ?? 0,
                    'status'        => 'completed'
                ]
            );
        }

        return redirect()->back()->with('success', 'Backup berhasil disimpan');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
