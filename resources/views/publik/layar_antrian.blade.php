<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitor Antrian Pasien — Klinik Medika</title>
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
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen lg:h-screen lg:overflow-hidden flex flex-col antialiased selection:bg-emerald-500 selection:text-white">

    <!-- TOP BAR: Clean Hospital Signage Header -->
    <header class="bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between shrink-0 shadow-[0_1px_3px_rgba(0,0,0,0.03)]">
        <div class="flex items-center gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-sm">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-extrabold tracking-tight text-base sm:text-lg text-slate-900 uppercase">Klinik Medika</span>
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        LIVE
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 hidden sm:block">Sistem Informasi &amp; Panggilan Antrian Pasien Terpadu</p>
            </div>
        </div>

        <div class="flex items-center gap-3 sm:gap-4">
            <!-- Digital Clock Badge -->
            <div class="flex items-center gap-2 bg-slate-100/90 border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-mono-num font-bold text-slate-700 shadow-inner">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                </svg>
                <span id="jam-server">{{ now()->format('H:i:s') }}</span>
            </div>

            <!-- Link to Admin Panel -->
            <a href="{{ route('panel.dashboard') }}" 
               class="text-xs font-bold text-slate-700 hover:text-slate-950 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-1.5 shadow-sm transition flex items-center gap-1.5">
                <span>Panel Admin</span>
                <span class="text-slate-400">&rarr;</span>
            </a>
        </div>
    </header>

    <!-- MAIN WORKSPACE -->
    <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 lg:divide-x lg:divide-slate-200 lg:overflow-hidden">

        <!-- KOLOM KIRI (5/12 Desktop): Papan Panggilan Aktif & Form Tiket -->
        <section class="lg:col-span-5 flex flex-col justify-between bg-white p-4 sm:p-6 lg:p-8 border-b lg:border-b-0 border-slate-200 space-y-6 lg:space-y-0">
            
            <!-- Papan Nomor Panggilan Aktif (Hero Card) -->
            <div id="kartu-panggilan" class="relative rounded-2xl bg-gradient-to-b from-slate-900 to-slate-950 text-white p-6 sm:p-8 lg:p-9 shadow-xl border border-slate-800 flex flex-col justify-between overflow-hidden transition-all duration-300">
                <!-- Background Accent Glow -->
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex items-center justify-between border-b border-slate-800/80 pb-4 relative z-10">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span class="text-[11px] font-mono-num uppercase tracking-widest text-slate-300 font-bold">Nomor Panggilan Aktif</span>
                    </div>
                    <span id="teks-info-loket" class="text-xs font-mono-num font-bold uppercase px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm">
                        {{ $antrianSekarang ? 'LOKET UTAMA' : 'STANDBY' }}
                    </span>
                </div>

                <!-- Giant Number Display -->
                <div class="my-6 sm:my-8 lg:my-auto py-2 text-center relative z-10">
                    <div id="teks-kode-antrian" class="font-mono-num text-7xl sm:text-8xl lg:text-9xl font-extrabold tracking-tighter text-white drop-shadow-md leading-none">
                        {{ $antrianSekarang ? $antrianSekarang->kode_antrian : '---' }}
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-2">
                        <div class="text-center sm:text-left">
                            <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-400">Nama Pasien</span>
                            <p id="teks-nama-pasien" class="text-lg lg:text-xl font-bold text-slate-100 tracking-tight mt-0.5">
                                {{ $antrianSekarang ? $antrianSekarang->nama_pasien : 'Belum ada panggilan aktif' }}
                            </p>
                        </div>
                        <span id="teks-poli-aktif" class="text-xs font-mono-num font-bold text-emerald-300 bg-slate-800/90 border border-slate-700 px-3 py-1 rounded-lg">
                            {{ $antrianSekarang ? ($antrianSekarang->jenis_poli ?? 'Poli Umum') : '-' }}
                        </span>
                    </div>
                </div>

                <!-- Mini Footer Status -->
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-800/80 text-xs relative z-10">
                    <div>
                        <span class="text-slate-400 block text-[11px] font-medium">Antrian Menunggu</span>
                        <span id="angka-menunggu" class="font-mono-num text-2xl font-extrabold text-white mt-0.5 block">
                            {{ $jumlahMenunggu ?? 0 }} <span class="text-xs font-normal text-slate-400">pasien</span>
                        </span>
                    </div>
                    <div class="text-right flex flex-col justify-end">
                        <span class="text-slate-400 block text-[11px] font-medium">Sinkronisasi</span>
                        <span class="font-mono-num text-emerald-400 font-bold text-xs flex items-center justify-end gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Otomatis 2 dtk
                        </span>
                    </div>
                </div>
            </div>

            <!-- Form Ambil Tiket Mandiri (In-Page Card: Responsif Mobile & Desktop) -->
            <div class="bg-slate-50/80 rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3.5">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                            Ambil Nomor Antrian Mandiri
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Silakan ketik nama Anda dan pilih poli layanan tujuan</p>
                    </div>
                    <span class="hidden sm:inline-block text-[11px] font-mono-num text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded font-bold">GRATIS</span>
                </div>

                <form id="form-ambil-tiket" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                    <input type="text" id="input_nama_pasien" placeholder="Masukkan nama lengkap Anda..." required
                        class="sm:col-span-5 border border-slate-300 rounded-xl bg-white px-3.5 py-2.5 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition">
                    
                    <select id="input_jenis_poli" class="sm:col-span-4 border border-slate-300 rounded-xl bg-white px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition">
                        <option value="Poli Umum">Poli Umum</option>
                        <option value="Poli Gigi">Poli Gigi</option>
                        <option value="Poli Anak">Poli Anak</option>
                        <option value="Farmasi / Obat">Farmasi / Obat</option>
                    </select>

                    <button type="submit" id="btn-ambil-tiket" class="sm:col-span-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs uppercase tracking-wider py-3 sm:py-2.5 px-4 rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Cetak Tiket</span>
                    </button>
                </form>
            </div>

        </section>

        <!-- KOLOM KANAN (7/12 Desktop): Urutan Daftar Antrian -->
        <section class="lg:col-span-7 flex flex-col bg-[#F8FAFC] lg:overflow-hidden">
            
            <!-- Table Header Bar -->
            <div class="px-5 sm:px-8 py-4 bg-white border-b border-slate-200 flex items-center justify-between shrink-0 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">Daftar Antrian Hari Ini</h2>
                    <p class="text-xs text-slate-500">Monitor urutan giliran pasien secara otomatis</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono-num font-semibold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-md">
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            <!-- 1. TAMPILAN DESKTOP: Tabel Modern & Rapi -->
            <div class="hidden lg:block flex-1 overflow-y-auto bg-white custom-scrollbar">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-slate-50/90 border-b border-slate-200 text-[11px] font-mono-num uppercase text-slate-500 sticky top-0 z-10 backdrop-blur-sm">
                        <tr>
                            <th class="py-3 px-6 w-28">No. Tiket</th>
                            <th class="py-3 px-6">Nama Pasien</th>
                            <th class="py-3 px-6 w-44">Unit Poli</th>
                            <th class="py-3 px-6 w-36">Status</th>
                            <th class="py-3 px-6 w-28 text-right">Jam Panggil</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-antrian-desktop" class="divide-y divide-slate-100">
                        @forelse($daftarAntrian as $item)
                            <tr class="{{ $item->status === 'dipanggil' ? 'bg-emerald-50/80 font-semibold border-l-4 border-l-emerald-600' : 'hover:bg-slate-50/80' }} transition">
                                <td class="py-3.5 px-6 font-mono-num font-bold text-slate-900">{{ $item->kode_antrian }}</td>
                                <td class="py-3.5 px-6 text-slate-800 font-medium">{{ $item->nama_pasien }}</td>
                                <td class="py-3.5 px-6 text-xs text-slate-600">
                                    <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded text-[11px] font-medium">{{ $item->jenis_poli ?? 'Poli Umum' }}</span>
                                </td>
                                <td class="py-3.5 px-6">
                                    @if($item->status === 'dipanggil')
                                        <span class="inline-flex items-center gap-1 font-mono-num text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full bg-emerald-600 text-white shadow-xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> DIPANGGIL
                                        </span>
                                    @elseif($item->status === 'menunggu')
                                        <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300">MENUNGGU</span>
                                    @elseif($item->status === 'selesai')
                                        <span class="inline-block font-mono-num text-[11px] font-medium uppercase px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">SELESAI</span>
                                    @else
                                        <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800">DILEWATI</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-right font-mono-num text-xs text-slate-500">
                                    {{ $item->waktu_dipanggil ? \Carbon\Carbon::parse($item->waktu_dipanggil)->format('H:i') : '--:--' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center text-slate-400 text-xs font-mono-num">BELUM ADA DATA ANTRIAN HARI INI</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 2. TAMPILAN MOBILE: Kartu Antrian Rapi & Jelas -->
            <div id="list-antrian-mobile" class="lg:hidden p-4 space-y-2.5">
                @forelse($daftarAntrian as $item)
                    <div class="rounded-xl border {{ $item->status === 'dipanggil' ? 'border-l-4 border-l-emerald-600 border-slate-300 bg-emerald-50/50 shadow-sm' : 'border-slate-200 bg-white' }} p-4 flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3.5">
                            <span class="font-mono-num text-xl font-extrabold text-slate-900">{{ $item->kode_antrian }}</span>
                            <div>
                                <p class="text-sm font-bold text-slate-800 leading-tight">{{ $item->nama_pasien }}</p>
                                <span class="text-[11px] text-slate-500 font-medium">{{ $item->jenis_poli ?? 'Poli Umum' }}</span>
                            </div>
                        </div>
                        <div>
                            @if($item->status === 'dipanggil')
                                <span class="inline-flex items-center gap-1 font-mono-num text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full bg-emerald-600 text-white">
                                    DIPANGGIL
                                </span>
                            @elseif($item->status === 'menunggu')
                                <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300">MENUNGGU</span>
                            @elseif($item->status === 'selesai')
                                <span class="inline-block font-mono-num text-[11px] font-medium uppercase px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">SELESAI</span>
                            @else
                                <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800">DILEWATI</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-xs font-mono-num">BELUM ADA DATA ANTRIAN HARI INI</div>
                @endforelse
            </div>

        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let kodeTerakhirDipanggil = null;

        function labelStatus(status) {
            const peta = {
                'dipanggil': '<span class="inline-flex items-center gap-1 font-mono-num text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full bg-emerald-600 text-white shadow-xs"><span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> DIPANGGIL</span>',
                'menunggu': '<span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300">MENUNGGU</span>',
                'selesai': '<span class="inline-block font-mono-num text-[11px] font-medium uppercase px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">SELESAI</span>',
                'dilewati': '<span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800">DILEWATI</span>'
            };
            return peta[status] || status;
        }

        async function muatDataAntrian() {
            try {
                const respon = await fetch("{{ route('publik.data') }}", {
                    headers: { 'Accept': 'application/json' }
                });
                if (!respon.ok) return;

                const data = await respon.json();

                document.getElementById('jam-server').textContent = data.jam_server;
                document.getElementById('angka-menunggu').innerHTML = `${data.jumlah_menunggu} <span class="text-xs font-normal text-slate-400">pasien</span>`;

                const elKode = document.getElementById('teks-kode-antrian');
                const elPasien = document.getElementById('teks-nama-pasien');
                const elPoli = document.getElementById('teks-poli-aktif');
                const elLoket = document.getElementById('teks-info-loket');
                const elKartu = document.getElementById('kartu-panggilan');

                // Saat ada nomor yang sedang DIPANGGIL
                if (data.antrian_sekarang) {
                    elKode.textContent = data.antrian_sekarang.kode_antrian;
                    elPasien.textContent = data.antrian_sekarang.nama_pasien || 'Pasien Umum';
                    elPoli.textContent = data.antrian_sekarang.jenis_poli || 'Poli Umum';
                    elLoket.textContent = 'LOKET UTAMA';

                    if (kodeTerakhirDipanggil !== data.antrian_sekarang.kode_antrian) {
                        kodeTerakhirDipanggil = data.antrian_sekarang.kode_antrian;
                        elKartu.classList.add('ring-4', 'ring-emerald-500/60');
                        setTimeout(() => elKartu.classList.remove('ring-4', 'ring-emerald-500/60'), 1500);
                    }
                } else {
                    elKode.textContent = '---';
                    elPasien.textContent = 'Belum ada panggilan aktif';
                    elPoli.textContent = '-';
                    elLoket.textContent = 'STANDBY';
                    kodeTerakhirDipanggil = null;
                }

                // Render Tabel Desktop & List Kartu Mobile
                const tabelDesktop = document.getElementById('tabel-antrian-desktop');
                const listMobile = document.getElementById('list-antrian-mobile');

                if (data.daftar_antrian.length === 0) {
                    const kosongHtml = `<div class="py-16 text-center text-slate-400 text-xs font-mono-num">BELUM ADA DATA ANTRIAN HARI INI</div>`;
                    tabelDesktop.innerHTML = `<tr><td colspan="5">${kosongHtml}</td></tr>`;
                    listMobile.innerHTML = kosongHtml;
                    return;
                }

                // 1. Isi Tabel Desktop
                tabelDesktop.innerHTML = data.daftar_antrian.map(item => {
                    const barisAktif = item.status === 'dipanggil' ? 'bg-emerald-50/80 font-semibold border-l-4 border-l-emerald-600' : 'hover:bg-slate-50/80';
                    const jamPanggil = item.waktu_dipanggil
                        ? new Date(item.waktu_dipanggil).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                        : '--:--';

                    return `
                        <tr class="${barisAktif} transition">
                            <td class="py-3.5 px-6 font-mono-num font-bold text-slate-900">${item.kode_antrian}</td>
                            <td class="py-3.5 px-6 text-slate-800 font-medium">${item.nama_pasien}</td>
                            <td class="py-3.5 px-6 text-xs text-slate-600">
                                <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded text-[11px] font-medium">${item.jenis_poli || 'Poli Umum'}</span>
                            </td>
                            <td class="py-3.5 px-6">${labelStatus(item.status)}</td>
                            <td class="py-3.5 px-6 text-right font-mono-num text-xs text-slate-500">${jamPanggil}</td>
                        </tr>
                    `;
                }).join('');

                // 2. Isi Kartu Mobile
                listMobile.innerHTML = data.daftar_antrian.map(item => {
                    const borderAktif = item.status === 'dipanggil'
                        ? 'border-l-4 border-l-emerald-600 border-slate-300 bg-emerald-50/50 shadow-sm'
                        : 'border-slate-200 bg-white';

                    return `
                        <div class="rounded-xl border ${borderAktif} p-4 flex items-center justify-between shadow-xs">
                            <div class="flex items-center gap-3.5">
                                <span class="font-mono-num text-xl font-extrabold text-slate-900">${item.kode_antrian}</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">${item.nama_pasien}</p>
                                    <span class="text-[11px] text-slate-500 font-medium">${item.jenis_poli || 'Poli Umum'}</span>
                                </div>
                            </div>
                            <div>${labelStatus(item.status)}</div>
                        </div>
                    `;
                }).join('');

            } catch (err) {
                console.error('Gagal mengambil data realtime:', err);
            }
        }

        async function kirimAntrianBaru(nama, poli) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch("{{ route('publik.ambil') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nama_pasien: nama, jenis_poli: poli })
            });

            const hasil = await res.json();
            if (!res.ok) throw new Error(hasil.pesan || hasil.message || 'Gagal mengambil nomor antrian');
            muatDataAntrian();
            return hasil;
        }

        function tampilkanErrorAntrian(kesalahan) {
            const pesan = kesalahan.message || 'Terjadi kesalahan saat mengambil tiket.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Gagal Mengambil Nomor',
                    text: pesan,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#e11d48'
                });
            } else {
                alert('Gagal Mengambil Nomor: ' + pesan);
            }
        }

        function tampilkanNomorAntrian(hasil) {
            const kode = hasil.data?.kode_antrian || 'Berhasil';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Nomor Berhasil Diambil!',
                    html: `Nomor antrian Anda: <b class="font-mono text-xl text-emerald-700">${kode}</b>.<br><span class="text-sm text-slate-500">Silakan menunggu panggilan petugas loket.</span>`,
                    icon: 'success',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#059669'
                });
            } else {
                alert(`Nomor Berhasil Diambil!\nNomor antrian Anda: ${kode}. Silakan menunggu panggilan.`);
            }
        }

        // Listener form ambil tiket
        document.getElementById('form-ambil-tiket').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btn-ambil-tiket');
            const namaEl = document.getElementById('input_nama_pasien');
            const poliEl = document.getElementById('input_jenis_poli');

            const teksAsli = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="inline-block animate-spin mr-1">&#8635;</span> <span>Memproses...</span>';

            try {
                const hasil = await kirimAntrianBaru(namaEl.value, poliEl.value);
                namaEl.value = '';
                tampilkanNomorAntrian(hasil);
            } catch (kesalahan) {
                tampilkanErrorAntrian(kesalahan);
            } finally {
                btn.disabled = false;
                btn.innerHTML = teksAsli;
            }
        });

        // Polling otomatis setiap 2 detik
        muatDataAntrian();
        setInterval(muatDataAntrian, 2000);
    </script>
</body>
</html>
