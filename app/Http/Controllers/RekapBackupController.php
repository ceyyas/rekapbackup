<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Departemen;
use App\Models\Perusahaan;
use App\Models\Inventori;
use App\Models\Periode;
use App\Models\RekapBackup;

class RekapBackupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index()
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $periodes = Periode::orderBy('tahun','desc')
            ->orderBy('bulan','desc')
            ->get();

        return view('rekap.index', compact('perusahaans','periodes'));
    }

    public function global(Request $request)
    {
        $departemens = DB::table('departemen')
            ->leftJoin('inventori', 'inventori.departemen_id', '=', 'departemen.id')
            ->leftJoin('rekap_backup', function ($join) use ($request) {
                $join->on('rekap_backup.inventori_id', '=', 'inventori.id');

                if ($request->periode_id) {
                    $join->where('rekap_backup.periode_id', $request->periode_id);
                }
            })
            ->where('departemen.perusahaan_id', $request->perusahaan_id)
            ->select(
                'departemen.id',
                'departemen.nama_departemen',
                DB::raw('COALESCE(SUM(rekap_backup.size_data + rekap_backup.size_email), 0) as total_size')
            )
            ->groupBy('departemen.id', 'departemen.nama_departemen')
            ->get();

        return view('rekap.partials.global', compact('departemens'))->render();
    }

    public function detail(Request $request, $departemenId)
    {
        $inventoris = DB::table('inventori')
            ->leftJoin('rekap_backup', function ($join) use ($request) {
                $join->on('rekap_backup.inventori_id', '=', 'inventori.id')
                     ->where('rekap_backup.periode', $request->periode);
            })
            ->where('inventori.departemen_id', $departemenId)
            ->select(
                'inventori.nama',
                DB::raw('COALESCE(rekap_backup.size_data, 0) as size_data')
            )
            ->get();

        return view('rekap.partials.detail', compact('inventoris'))->render();
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
        //
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
