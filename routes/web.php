<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RekapPerkaraController;
use App\Http\Controllers\PihakController;
use App\Http\Controllers\PenilaianKinerjaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\AdminAboveMiddleware;
use App\Http\Middleware\NonHakimMiddleware;

// ================================================
// AUTH — guest only
// ================================================
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ================================================
// SEMUA HALAMAN — wajib login
// ================================================
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Rekap Perkara
    Route::prefix('rekap-perkara')->name('rekap-perkara.')->group(function () {
        Route::get('/',       [RekapPerkaraController::class, 'index'])->name('index');
        Route::get('/detail', [RekapPerkaraController::class, 'detail'])->name('detail');

        Route::middleware(NonHakimMiddleware::class)->group(function () {
            Route::get('/export-pdf',   [RekapPerkaraController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export-excel', [RekapPerkaraController::class, 'exportExcel'])->name('export.excel');
        });
    });

    // Data Pihak — super_admin + admin only
    Route::prefix('pihak')->name('pihak.')->middleware(AdminAboveMiddleware::class)->group(function () {
        Route::get('/',          [PihakController::class, 'index'])->name('index');
        Route::get('/{id}/edit', [PihakController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [PihakController::class, 'update'])->name('update');
    });

    // Penilaian Kinerja — semua kecuali hakim
    Route::prefix('penilaian-kinerja')->name('penilaian-kinerja.')->middleware(NonHakimMiddleware::class)->group(function () {
        Route::get('/',       [PenilaianKinerjaController::class, 'index'])->name('index');
        Route::get('/detail', [PenilaianKinerjaController::class, 'detail'])->name('detail');
    });

    // Manajemen User
    Route::prefix('users')->name('users.')->middleware(AdminAboveMiddleware::class)->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');

        Route::middleware(SuperAdminMiddleware::class)->group(function () {
            Route::get('/create',                [UserController::class, 'create'])->name('create');
            Route::post('/',                     [UserController::class, 'store'])->name('store');
            Route::get('/{id}/edit',             [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}',                  [UserController::class, 'update'])->name('update');
            Route::delete('/{id}',               [UserController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        });
    });

    // Log Aktivitas — super_admin only
    Route::prefix('log')->name('log.')->middleware(SuperAdminMiddleware::class)->group(function () {
        Route::get('/', [LogAktivitasController::class, 'index'])->name('index');
    });

});