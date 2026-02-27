<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Perusahaan;
use App\Models\Departemen;
use App\Models\Inventori;
use App\Models\InventoriHistory;
use App\Models\RekapBackup;
use App\Models\Stok;
use App\Traits\RekapBackupTrait;
use App\Exports\RekapExport;
use App\Exports\CdDvdExport;

use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

class RekapBackupController extends Controller
{
    use RekapBackupTrait;
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
        $inventoris = $this->getInventoriDetailQuery($departemenId, $periode)->get();

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

        // Rekap per departemen
        $rekapDepartemen = $this->getDepartemenQuery($perusahaanId, $periode)->get();

        // Detail inventori per departemen 
        $rekapDetail = Departemen::where('perusahaan_id', $perusahaanId)
            ->get()
            ->map(function($dept) use ($periode) {
                $dept->detail_inventori = $this->getInventoriDetailQuery($dept->id, $periode)->get();
                return $dept;
            });

        $periodeFormat = \Carbon\Carbon::parse($periode)->translatedFormat('F Y');
        $fileName = "rekap_backup_{$perusahaan}_{$periodeFormat}.xlsx";

        return Excel::download(
            new RekapExport($rekapDepartemen, $rekapDetail, $perusahaan, $periode),
            $fileName
        );
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

        $oldCd700  = $rekap->jumlah_cd700 ?? 0;
        $oldDvd47  = $rekap->jumlah_dvd47 ?? 0;
        $oldDvd85  = $rekap->jumlah_dvd85 ?? 0;

        $newCd700  = $request->cd700 !== null ? (int) $request->cd700 : $oldCd700;
        $diffCd700 = $newCd700 - $oldCd700;

        $newDvd47  = $request->dvd47 !== null ? (int) $request->dvd47 : $oldDvd47;
        $diffDvd47 = $newDvd47 - $oldDvd47;

        $newDvd85  = $request->dvd85 !== null ? (int) $request->dvd85 : $oldDvd85;
        $diffDvd85 = $newDvd85 - $oldDvd85;

        $tambahPemakaian = function($namaBarang, $jumlahInput) {
            $stokList = Stok::where('nama_barang', $namaBarang)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($stokList as $stok) {
                if ($jumlahInput <= 0) break;

                $tersisa = $stok->tersisa;

                if ($tersisa >= $jumlahInput) {
                    $stok->pemakaian += $jumlahInput;
                    $stok->save();
                    $jumlahInput = 0;
                } else {
                    $stok->pemakaian += $tersisa;
                    $stok->save();
                    $jumlahInput -= $tersisa;
                }
            }

            return $jumlahInput;
        };

        $kurangiPemakaian = function($namaBarang, $jumlahKurang) {
            $stokList = Stok::where('nama_barang', $namaBarang)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($stokList as $stok) {
                if ($jumlahKurang <= 0) break;

                $pemakaian = $stok->pemakaian;

                if ($pemakaian >= $jumlahKurang) {
                    $stok->pemakaian -= $jumlahKurang;
                    $stok->save();
                    $jumlahKurang = 0;
                } else {
                    $jumlahKurang -= $pemakaian;
                    $stok->pemakaian = 0;
                    $stok->save();
                }
            }
        };

        // CD 700 MB
        if ($diffCd700 > 0) {
            $sisa = $tambahPemakaian('CD 700 MB', $diffCd700);
            if ($sisa > 0) return response()->json(['error' => 'Stok CD 700 MB tidak mencukupi'], 422);
        } elseif ($diffCd700 < 0) {
            $kurangiPemakaian('CD 700 MB', abs($diffCd700));
        }

        // DVD 4.7 GB
        if ($diffDvd47 > 0) {
            $sisa = $tambahPemakaian('DVD 4.7 GB', $diffDvd47);
            if ($sisa > 0) return response()->json(['error' => 'Stok DVD 4.7 GB tidak mencukupi'], 422);
        } elseif ($diffDvd47 < 0) {
            $kurangiPemakaian('DVD 4.7 GB', abs($diffDvd47));
        }

        // DVD 8.5 GB
        if ($diffDvd85 > 0) {
            $sisa = $tambahPemakaian('DVD 8.5 GB', $diffDvd85);
            if ($sisa > 0) return response()->json(['error' => 'Stok DVD 8.5 GB tidak mencukupi'], 422);
        } elseif ($diffDvd85 < 0) {
            $kurangiPemakaian('DVD 8.5 GB', abs($diffDvd85));
        }

        // Update nilai rekap
        $rekap->jumlah_cd700 = $newCd700;
        $rekap->jumlah_dvd47 = $newDvd47;
        $rekap->jumlah_dvd85 = $newDvd85;
        $rekap->save();

        return response()->json(['success' => true]);
    }

    private function getCdDvdQuery($perusahaanId, $periode)
    {
        return Departemen::query()
            ->where('departemen.perusahaan_id', $perusahaanId)
            ->leftJoin('inventori', 'inventori.departemen_id', '=', 'departemen.id')
            ->leftJoin('rekap_backup', function ($join) use ($periode) {
                $join->on('rekap_backup.inventori_id', '=', 'inventori.id')
                    ->where('rekap_backup.periode', $periode);
            })
            ->select('departemen.id','departemen.nama_departemen')
            ->selectRaw('COALESCE(SUM(rekap_backup.size_data),0) as size_data')
            ->selectRaw('COALESCE(SUM(rekap_backup.size_email),0) as size_email')
            ->selectRaw('COALESCE(SUM(rekap_backup.size_data + rekap_backup.size_email),0) as total_size')
            ->selectRaw('COALESCE(SUM(rekap_backup.jumlah_cd700),0) as total_cd700')
            ->selectRaw('COALESCE(SUM(rekap_backup.jumlah_dvd47),0) as total_dvd47')
            ->selectRaw('COALESCE(SUM(rekap_backup.jumlah_dvd85),0) as total_dvd85')
            ->groupBy('departemen.id','departemen.nama_departemen');
    }

    public function exportBurning(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|date_format:Y-m',
            'perusahaan_id' => 'required|exists:perusahaan,id'
        ]);

        $perusahaanId = $request->input('perusahaan_id'); 
        $periode = $request->periode_id . '-01'; 
        $perusahaan = Perusahaan::find($perusahaanId)->nama_perusahaan;
        $rekapCdDvd = $this->getCdDvdQuery($perusahaanId, $periode)->get();

        $periodeFormat = \Carbon\Carbon::parse($periode)->translatedFormat('F_Y'); 
        $fileName = "penggunaan_cd_dvd_{$perusahaan}_{$periodeFormat}.xlsx";

        return Excel::download(
            new CdDvdExport($periode, $perusahaan, $rekapCdDvd),
            $fileName
        );
    }

}
