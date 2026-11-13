<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meja Kendali Antrian — Administrator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono-num { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen pb-24 lg:pb-0 flex flex-col antialiased">

    <!-- HEADER PANEL ADMINISTRATOR -->
    <header class="bg-slate-900 text-white px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between shrink-0 shadow-md">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-sm sm:text-base tracking-tight uppercase">Meja Kendali Antrian</span>
                    <span class="hidden sm:inline-block text-[11px] font-mono-num bg-slate-800 text-emerald-400 px-2 py-0.5 rounded border border-slate-700">
                        {{ auth()->user()->name ?? 'Admin Klinik' }} &bull; Loket Utama
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 sm:gap-3">
            <a href="{{ route('publik.utama') }}" target="_blank" 
               class="text-xs font-semibold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 transition flex items-center gap-1.5">
                <span>Layar Publik</span>
                <span class="text-slate-400">&#8599;</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs font-bold bg-rose-600/90 hover:bg-rose-700 text-white px-3.5 py-1.5 rounded-lg transition shadow-xs">
                    Keluar
                </button>
            </form>
        </div>
    </header>

    <!-- NOTIFIKASI PESAN SUKSES / ERROR -->
    @if(session('pesan_sukses'))
        <div class="bg-emerald-600 text-white px-4 py-2.5 text-center text-xs font-semibold shadow-xs flex items-center justify-center gap-2">
            <span>&#10003;</span>
            <span>{{ session('pesan_sukses') }}</span>
        </div>
    @endif

    <!-- LAYOUT UTAMA -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 max-w-[1440px] w-full mx-auto">

        <!-- SIDEBAR KIRI (Desktop): Ringkasan & Form Pasien Baru -->
        <aside class="lg:col-span-3 bg-white border-b lg:border-b-0 lg:border-r border-slate-200 p-4 sm:p-6 space-y-6">
            
            <!-- Ringkasan Statistik Cepat (KPI Cards) -->
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="border border-slate-200 rounded-xl p-3 bg-slate-50 shadow-2xs">
                    <span class="block text-[10px] uppercase font-bold text-slate-500 tracking-wider">Total</span>
                    <span class="font-mono-num text-xl font-extrabold text-slate-900 mt-0.5 block">{{ $daftarAntrian->count() }}</span>
                </div>
                <div class="border border-amber-200 rounded-xl p-3 bg-amber-50/60 shadow-2xs">
                    <span class="block text-[10px] uppercase font-bold text-amber-800 tracking-wider">Menunggu</span>
                    <span class="font-mono-num text-xl font-extrabold text-amber-900 mt-0.5 block">{{ $daftarAntrian->where('status', 'menunggu')->count() }}</span>
                </div>
                <div class="border border-emerald-200 rounded-xl p-3 bg-emerald-50/60 shadow-2xs">
                    <span class="block text-[10px] uppercase font-bold text-emerald-800 tracking-wider">Selesai</span>
                    <span class="font-mono-num text-xl font-extrabold text-emerald-900 mt-0.5 block">{{ $daftarAntrian->where('status', 'selesai')->count() }}</span>
                </div>
            </div>

            <!-- Form Registrasi Manual Pasien -->
            <div class="bg-slate-50/70 border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-2xs">
                <div class="border-b border-slate-200/80 pb-3 mb-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Pendaftaran Manual Loket
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Input pasien langsung dari meja loket</p>
                </div>

                <form action="{{ route('panel.antrian.tambah') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Pasien</label>
                        <input type="text" name="nama_pasien" required placeholder="Contoh: Budi Santoso"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm bg-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 shadow-2xs transition">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Unit Poli Tujuan</label>
                        <select name="jenis_poli" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 shadow-2xs transition">
                            <option value="Poli Umum">Poli Umum</option>
                            <option value="Poli Gigi">Poli Gigi</option>
                            <option value="Poli Anak">Poli Anak</option>
                            <option value="Farmasi / Obat">Farmasi / Obat</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>+ Tambah Antrian</span>
                    </button>
                </form>
            </div>

        </aside>

        <!-- WORKSPACE KANAN: Kontrol Panggilan & Daftar Antrian -->
        <main class="lg:col-span-9 p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- PANEL KONTROL PANGGILAN AKTIF (Hero Control Card) -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                <div class="flex items-start sm:items-center gap-5">
                    <div class="{{ $antrianAktif ? 'bg-emerald-600 ring-4 ring-emerald-100' : 'bg-slate-900' }} text-white px-6 py-4 rounded-xl min-w-[140px] text-center transition shadow-sm">
                        <span class="block text-[10px] font-mono-num uppercase tracking-widest text-slate-200 font-bold">
                            {{ $antrianAktif ? 'Sedang Dipanggil' : 'Standby' }}
                        </span>
                        <span class="font-mono-num text-4xl sm:text-5xl font-extrabold tracking-tight block mt-1">
                            {{ $antrianAktif ? $antrianAktif->kode_antrian : '---' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pasien Sedang Dilayani</span>
                        <h3 class="text-xl font-extrabold text-slate-900 mt-0.5 tracking-tight">
                            {{ $antrianAktif ? $antrianAktif->nama_pasien : 'Belum ada nomor yang dipanggil' }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="text-xs font-medium text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-0.5 rounded-md">
                                {{ $antrianAktif ? ($antrianAktif->jenis_poli ?? 'Poli Umum') : 'Siap memanggil nomor antrian berikutnya' }}
                            </span>
                            @if($antrianAktif)
                                <form action="{{ route('panel.antrian.lewati', $antrianAktif->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-[11px] font-mono-num font-bold uppercase text-amber-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 px-2.5 py-0.5 rounded-md transition cursor-pointer">
                                        Lewati (Skip)
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- TOMBOL KONTROL AKSI (DESKTOP) -->
                <div class="hidden lg:flex items-center gap-3 shrink-0">
                    <!-- Tombol Prev -->
                    <form action="{{ route('panel.antrian.sebelumnya') }}" method="POST">
                        @csrf
                        <button type="submit" class="border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider px-4 py-3 rounded-xl shadow-2xs transition cursor-pointer">
                            &larr; Prev
                        </button>
                    </form>

                    @if($antrianAktif)
                        <!-- Tombol SELESAI (Aktif jika sedang ada antrian dipanggil) -->
                        <form action="{{ route('panel.antrian.selesai', $antrianAktif->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                                <span>&#10003;</span> Selesai Pelayanan
                            </button>
                        </form>
                    @endif

                    <!-- Tombol NEXT (Panggil antrian berikutnya) -->
                    <form action="{{ route('panel.antrian.selanjutnya') }}" method="POST">
                        @csrf
                        <button type="submit" class="{{ $antrianAktif ? 'border border-slate-300 bg-slate-900 hover:bg-slate-800 text-white' : 'bg-emerald-700 hover:bg-emerald-800 text-white' }} text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-xl shadow-sm transition cursor-pointer">
                            {{ $antrianAktif ? 'Lanjutkan Next' : 'Panggil Antrian' }} &rarr;
                        </button>
                    </form>
                </div>

            </div>

            <!-- DAFTAR ANTRIAN -->
            <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">Daftar Antrian Hari Ini</h2>
                        <p class="text-xs text-slate-500">Kelola status dan hapus tiket antrian</p>
                    </div>
                    <span class="text-xs font-mono-num font-semibold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-md">
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>

                <!-- 1. TABEL KHUSUS DESKTOP -->
                <div class="hidden lg:block overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-mono-num uppercase text-slate-500">
                            <tr>
                                <th class="py-3.5 px-6 w-20">Urut</th>
                                <th class="py-3.5 px-6 w-28">Kode</th>
                                <th class="py-3.5 px-6">Nama Pasien</th>
                                <th class="py-3.5 px-6 w-40">Unit Poli</th>
                                <th class="py-3.5 px-6 w-36">Status</th>
                                <th class="py-3.5 px-6 w-32">Waktu Panggil</th>
                                <th class="py-3.5 px-6 text-right w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($daftarAntrian as $item)
                                <tr class="{{ $item->status === 'dipanggil' ? 'bg-emerald-50/80 font-semibold border-l-4 border-l-emerald-600' : 'hover:bg-slate-50/80' }} transition">
                                    <td class="py-3.5 px-6 font-mono-num text-xs text-slate-500">#{{ $item->nomor_urut }}</td>
                                    <td class="py-3.5 px-6 font-mono-num font-bold text-slate-900">{{ $item->kode_antrian }}</td>
                                    <td class="py-3.5 px-6 text-slate-800 font-medium">{{ $item->nama_pasien }}</td>
                                    <td class="py-3.5 px-6 text-xs text-slate-600">
                                        <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded text-[11px] font-medium">{{ $item->jenis_poli ?? 'Poli Umum' }}</span>
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if($item->status === 'dipanggil')
                                            <span class="inline-flex items-center gap-1 font-mono-num text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full bg-emerald-600 text-white shadow-2xs">
                                                DIPANGGIL
                                            </span>
                                        @elseif($item->status === 'menunggu')
                                            <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300">MENUNGGU</span>
                                        @elseif($item->status === 'selesai')
                                            <span class="inline-block font-mono-num text-[11px] font-medium uppercase px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">SELESAI</span>
                                        @else
                                            <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800">DILEWATI</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 font-mono-num text-xs text-slate-500">
                                        {{ $item->waktu_dipanggil ? \Carbon\Carbon::parse($item->waktu_dipanggil)->format('H:i:s') : '--:--:--' }}
                                    </td>
                                    <td class="py-3.5 px-6 text-right">
                                        <form action="{{ route('panel.antrian.hapus', $item->id) }}" method="POST" data-confirm-delete data-antrian-code="{{ $item->kode_antrian }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-mono-num font-semibold text-rose-600 hover:text-rose-800 hover:underline cursor-pointer">
                                                HAPUS
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-16 text-center text-xs font-mono-num text-slate-400">BELUM ADA DATA ANTRIAN HARI INI</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 2. LIST KARTU KHUSUS MOBILE -->
                <div class="lg:hidden divide-y divide-slate-100">
                    @forelse($daftarAntrian as $item)
                        <div class="p-4 flex items-center justify-between {{ $item->status === 'dipanggil' ? 'bg-emerald-50/70 border-l-4 border-l-emerald-600' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="font-mono-num text-lg font-extrabold text-slate-900">{{ $item->kode_antrian }}</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $item->nama_pasien }}</p>
                                    <span class="text-[11px] text-slate-500 font-medium">{{ $item->jenis_poli ?? 'Poli Umum' }} &bull; {{ strtoupper($item->status) }}</span>
                                </div>
                            </div>
                            <form action="{{ route('panel.antrian.hapus', $item->id) }}" method="POST" data-confirm-delete data-antrian-code="{{ $item->kode_antrian }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-mono-num text-rose-600 hover:text-rose-800 border border-rose-200 rounded px-2.5 py-1 cursor-pointer">HAPUS</button>
                            </form>
                        </div>
                    @empty
                        <div class="p-12 text-center text-xs font-mono-num text-slate-400">BELUM ADA DATA ANTRIAN HARI INI</div>
                    @endforelse
                </div>

            </div>

        </main>

    </div>

    <!-- FLOATING DOCK CONTROLLER (KHUSUS MOBILE OPERATOR) -->
    <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-300 p-3 grid grid-cols-2 gap-2.5 z-30 shadow-lg">
        <form action="{{ route('panel.antrian.sebelumnya') }}" method="POST">
            @csrf
            <button type="submit" class="w-full border border-slate-300 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs uppercase py-3.5 rounded-xl transition cursor-pointer">
                &larr; Prev
            </button>
        </form>

        @if($antrianAktif)
            <!-- Jika sedang ada pasien dipanggil, tombol utama mobile adalah SELESAI -->
            <form action="{{ route('panel.antrian.selesai', $antrianAktif->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase py-3.5 rounded-xl shadow-sm transition cursor-pointer flex items-center justify-center gap-1.5">
                    <span>&#10003;</span> Selesai
                </button>
            </form>
        @else
            <!-- Jika belum ada antrian aktif, tombol utama mobile adalah NEXT -->
            <form action="{{ route('panel.antrian.selanjutnya') }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs uppercase py-3.5 rounded-xl shadow-sm transition cursor-pointer">
                    Next (Panggil) &rarr;
                </button>
            </form>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const antrianCode = form.dataset.antrianCode;
                Swal.fire({
                    title: 'Hapus Antrian?',
                    text: `Nomor antrian ${antrianCode} akan dihapus secara permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                }).then((result) => {
                    if (result.isConfirmed) {
                        HTMLFormElement.prototype.submit.call(form);
                    }
                });
            });
        });
    </script>
</body>
</html>
