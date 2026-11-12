<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan sementara pengecekan foreign key agar bisa dibersihkan
        Schema::disableForeignKeyConstraints();
        DB::table('antrians')->truncate();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Buat 1 Akun Admin Saja
        $dataAdmin = [
            'name'       => 'Admin Klinik',
            'email'      => 'admin@medik.id',
            'password'   => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Cek otomatis: jika di tabel users Anda ada kolom 'peran' dan 'nama_loket', ikut diisi
        if (Schema::hasColumn('users', 'peran')) {
            $dataAdmin['peran'] = 'admin';
        }
        if (Schema::hasColumn('users', 'nama_loket')) {
            $dataAdmin['nama_loket'] = 'Loket Utama';
        }

        $adminId = DB::table('users')->insertGetId($dataAdmin);

        // 2. Buat 3 Data Antrian Awal untuk Hari Ini
        $hariIni = now()->toDateString();
        $daftarPasien = [
            ['nama' => 'Ahmad Fauzi', 'poli' => 'Poli Umum'],
            ['nama' => 'Ratna Sari', 'poli' => 'Poli Gigi'],
            ['nama' => 'Dedi Kurniawan', 'poli' => 'Poli Umum'],
        ];

        foreach ($daftarPasien as $index => $pasien) {
            $urut = $index + 1;

            $dataAntrian = [
                'nomor_urut'      => $urut,
                'kode_antrian'    => 'A-' . str_pad($urut, 3, '0', STR_PAD_LEFT),
                'nama_pasien'     => $pasien['nama'],
                'status'          => $urut === 1 ? 'dipanggil' : 'menunggu',
                'tanggal_antrian' => $hariIni,
                'waktu_dipanggil' => $urut === 1 ? now() : null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            // Cek otomatis ketersediaan kolom opsional di tabel antrians
            if (Schema::hasColumn('antrians', 'jenis_poli')) {
                $dataAntrian['jenis_poli'] = $pasien['poli'];
            }
            if (Schema::hasColumn('antrians', 'diinput_oleh')) {
                $dataAntrian['diinput_oleh'] = $adminId;
            }
            if (Schema::hasColumn('antrians', 'dilayani_oleh')) {
                $dataAntrian['dilayani_oleh'] = $urut === 1 ? $adminId : null;
            }

            DB::table('antrians')->insert($dataAntrian);
        }
    }
}