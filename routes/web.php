<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntrianPublik;
use App\Http\Controllers\KelolaAntrian;

// 1. Rute Halaman Publik (Pengunjung & Layar TV Monitor)
Route::get('/', [AntrianPublik::class, 'halamanUtama'])->name('publik.utama');
Route::get('/api/data-antrian', [AntrianPublik::class, 'ambilDataRealtime'])->name('publik.data');
Route::post('/api/ambil-antrian', [AntrianPublik::class, 'ambilNomorMandiri'])->name('publik.ambil');

// 2. Rute Gambar Aset Khusus (Background Login Grid)
Route::get('/images/bg-login.png', function () {
    $source = 'C:/Users/Reyhan Humris/.gemini/antigravity/brain/c4239e81-7a9c-4fa3-862c-9da3c1ad1125/.user_uploaded/media_1794532606032_871a9b9f.png';
    $dest = public_path('images/bg-login.png');

    // Otomatis salin ke folder public/images agar bisa disajikan secara permanen
    if (!file_exists($dest) && file_exists($source)) {
        if (!is_dir(public_path('images'))) {
            mkdir(public_path('images'), 0777, true);
        }
        copy($source, $dest);
    }

    if (file_exists($dest)) {
        return response()->file($dest, ['Content-Type' => 'image/png']);
    }
    if (file_exists($source)) {
        return response()->file($source, ['Content-Type' => 'image/png']);
    }

    abort(404);
})->name('gambar.bg-login');

// 3. Rute Login & Logout Admin
Route::middleware('guest')->group(function () {
    Route::get('/login', [KelolaAntrian::class, 'halamanLogin'])->name('login');
    Route::post('/login', [KelolaAntrian::class, 'prosesLogin'])->name('login.proses');
});
Route::post('/logout', [KelolaAntrian::class, 'keluar'])->middleware('auth')->name('logout');

// 4. Rute Panel Kendali Antrian (Khusus Administrator)
Route::prefix('panel')->name('panel.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [KelolaAntrian::class, 'tampilDashboard'])->name('dashboard');
    Route::post('/antrian/tambah', [KelolaAntrian::class, 'tambahAntrianManual'])->name('antrian.tambah');
    Route::post('/antrian/selanjutnya', [KelolaAntrian::class, 'panggilSelanjutnya'])->name('antrian.selanjutnya');
    Route::post('/antrian/sebelumnya', [KelolaAntrian::class, 'panggilSebelumnya'])->name('antrian.sebelumnya');
    
    // Rute tombol 'Selesai' untuk menyelesaikan pelayanan antrian aktif
    Route::match(['patch', 'post'], '/antrian/{id}/selesai', [KelolaAntrian::class, 'selesaikanAntrian'])->name('antrian.selesai');

    // Rute tombol Lewati (Skip) dan Hapus
    Route::match(['patch', 'post'], '/antrian/{id}/lewati', [KelolaAntrian::class, 'lewatiAntrian'])->name('antrian.lewati');
    Route::match(['delete', 'post'], '/antrian/{id}/hapus', [KelolaAntrian::class, 'hapusAntrian'])->name('antrian.hapus');
});
