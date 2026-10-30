<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\Auth\LoginController as AuthLoginController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/produk');

// Hanya untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});

// Hanya untuk yang sudah login (Kasir & Owner)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Katalog Produk (Akses Kasir & Owner)
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');

    // POS Kasir (Akses Kasir & Owner)
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/cari-barang', [PosController::class, 'cariBarang'])->name('pos.cari-barang');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/sync-offline', [PosController::class, 'syncOffline'])->name('pos.sync-offline');

    // Pelanggan & Kasbon (Akses Kasir & Owner)
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');
    Route::post('/pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store');
    Route::put('/pelanggan/{id}', [PelangganController::class, 'update'])->name('pelanggan.update');
    Route::post('/pelanggan/{id}/bayar-hutang', [PelangganController::class, 'bayarHutang'])->name('pelanggan.bayar-hutang');
    Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');

    // Khusus Owner (Peran: admin) - Manajemen Master Barang, Laporan & Karyawan
    Route::middleware('role:admin')->group(function () {
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::put('/produk/{id}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');

        // Laporan Keuangan, Omzet & Laba
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

        // Asisten Cerdas AI & Optimasi Inventaris
        Route::get('/ai', [AiController::class, 'index'])->name('ai.index');
        Route::post('/ai/refresh', [AiController::class, 'refreshInsights'])->name('ai.refresh');
        Route::post('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');

        // Manajemen Staf & Karyawan
        Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
        Route::post('/karyawan', [KaryawanController::class, 'store'])->name('karyawan.store');
        Route::put('/karyawan/{id}', [KaryawanController::class, 'update'])->name('karyawan.update');
        Route::delete('/karyawan/{id}', [KaryawanController::class, 'destroy'])->name('karyawan.destroy');
    });
});
