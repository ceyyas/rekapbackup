<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventori;
use App\Models\RekapBackup;

class DashboardController extends Controller
{
    public function index()
    {
        // Ringkasan inventori
        $totalKomputer = Inventori::where('kategori', 'PC')->count();
        $totalLaptop   = Inventori::where('kategori', 'Laptop')->count();

        // Rekap backup per bulan
        $backupPerBulan = RekapBackup::selectRaw('periode_id, COUNT(*) as total')
            ->groupBy('periode_id')
            ->with('periode') // relasi ke tabel periode_backup
            ->get();

        return view('dashboard', compact('totalKomputer', 'totalLaptop', 'backupPerBulan'));
}

}
