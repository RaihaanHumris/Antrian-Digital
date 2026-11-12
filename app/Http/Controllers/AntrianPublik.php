<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AntrianPublik extends Controller
{
    /**
     * Menampilkan halaman utama monitor antrian untuk pengunjung.
     */
    public function halamanUtama()
    {
        return view('publik.layar_antrian');
    }

    /**
     * Mengirimkan data antrian terbaru dalam bentuk JSON.
     * Fungsi ini dipanggil otomatis setiap 2 detik oleh JavaScript (Realtime tanpa refresh).
     */
    public function ambilDataRealtime(): JsonResponse
    {
        $hariIni = now()->toDateString();

        // 1. Cari 1 nomor antrian yang sedang berstatus 'dipanggil' hari ini
        $antrianSekarang = Antrian::with('petugasPelayan:id,name,nama_loket')
            ->where('tanggal_antrian', $hariIni)
            ->where('status', 'dipanggil')
            ->orderByDesc('waktu_dipanggil')
            ->first();

        // 2. Ambil seluruh daftar antrian hari ini diurutkan dari nomor urut terkecil
        $daftarAntrian = Antrian::where('tanggal_antrian', $hariIni)
            ->orderBy('nomor_urut', 'asc')
            ->get([
                'id',
                'nomor_urut',
                'kode_antrian',
                'nama_pasien',
                'jenis_poli',
                'status',
                'waktu_dipanggil'
            ]);

        // 3. Hitung berapa antrian yang masih berstatus 'menunggu'
        $jumlahMenunggu = $daftarAntrian->where('status', 'menunggu')->count();

        return response()->json([
            'antrian_sekarang' => $antrianSekarang,
            'daftar_antrian'   => $daftarAntrian,
            'jumlah_menunggu'  => $jumlahMenunggu,
            'jam_server'       => now()->format('H:i:s'),
        ]);
    }

    /**
     * Fitur ketika pengunjung mengambil nomor antrian sendiri dari halaman depan.
     */
    public function ambilNomorMandiri(Request $request): JsonResponse
    {
        $hariIni = now()->toDateString();

        $antrianBaru = DB::transaction(function () use ($request, $hariIni) {
            // Cari angka nomor_urut paling besar hari ini, kalau belum ada mulai dari 0
            $nomorTerakhir = Antrian::where('tanggal_antrian', $hariIni)
                ->lockForUpdate()
                ->max('nomor_urut') ?? 0;

            $nomorBerikutnya = $nomorTerakhir + 1;

            return Antrian::create([
                'nomor_urut'      => $nomorBerikutnya,
                'kode_antrian'    => 'A-' . str_pad($nomorBerikutnya, 3, '0', STR_PAD_LEFT),
                'nama_pasien'     => $request->input('nama_pasien') ?: 'Pasien Umum',
                'jenis_poli'      => $request->input('jenis_poli', 'Poli Umum'),
                'status'          => 'menunggu',
                'tanggal_antrian' => $hariIni,
                'diinput_oleh'    => null, // Kosong karena pasien ambil sendiri di layar publik
            ]);
        });

        return response()->json([
            'pesan' => 'Berhasil mengambil nomor antrian',
            'data'  => $antrianBaru,
        ]);
    }
}