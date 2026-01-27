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

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            Periode::firstOrCreate([
                'bulan' => $bulan,
                'tahun' => $request->tahun
            ]);
        }

        return redirect()->route('periode.index')
            ->with('success', 'Periode berhasil dibuat');
    }

    public function edit(string $id)
    {
        $periodes = Periode::orderBy('tahun')
            ->orderBy('bulan')
            ->get();
        
        return view('periode.edit', compact('periode'));
    }

    public function update(Request $request, string $id)
    {
        $periodes = Periode::orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        $request->validate([

        ]);
    }
}
