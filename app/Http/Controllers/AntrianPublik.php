<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AntrianPublik extends Controller
{
    /**
     * Menampilkan halaman utama monitor antrian untuk pengunjung / display TV.
     * Mengirimkan data awal (initial data) agar saat pertama kali dibuka langsung tampil tanpa menunggu jeda AJAX.
     */
    public function halamanUtama()
    {
        $hariIni = now()->toDateString();

        // Cari nomor antrian yang saat ini berstatus 'dipanggil'
        $antrianSekarang = Antrian::where('status', 'dipanggil')
            ->orderByDesc('waktu_dipanggil')
            ->orderByDesc('id')
            ->first();

        // Ambil semua daftar antrian hari ini
        $daftarAntrian = Antrian::whereDate('tanggal_antrian', $hariIni)
            ->orderBy('nomor_urut', 'asc')
            ->get();

        // Fallback jika database belum ada filter tanggal hari ini
        if ($daftarAntrian->isEmpty()) {
            $daftarAntrian = Antrian::orderBy('nomor_urut', 'asc')->get();
        }

        $jumlahMenunggu = $daftarAntrian->where('status', 'menunggu')->count();

        return view('publik.layar_antrian', compact('antrianSekarang', 'daftarAntrian', 'jumlahMenunggu'));
    }

    /**
     * Mengambil data antrian terkini dalam format JSON.
     * Dipanggil otomatis tiap 2 detik oleh browser pengunjung (AJAX Polling Realtime).
     */
    public function ambilDataRealtime(): JsonResponse
    {
        $hariIni = now()->toDateString();

        // 1. Ambil nomor antrian yang saat ini aktif berstatus 'dipanggil'
        $antrianSekarang = Antrian::where('status', 'dipanggil')
            ->orderByDesc('waktu_dipanggil')
            ->orderByDesc('id')
            ->first();

        // 2. Ambil seluruh daftar antrian hari ini
        $daftarAntrian = Antrian::whereDate('tanggal_antrian', $hariIni)
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

        // Fallback jika belum ada filter tanggal yang sama persis
        if ($daftarAntrian->isEmpty()) {
            $daftarAntrian = Antrian::orderBy('nomor_urut', 'asc')->get([
                'id',
                'nomor_urut',
                'kode_antrian',
                'nama_pasien',
                'jenis_poli',
                'status',
                'waktu_dipanggil'
            ]);
        }

        // 3. Hitung jumlah antrian yang masih berstatus 'menunggu'
        $jumlahMenunggu = $daftarAntrian->where('status', 'menunggu')->count();

        return response()->json([
            'antrian_sekarang' => $antrianSekarang,
            'daftar_antrian'   => $daftarAntrian,
            'jumlah_menunggu'  => $jumlahMenunggu,
            'jam_server'       => now()->format('H:i:s'),
        ]);
    }

    /**
     * Fitur pendaftaran tiket mandiri oleh pengunjung / pasien dari layar publik atau HP.
     */
    public function ambilNomorMandiri(Request $request): JsonResponse
    {
        $hariIni = now()->toDateString();

        $antrianBaru = DB::transaction(function () use ($request, $hariIni) {
            $terakhir = Antrian::where('tanggal_antrian', $hariIni)
                ->orderByDesc('nomor_urut')
                ->lockForUpdate()
                ->first();

            $nomorBerikutnya = $terakhir ? ($terakhir->nomor_urut + 1) : 1;

            return Antrian::create([
                'nomor_urut'      => $nomorBerikutnya,
                'kode_antrian'    => 'A-' . str_pad($nomorBerikutnya, 3, '0', STR_PAD_LEFT),
                'nama_pasien'     => $request->input('nama_pasien') ?: 'Pasien Umum',
                'jenis_poli'      => $request->input('jenis_poli', 'Poli Umum'),
                'status'          => 'menunggu',
                'tanggal_antrian' => $hariIni,
                'diinput_oleh'    => null,
            ]);
        });

        return response()->json([
            'pesan' => 'Berhasil mengambil nomor antrian',
            'data'  => $antrianBaru,
        ]);
    }
}
