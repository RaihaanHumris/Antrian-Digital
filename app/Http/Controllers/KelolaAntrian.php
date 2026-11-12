<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KelolaAntrian extends Controller
{
    /**
     * 1. Menampilkan halaman form login untuk Admin & Petugas.
     */
    public function halamanLogin()
    {
        return view('auth.login');
    }

    /**
     * 2. Memproses autentikasi login Admin & Petugas.
     */
    public function prosesLogin(Request $request): RedirectResponse
    {
        $kredensial = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($kredensial, $request->boolean('ingat_saya'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('panel.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * 3. Memproses logout / keluar dari sesi.
     */
    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * 4. Menampilkan halaman dashboard untuk Admin dan Petugas.
     */
    public function tampilDashboard()
    {
        $hariIni = now()->toDateString();

        // Cari antrian yang sedang aktif dipanggil saat ini
        $antrianAktif = Antrian::where('tanggal_antrian', $hariIni)
            ->where('status', 'dipanggil')
            ->first();

        // Ambil semua daftar antrian hari ini beserta relasi petugasnya
        $daftarAntrian = Antrian::with(['petugasPelayan', 'petugasPenginput'])
            ->where('tanggal_antrian', $hariIni)
            ->orderBy('nomor_urut', 'asc')
            ->get();

        return view('admin.dashboard_antrian', compact('antrianAktif', 'daftarAntrian'));
    }

    /**
     * 5. Fitur Petugas/Admin untuk menambah/menginput antrian pasien baru.
     */
    public function tambahAntrianManual(Request $request): RedirectResponse
    {
        $dataValid = $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'jenis_poli'  => 'required|string|max:50',
        ]);

        $hariIni = now()->toDateString();

        DB::transaction(function () use ($dataValid, $hariIni) {
            $nomorTerakhir = Antrian::where('tanggal_antrian', $hariIni)
                ->lockForUpdate()
                ->max('nomor_urut') ?? 0;

            $nomorBaru = $nomorTerakhir + 1;

            Antrian::create([
                'nomor_urut'      => $nomorBaru,
                'kode_antrian'    => 'A-' . str_pad($nomorBaru, 3, '0', STR_PAD_LEFT),
                'nama_pasien'     => $dataValid['nama_pasien'],
                'jenis_poli'      => $dataValid['jenis_poli'],
                'status'          => 'menunggu',
                'tanggal_antrian' => $hariIni,
                'diinput_oleh'    => Auth::id(),
            ]);
        });

        return back()->with('pesan_sukses', 'Data antrian pasien berhasil ditambahkan.');
    }

    /**
     * 6. Tombol NEXT (Selanjutnya):
     * Menyelesaikan antrian yang sedang dipanggil, lalu memanggil nomor urut berikutnya.
     */
    public function panggilSelanjutnya(): RedirectResponse
    {
        $hariIni = now()->toDateString();

        DB::transaction(function () use ($hariIni) {
            $sedangDipanggil = Antrian::where('tanggal_antrian', $hariIni)
                ->where('status', 'dipanggil')
                ->first();

            if ($sedangDipanggil) {
                $sedangDipanggil->update(['status' => 'selesai']);

                $antrianBerikutnya = Antrian::where('tanggal_antrian', $hariIni)
                    ->where('nomor_urut', '>', $sedangDipanggil->nomor_urut)
                    ->whereIn('status', ['menunggu', 'dilewati'])
                    ->orderBy('nomor_urut', 'asc')
                    ->first();
            } else {
                $antrianBerikutnya = Antrian::where('tanggal_antrian', $hariIni)
                    ->where('status', 'menunggu')
                    ->orderBy('nomor_urut', 'asc')
                    ->first();
            }

            if ($antrianBerikutnya) {
                $antrianBerikutnya->update([
                    'status'          => 'dipanggil',
                    'dilayani_oleh'   => Auth::id(),
                    'waktu_dipanggil' => now(),
                ]);
            }
        });

        return back()->with('pesan_sukses', 'Berhasil memanggil nomor antrian selanjutnya.');
    }

    /**
     * 7. Tombol PREV (Sebelumnya):
     * Mengembalikan antrian saat ini menjadi 'menunggu', lalu memanggil ulang nomor sebelumnya.
     */
    public function panggilSebelumnya(): RedirectResponse
    {
        $hariIni = now()->toDateString();

        DB::transaction(function () use ($hariIni) {
            $sedangDipanggil = Antrian::where('tanggal_antrian', $hariIni)
                ->where('status', 'dipanggil')
                ->first();

            if ($sedangDipanggil) {
                $antrianSebelumnya = Antrian::where('tanggal_antrian', $hariIni)
                    ->where('nomor_urut', '<', $sedangDipanggil->nomor_urut)
                    ->orderByDesc('nomor_urut')
                    ->first();

                if ($antrianSebelumnya) {
                    $sedangDipanggil->update(['status' => 'menunggu']);

                    $antrianSebelumnya->update([
                        'status'          => 'dipanggil',
                        'dilayani_oleh'   => Auth::id(),
                        'waktu_dipanggil' => now(),
                    ]);
                }
            }
        });

        return back()->with('pesan_sukses', 'Kembali memanggil nomor antrian sebelumnya.');
    }
    /**
     * 8. Tombol Lewati (Skip) jika pasien dipanggil tapi tidak hadir.
     */
    public function lewatiAntrian($id): RedirectResponse
    {
        $antrian = Antrian::findOrFail($id);

        $antrian->update([
            'status'        => 'dilewati',
            'dilayani_oleh' => Auth::id(),
        ]);

        return back()->with('pesan_sukses', "Antrian {$antrian->kode_antrian} ditandai dilewati.");
    }

    /**
     * 9. Tombol Hapus Antrian.
     */
    public function hapusAntrian($id): RedirectResponse
    {
        $antrian = Antrian::findOrFail($id);
        $kode = $antrian->kode_antrian;
        
        $antrian->delete();

        return back()->with('pesan_sukses', "Antrian {$kode} berhasil dihapus.");
    }

}

?>