<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntrianPublik;
use App\Http\Controllers\KelolaAntrian;

// 1. Rute Halaman Publik (Pengunjung)
Route::get('/', [AntrianPublik::class, 'halamanUtama'])->name('publik.utama');
Route::get('/api/data-antrian', [AntrianPublik::class, 'ambilDataRealtime'])->name('publik.data');
Route::post('/api/ambil-antrian', [AntrianPublik::class, 'ambilNomorMandiri'])->name('publik.ambil');

// 2. Rute Login & Logout Admin
Route::middleware('guest')->group(function () {
    Route::get('/login', [KelolaAntrian::class, 'halamanLogin'])->name('login');
    Route::post('/login', [KelolaAntrian::class, 'prosesLogin'])->name('login.proses');
});
Route::post('/logout', [KelolaAntrian::class, 'keluar'])->middleware('auth')->name('logout');

// 3. Rute Panel Admin (Menggunakan {id} agar tidak terkena 404 Route Model Binding)
Route::prefix('panel')->name('panel.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [KelolaAntrian::class, 'tampilDashboard'])->name('dashboard');
    Route::post('/antrian/tambah', [KelolaAntrian::class, 'tambahAntrianManual'])->name('antrian.tambah');
    Route::post('/antrian/selanjutnya', [KelolaAntrian::class, 'panggilSelanjutnya'])->name('antrian.selanjutnya');
    Route::post('/antrian/sebelumnya', [KelolaAntrian::class, 'panggilSebelumnya'])->name('antrian.sebelumnya');
    
    // Gunakan {id} dan dukung method PATCH/POST serta DELETE/POST
    Route::match(['patch', 'post'], '/antrian/{id}/lewati', [KelolaAntrian::class, 'lewatiAntrian'])->name('antrian.lewati');
    Route::match(['delete', 'post'], '/antrian/{id}/hapus', [KelolaAntrian::class, 'hapusAntrian'])->name('antrian.hapus');
});