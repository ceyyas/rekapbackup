<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\KomputerController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\RekapBackupController;

Route::get('/', function () {
    return redirect()->route('login');
});

// LOGIN (hanya untuk guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'view'])->name('login.view');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
});

// Semua route yang butuh login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/komputer/data', [KomputerController::class, 'data'])->name('komputer.data');
    Route::get('/departemen/by-perusahaan', [DepartemenController::class, 'byPerusahaan']);
    Route::resource('departemen', DepartemenController::class);
    Route::resource('komputer', KomputerController::class);
   
    
    Route::resource('stok', StokController::class);

    // route rekap backup
    Route::get('/rekap-backup', [RekapBackupController::class, 'index'])->name('rekap-backup.index');
    Route::get('/rekap-backup/departemen/{departemen}', [RekapBackupController::class, 'detailPage'])->name('rekap-backup.detail-page');
    Route::get('/rekap-backup/departemen/{departemen}/data', [RekapBackupController::class, 'detailData'])->name('rekap-backup.detail-data');
    Route::post('/rekap-backup/save', [RekapBackupController::class, 'saveDetail'])->name('rekap-backup.save');
    Route::post('/rekap-backup/auto-save', [RekapBackupController::class, 'autoSave'])->name('rekap.autoSave');
    Route::get('/rekap-backup/filter', [RekapBackupController::class, 'filter'])->name('rekap.filter');

    // export data
    Route::get('/rekap-backup/export', [RekapBackupController::class, 'export'])->name('rekap-backup.export');

    // laporan perusahaan
    Route::get('/laporan/perusahaan', [RekapBackupController::class, 'laporanperusahaan'])
    ->name('rekap-backup.laporan-perusahaan');
    Route::get('/laporan/perusahaan/pivot', [RekapBackupController::class, 'laporanPerusahaanPivot'])
    ->name('rekap-backup.laporan-perusahaan-pivot');
    Route::get('/laporan/perusahaan/export', [RekapBackupController::class, 'exportPerusahaan'])
    ->name('rekap-backup.export-perusahaan');

    // laporan bulanan
    Route::get('/laporan/bulanan', [RekapBackupController::class, 'laporanbulanan'])
    ->name('rekap-backup.laporan-bulanan');
    Route::get('/laporan/bulanan/data', [RekapBackupController::class, 'laporanbulanandata'])
    ->name('laporan-bulanan.data');
    Route::get('/laporan/bulanan/export', [RekapBackupController::class, 'exportBulanan'])
    ->name('rekap-backup.export-bulanan');

    // input penggunaan CD DVD
    Route::get('/rekap-backup/cd-dvd', [RekapBackupController::class, 'cdDvd'])->name('rekap-backup.cd-dvd');
    Route::get('/laporanburning/export', [RekapBackupController::class, 'exportBurning'])
    ->name('rekap-backup.export-burning');

    // LOGOUT
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

});
