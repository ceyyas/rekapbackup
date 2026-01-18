<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\KomputerController;
use App\Http\Controllers\LaptopController;
use App\Http\Controllers\McpController;

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

Route::get('/departemen', [DepartemenController::class, 'index'])
    ->name('departemen.index');
Route::resource('departemen', DepartemenController::class);

Route::get('/komputer', [KomputerController::class, 'index'])
        ->name('komputer.index');
Route::resource('komputer', KomputerController::class);

Route::get('/laptop', [LaptopController::class, 'index'])
        ->name('laptop.index');
Route::resource('laptop', LaptopController::class);

Route::get('/mcp', [McpController::class, 'index'])
        ->name('mcp.index');

// LOGOUT
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');
