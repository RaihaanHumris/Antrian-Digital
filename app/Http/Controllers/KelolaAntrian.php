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
     * 1. Menampilkan halaman form login khusus Administrator (Admin).
     */
    public function halamanLogin()
    {
        return view('auth.login');
    }

    /**
     * 2. Memproses autentikasi login Administrator.
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
     * 3. Memproses logout / keluar dari sesi login Admin.
     */
    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * 4. Menampilkan halaman dashboard kendali antrian untuk Admin.
     */
    public function tampilDashboard()
    {
        $hariIni = now()->toDateString();

        // Cari antrian yang sedang aktif dipanggil saat ini
        $antrianAktif = Antrian::where('status', 'dipanggil')
            ->orderByDesc('waktu_dipanggil')
            ->orderByDesc('id')
            ->first();

        // Ambil daftar antrian hari ini (atau seluruh antrian jika tanggal beda hari)
        $daftarAntrian = Antrian::whereDate('tanggal_antrian', $hariIni)
            ->orderBy('nomor_urut', 'asc')
            ->get();

        if ($daftarAntrian->isEmpty()) {
            $daftarAntrian = Antrian::orderBy('nomor_urut', 'asc')->get();
        }

        return view('admin.dashboard_antrian', compact('antrianAktif', 'daftarAntrian'));
    }

    /**
     * 5. Fitur Admin untuk menambah antrian pasien baru secara manual dari meja loket.
     */
    public function tambahAntrianManual(Request $request): RedirectResponse
    {
        $dataValid = $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'jenis_poli'  => 'required|string|max:50',
        ]);

        $hariIni = now()->toDateString();

        DB::transaction(function () use ($dataValid, $hariIni) {
            $terakhir = Antrian::where('tanggal_antrian', $hariIni)
                ->orderByDesc('nomor_urut')
                ->lockForUpdate()
                ->first();

            $nomorBaru = $terakhir ? ($terakhir->nomor_urut + 1) : 1;

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
     * 6. Tombol SELESAI:
     * Menyelesaikan pelayanan nomor antrian yang sedang aktif dipanggil.
     * Setelah pasien selesai dilayani, status antrian berubah menjadi 'selesai',
     * dan Admin siap memanggil nomor antrian berikutnya.
     */
    public function selesaikanAntrian($id): RedirectResponse
    {
        $antrian = Antrian::findOrFail($id);

        $antrian->update([
            'status' => 'selesai',
        ]);

        return back()->with('pesan_sukses', "Antrian {$antrian->kode_antrian} ({$antrian->nama_pasien}) telah selesai dilayani.");
    }

    /**
     * 7. Tombol NEXT (Lanjutkan Nomor Antrian Berikutnya):
     * - Jika ada nomor yang sedang dipanggil dan belum diselesaikan, selesaikan otomatis.
     * - Panggil antrian berikutnya yang masih berstatus 'menunggu' urut dari nomor urut terkecil.
     */
    public function panggilSelanjutnya(): RedirectResponse
    {
        $hariIni = now()->toDateString();

        $pesan = '';

        DB::transaction(function () use ($hariIni, &$pesan) {
            // Selesaikan antrian aktif saat ini jika masih ada
            $sedangDipanggil = Antrian::where('status', 'dipanggil')->first();
            if ($sedangDipanggil) {
                $sedangDipanggil->update(['status' => 'selesai']);
            }

            // Ambil antrian berikutnya yang berstatus 'menunggu'
            $antrianBerikutnya = Antrian::where('status', 'menunggu')
                ->orderBy('nomor_urut', 'asc')
                ->first();

            if ($antrianBerikutnya) {
                $antrianBerikutnya->update([
                    'status'          => 'dipanggil',
                    'dilayani_oleh'   => Auth::id(),
                    'waktu_dipanggil' => now(),
                ]);
                $pesan = "Berhasil memanggil nomor {$antrianBerikutnya->kode_antrian} ({$antrianBerikutnya->nama_pasien}).";
            } else {
                $pesan = "Semua antrian yang menunggu telah selesai.";
            }
        });

        return back()->with('pesan_sukses', $pesan);
    }

    /**
     * 8. Tombol PREV (Sebelumnya):
     * Memanggil kembali nomor antrian sebelumnya jika diperlukan.
     */
    public function panggilSebelumnya(): RedirectResponse
    {
        DB::transaction(function () {
            $sedangDipanggil = Antrian::where('status', 'dipanggil')->first();

            if ($sedangDipanggil) {
                $antrianSebelumnya = Antrian::where('nomor_urut', '<', $sedangDipanggil->nomor_urut)
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
            } else {
                $antrianTerakhir = Antrian::whereIn('status', ['selesai', 'dilewati'])
                    ->orderByDesc('waktu_dipanggil')
                    ->first();

                if ($antrianTerakhir) {
                    $antrianTerakhir->update([
                        'status'          => 'dipanggil',
                        'dilayani_oleh'   => Auth::id(),
                        'waktu_dipanggil' => now(),
                    ]);
                }
            }
        });

        return back()->with('pesan_sukses', 'Memanggil kembali antrian sebelumnya.');
    }

    /**
     * 9. Tombol LEWATI (Skip):
     * Menandai pasien yang tidak hadir saat dipanggil menjadi status 'dilewati'.
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
     * 10. Tombol HAPUS Antrian:
     */
    public function hapusAntrian($id): RedirectResponse
    {
        $antrian = Antrian::findOrFail($id);
        $kode = $antrian->kode_antrian;

        $antrian->delete();

        return back()->with('pesan_sukses', "Antrian {$kode} berhasil dihapus.");
    }
}
