<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

use App\Http\Controllers\TransaksiController;

Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');

Route::get('/pelanggan', function () {
    return view('pelanggan.index');
})->name('pelanggan.index');

use App\Http\Controllers\PegawaiController;

Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');

use App\Http\Controllers\AbsensiController;
Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');

Route::get('/laporan', function () {
    return view('laporan.index');
})->name('laporan.index');

use App\Http\Controllers\PengeluaranController;

Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
