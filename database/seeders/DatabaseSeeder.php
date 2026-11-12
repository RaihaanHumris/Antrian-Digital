<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data awal akun Administrator dan contoh antrian pasien hari ini.
     */
    public function run(): void
    {
        // 1. Bersihkan data lama dengan aman
        Schema::disableForeignKeyConstraints();
        DB::table('antrians')->truncate();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Buat 1 Akun Administrator Utama
        $adminId = DB::table('users')->insertGetId([
            'name'       => 'Admin Klinik',
            'email'      => 'admin@medika.id',
            'password'   => Hash::make('password123'),
            'peran'      => 'admin',
            'nama_loket' => 'Loket Utama',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Buat Data Antrian Awal untuk Hari Ini
        $hariIni = now()->toDateString();
        $daftarPasien = [
            ['nama' => 'Ahmad Fauzi', 'poli' => 'Poli Umum'],
            ['nama' => 'Ratna Sari', 'poli' => 'Poli Gigi'],
            ['nama' => 'Dedi Kurniawan', 'poli' => 'Poli Umum'],
        ];

        foreach ($daftarPasien as $index => $pasien) {
            $urut = $index + 1;

            DB::table('antrians')->insert([
                'nomor_urut'      => $urut,
                'kode_antrian'    => 'A-' . str_pad($urut, 3, '0', STR_PAD_LEFT),
                'nama_pasien'     => $pasien['nama'],
                'jenis_poli'      => $pasien['poli'],
                'status'          => $urut === 1 ? 'dipanggil' : 'menunggu',
                'tanggal_antrian' => $hariIni,
                'diinput_oleh'    => $adminId,
                'dilayani_oleh'   => $urut === 1 ? $adminId : null,
                'waktu_dipanggil' => $urut === 1 ? now() : null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
