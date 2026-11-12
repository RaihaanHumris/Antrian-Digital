<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meja Kendali Antrian — Klinik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono-num { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen pb-24 lg:pb-0 flex flex-col">

    <!-- HEADER PANEL OPERATOR -->
    <header class="bg-slate-900 text-white px-4 lg:px-8 h-14 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 bg-emerald-400"></span>
            <span class="font-bold text-sm tracking-tight uppercase">Meja Kendali Antrian</span>
            <span class="hidden sm:inline-block text-[11px] font-mono-num bg-slate-800 text-slate-300 px-2 py-0.5 border border-slate-700">
                {{ auth()->user()->name ?? 'Admin' }}
            </span>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('publik.utama') }}" target="_blank" class="text-xs font-medium text-slate-300 hover:text-white border border-slate-700 px-2.5 py-1">
                Layar Publik &#8599;
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs font-semibold bg-rose-700 hover:bg-rose-800 text-white px-3 py-1 transition">
                    Keluar
                </button>
            </form>
        </div>
    </header>

    <!-- LAYOUT UTAMA -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 max-w-[1440px] w-full mx-auto">

        <!-- SIDEBAR KIRI (Desktop): Ringkasan & Input Pasien Baru -->
        <aside class="lg:col-span-3 bg-white border-b lg:border-b-0 lg:border-r border-slate-200 p-4 lg:p-6 space-y-6">
            
            <!-- Ringkasan Statistik Cepat -->
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="border border-slate-200 p-2.5 bg-slate-50">
                    <span class="block text-[10px] uppercase text-slate-500">Total</span>
                    <span class="font-mono-num text-lg font-bold text-slate-900">{{ $daftarAntrian->count() }}</span>
                </div>
                <div class="border border-amber-200 p-2.5 bg-amber-50/50">
                    <span class="block text-[10px] uppercase text-amber-800">Menunggu</span>
                    <span class="font-mono-num text-lg font-bold text-amber-900">{{ $daftarAntrian->where('status', 'menunggu')->count() }}</span>
                </div>
                <div class="border border-slate-200 p-2.5 bg-slate-50">
                    <span class="block text-[10px] uppercase text-slate-500">Selesai</span>
                    <span class="font-mono-num text-lg font-bold text-slate-700">{{ $daftarAntrian->where('status', 'selesai')->count() }}</span>
                </div>
            </div>

            <!-- Form Registrasi Manual Pasien -->
            <div>
                <div class="border-b border-slate-200 pb-2 mb-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">Input Antrian Manual</h2>
                    <p class="text-[11px] text-slate-500">Pendaftaran oleh petugas loket</p>
                </div>

                <form action="{{ route('panel.antrian.tambah') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Nama Pasien</label>
                        <input type="text" name="nama_pasien" required placeholder="Masukkan nama pasien"
                            class="w-full border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:border-slate-900">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Unit Poli</label>
                        <select name="jenis_poli" class="w-full border border-slate-300 px-3 py-2 text-sm bg-white focus:outline-none focus:border-slate-900">
                            <option value="Poli Umum">Poli Umum</option>
                            <option value="Poli Gigi">Poli Gigi</option>
                            <option value="Poli Anak">Poli Anak</option>
                            <option value="Farmasi / Obat">Farmasi / Obat</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider py-3 transition">
                        + Tambah ke Antrian
                    </button>
                </form>
            </div>

        </aside>

        <!-- WORKSPACE KANAN (Desktop): Kontrol Panggilan & Daftar Antrian -->
        <main class="lg:col-span-9 p-4 lg:p-8 space-y-6">

            <!-- PANEL KONTROL PANGGILAN AKTIF -->
            <div class="bg-white border border-slate-300 p-5 lg:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                <div class="flex items-start sm:items-center gap-5">
                    <div class="bg-slate-900 text-white px-5 py-4 min-w-[140px] text-center">
                        <span class="block text-[10px] font-mono-num uppercase tracking-widest text-slate-400">Dipanggil</span>
                        <span class="font-mono-num text-4xl font-extrabold tracking-tight block mt-1">
                            {{ $antrianAktif ? $antrianAktif->kode_antrian : '---' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pasien Saat Ini</span>
                        <h3 class="text-lg font-bold text-slate-900 mt-0.5">
                            {{ $antrianAktif ? $antrianAktif->nama_pasien : 'Belum ada nomor yang dipanggil' }}
                        </h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-slate-500">
                                {{ $antrianAktif ? ($antrianAktif->jenis_poli ?? 'Poli Umum') : 'Tekan tombol Selanjutnya untuk memulai panggilan' }}
                            </span>
                            @if($antrianAktif)
                                <form action="{{ route('panel.antrian.lewati', $antrianAktif->id) }}" method="POST" class="inline ml-2">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-[11px] font-mono-num font-bold uppercase text-amber-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 px-2 py-0.5">
                                        Lewati (Skip)
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- TOMBOL PREV / NEXT KHUSUS DESKTOP -->
                <div class="hidden lg:flex items-center gap-3 shrink-0">
                    <form action="{{ route('panel.antrian.sebelumnya') }}" method="POST">
                        @csrf
                        <button type="submit" class="border border-slate-300 bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold uppercase tracking-wider px-5 py-4 transition">
                            &larr; Sebelumnya (Prev)
                        </button>
                    </form>

                    <form action="{{ route('panel.antrian.selanjutnya') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider px-7 py-4 transition">
                            Selanjutnya (Next) &rarr;
                        </button>
                    </form>
                </div>

            </div>

            <!-- DAFTAR ANTRIAN -->
            <div class="bg-white border border-slate-200">
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">Daftar Antrian Hari Ini</h2>
                    <span class="text-xs font-mono-num text-slate-500">{{ now()->format('d/m/Y') }}</span>
                </div>

                <!-- 1. TABEL KHUSUS DESKTOP -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-mono-num uppercase text-slate-500">
                            <tr>
                                <th class="py-3 px-5 w-20">Urut</th>
                                <th class="py-3 px-5 w-28">Kode</th>
                                <th class="py-3 px-5">Nama Pasien</th>
                                <th class="py-3 px-5">Poli</th>
                                <th class="py-3 px-5">Status</th>
                                <th class="py-3 px-5">Waktu Panggil</th>
                                <th class="py-3 px-5 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($daftarAntrian as $item)
                                <tr class="{{ $item->status === 'dipanggil' ? 'bg-emerald-50/70 font-semibold' : 'hover:bg-slate-50' }}">
                                    <td class="py-3 px-5 font-mono-num text-xs text-slate-500">#{{ $item->nomor_urut }}</td>
                                    <td class="py-3 px-5 font-mono-num font-bold text-slate-900">{{ $item->kode_antrian }}</td>
                                    <td class="py-3 px-5">{{ $item->nama_pasien }}</td>
                                    <td class="py-3 px-5 text-xs text-slate-600">{{ $item->jenis_poli ?? 'Poli Umum' }}</td>
                                    <td class="py-3 px-5">
                                        @if($item->status === 'dipanggil')
                                            <span class="font-mono-num text-[11px] font-bold uppercase px-2 py-0.5 bg-emerald-600 text-white">DIPANGGIL</span>
                                        @elseif($item->status === 'menunggu')
                                            <span class="font-mono-num text-[11px] font-semibold uppercase px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-300">MENUNGGU</span>
                                        @elseif($item->status === 'dilewati')
                                            <span class="font-mono-num text-[11px] font-semibold uppercase px-2 py-0.5 bg-rose-100 text-rose-800">DILEWATI</span>
                                        @else
                                            <span class="font-mono-num text-[11px] uppercase px-2 py-0.5 bg-slate-100 text-slate-500">SELESAI</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-5 font-mono-num text-xs text-slate-500">
                                        {{ $item->waktu_dipanggil ? \Carbon\Carbon::parse($item->waktu_dipanggil)->format('H:i:s') : '--:--' }}
                                    </td>
                                    <td class="py-3 px-5 text-right">
                                        <form action="{{ route('panel.antrian.hapus', $item->id) }}" method="POST" data-confirm-delete data-antrian-code="{{ $item->kode_antrian }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-mono-num text-rose-600 hover:underline">HAPUS</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-xs font-mono-num text-slate-400">BELUM ADA DATA ANTRIAN</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 2. LIST KARTU KHUSUS MOBILE -->
                <div class="lg:hidden divide-y divide-slate-200">
                    @forelse($daftarAntrian as $item)
                        <div class="p-4 flex items-center justify-between {{ $item->status === 'dipanggil' ? 'bg-emerald-50/70' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="font-mono-num text-base font-extrabold text-slate-900">{{ $item->kode_antrian }}</span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $item->nama_pasien }}</p>
                                    <span class="text-[11px] text-slate-500">{{ $item->jenis_poli ?? 'Poli Umum' }} &bull; {{ strtoupper($item->status) }}</span>
                                </div>
                            </div>
                            <form action="{{ route('panel.antrian.hapus', $item->id) }}" method="POST" data-confirm-delete data-antrian-code="{{ $item->kode_antrian }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] font-mono-num text-rose-600 border border-rose-200 px-2 py-1">HAPUS</button>
                            </form>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs font-mono-num text-slate-400">BELUM ADA DATA ANTRIAN</div>
                    @endforelse
                </div>

            </div>

        </main>

    </div>

    <!-- STICKY BOTTOM CONTROLLER KHUSUS MOBILE OPERATOR -->
    <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t-2 border-slate-900 p-3 grid grid-cols-2 gap-2.5 z-30">
        <form action="{{ route('panel.antrian.sebelumnya') }}" method="POST">
            @csrf
            <button type="submit" class="w-full border border-slate-300 bg-slate-100 text-slate-900 font-bold text-xs uppercase py-3.5">
                &larr; Prev
            </button>
        </form>
        <form action="{{ route('panel.antrian.selanjutnya') }}" method="POST">
            @csrf
            <button type="submit" class="w-full bg-emerald-700 text-white font-bold text-xs uppercase py-3.5">
                Next (Panggil) &rarr;
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const antrianCode = form.dataset.antrianCode;
                Swal.fire({
                    title: 'Hapus antrian?',
                    text: `Antrian ${antrianCode} akan dihapus.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#e11d48',
                }).then((result) => {
                    if (result.isConfirmed) {
                        HTMLFormElement.prototype.submit.call(form);
                    }
                });
            });
        });

        @if(session('pesan_sukses'))
            Swal.fire({
                title: 'Berhasil',
                text: @json(session('pesan_sukses')),
                icon: 'success',
                confirmButtonText: 'OK',
            });
        @elseif($errors->any())
            Swal.fire({
                title: 'Terjadi kesalahan',
                text: @json($errors->first()),
                icon: 'error',
                confirmButtonText: 'OK',
            });
        @endif
    </script>
</body>
</html>