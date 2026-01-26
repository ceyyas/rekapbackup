<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\KomputerController;
use App\Http\Controllers\LaptopController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\RekapBackupController;
use App\Http\Controllers\PeriodeBackupController;

Route::get('/', function () {
    return redirect('/login');
});

// LOGIN (hanya untuk guest)
Route::get('/login', [LoginController::class, 'view'])
    ->middleware('guest');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest')
    ->name('login');

// DASHBOARD (harus login)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::resource('departemen', DepartemenController::class);
Route::resource('komputer', KomputerController::class);
Route::resource('laptop', LaptopController::class);
Route::resource('stok', StokController::class);

Route::get('/rekap-backup', [RekapBackupController::class, 'index']);
Route::get('/rekap-backup/global', [RekapBackupController::class, 'global']);
Route::get('/rekap-backup/detail/{departemen}', [RekapBackupController::class, 'detail']);

Route::post('/periode/generate',
    [PeriodeBackupController::class, 'generateTahun']
)->name('periode.generate');



// LOGOUT
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');
