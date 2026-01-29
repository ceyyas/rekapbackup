<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periode;

class PeriodeBackupController extends Controller
{
    public function index()
    {
        $periodes = Periode::orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        return view('periode.index', compact('periodes'));
    }

    public function generateTahun(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4'
        ]);

        $bulanMap = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        foreach ($bulanMap as $bulan => $namaBulan) {
            Periode::firstOrCreate(
                ['bulan' => $bulan, 'tahun' => $request->tahun],
                ['nama_bulan' => $namaBulan]
            );

        }

        return redirect()
            ->route('periode.index')
            ->with('success', 'Periode berhasil dibuat');
    }
    
}
