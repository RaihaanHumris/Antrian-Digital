<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitor Antrian — Klinik Kesehatan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono-num { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen lg:h-screen lg:overflow-hidden flex flex-col pb-24 lg:pb-0">

    <!-- TOP BAR: Signage Header -->
    <header class="bg-white border-b border-slate-200 px-4 lg:px-8 h-14 lg:h-16 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 bg-emerald-600"></div>
            <div class="flex items-baseline gap-2.5">
                <span class="font-bold tracking-tight text-sm lg:text-base text-slate-900 uppercase">Klinik Kesehatan</span>
                <span class="hidden sm:inline-block text-xs text-slate-400 font-medium">/ Monitor Antrian Pasien</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 bg-slate-100 border border-slate-200 px-2.5 py-1 text-xs font-mono-num text-slate-700">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                <span id="jam-server">{{ now()->format('H:i:s') }}</span>
            </div>
            <a href="{{ route('panel.dashboard') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-300 px-3 py-1.5 hover:bg-slate-50 transition">
                Panel Admin
            </a>
        </div>
    </header>

    <!-- MAIN WORKSPACE -->
    <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 lg:divide-x lg:divide-slate-200 lg:overflow-hidden">

        <!-- KOLOM KIRI: Papan Panggilan Aktif & Form Kiosk Desktop -->
        <section class="lg:col-span-5 flex flex-col justify-between bg-white p-5 lg:p-8 border-b lg:border-b-0 border-slate-200">
            
            <!-- Papan Nomor Panggilan Aktif -->
            <div id="kartu-panggilan" class="border border-slate-900 bg-slate-900 text-white p-6 lg:p-10 flex flex-col justify-between flex-1 transition-colors duration-300">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <span class="text-[11px] font-mono-num uppercase tracking-widest text-slate-400">Panggilan Aktif</span>
                    <span id="teks-info-loket" class="text-xs font-mono-num font-bold uppercase px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        {{ $antrianSekarang ? 'LOKET UTAMA' : 'STANDBY' }}
                    </span>
                </div>

                <div class="my-8 lg:my-auto py-4">
                    <div id="teks-kode-antrian" class="font-mono-num text-6xl sm:text-7xl lg:text-8xl font-extrabold tracking-tighter text-white leading-none">
                        {{ $antrianSekarang ? $antrianSekarang->kode_antrian : '---' }}
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="block text-[10px] uppercase tracking-wider text-slate-400">Nama Pasien</span>
                            <p id="teks-nama-pasien" class="text-base lg:text-xl font-semibold text-slate-100 mt-0.5">
                                {{ $antrianSekarang ? $antrianSekarang->nama_pasien : 'Belum ada panggilan aktif' }}
                            </p>
                        </div>
                        <span id="teks-poli-aktif" class="text-xs font-mono-num text-slate-300 border border-slate-700 px-2.5 py-1">
                            {{ $antrianSekarang ? ($antrianSekarang->jenis_poli ?? 'Poli Umum') : '-' }}
                        </span>
                    </div>
                </div>

                <!-- Statistik Ringkas Bawah Papan -->
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-800 text-xs">
                    <div>
                        <span class="text-slate-400 block">Antrian Menunggu</span>
                        <span id="angka-menunggu" class="font-mono-num text-lg font-bold text-white">{{ $jumlahMenunggu ?? 0 }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 block">Pembaruan</span>
                        <span class="font-mono-num text-emerald-400 font-semibold">Otomatis / 2 dtk</span>
                    </div>
                </div>
            </div>

            <!-- FORM AMBIL TIKET MANDIRI (Desktop) -->
            <div class="hidden lg:block mt-6 pt-6 border-t border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Registrasi Mandiri (Kiosk)</h2>
                    <span class="text-[11px] text-slate-400">Cetak nomor instan</span>
                </div>

                <form id="form-ambil-desktop" class="grid grid-cols-12 gap-2.5">
                    <input type="text" id="nama_pasien_desktop" placeholder="Nama lengkap pasien..." required
                        class="col-span-5 border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm focus:bg-white focus:outline-none focus:border-slate-900">
                    
                    <select id="jenis_poli_desktop" class="col-span-4 border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white focus:outline-none focus:border-slate-900">
                        <option value="Poli Umum">Poli Umum</option>
                        <option value="Poli Gigi">Poli Gigi</option>
                        <option value="Poli Anak">Poli Anak</option>
                        <option value="Farmasi / Obat">Farmasi / Obat</option>
                    </select>

                    <button type="submit" class="col-span-3 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs uppercase tracking-wider px-4 py-2.5 transition">
                        + Ambil Tiket
                    </button>
                </form>
            </div>

        </section>

        <!-- KOLOM KANAN: Daftar Antrian (Tabel Desktop / Card List Mobile) -->
        <section class="lg:col-span-7 flex flex-col bg-[#F8FAFC] lg:overflow-hidden">
            
            <div class="px-4 lg:px-8 py-4 bg-white border-b border-slate-200 flex items-center justify-between shrink-0">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Daftar Urutan Antrian</h2>
                    <p class="text-xs text-slate-500">Data diperbarui secara langsung tanpa muat ulang halaman</p>
                </div>
                <span class="text-xs font-mono-num text-slate-500">{{ now()->format('d/m/Y') }}</span>
            </div>

            <!-- 1. TAMPILAN DESKTOP -->
            <div class="hidden lg:block flex-1 overflow-y-auto bg-white">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-100 border-b border-slate-200 text-[11px] font-mono-num uppercase text-slate-500 sticky top-0">
                        <tr>
                            <th class="py-3 px-6 w-32">Kode</th>
                            <th class="py-3 px-6">Nama Pasien</th>
                            <th class="py-3 px-6 w-40">Unit / Poli</th>
                            <th class="py-3 px-6 w-36">Status</th>
                            <th class="py-3 px-6 w-28 text-right">Pukul</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-antrian-desktop" class="divide-y divide-slate-200 text-sm">
                        @forelse($daftarAntrian as $item)
                            <tr class="{{ $item->status === 'dipanggil' ? 'bg-emerald-50/90 font-semibold' : 'hover:bg-slate-50' }}">
                                <td class="py-3.5 px-6 font-mono-num font-bold text-slate-900">{{ $item->kode_antrian }}</td>
                                <td class="py-3.5 px-6 text-slate-800">{{ $item->nama_pasien }}</td>
                                <td class="py-3.5 px-6 text-xs text-slate-600">{{ $item->jenis_poli ?? 'Poli Umum' }}</td>
                                <td class="py-3.5 px-6">
                                    @if($item->status === 'dipanggil')
                                        <span class="inline-block font-mono-num text-[11px] font-bold uppercase px-2 py-0.5 bg-emerald-600 text-white">DIPANGGIL</span>
                                    @elseif($item->status === 'menunggu')
                                        <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-300">MENUNGGU</span>
                                    @elseif($item->status === 'selesai')
                                        <span class="inline-block font-mono-num text-[11px] uppercase px-2 py-0.5 bg-slate-100 text-slate-500">SELESAI</span>
                                    @else
                                        <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2 py-0.5 bg-rose-100 text-rose-800">DILEWATI</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-right font-mono-num text-xs text-slate-500">
                                    {{ $item->waktu_dipanggil ? \Carbon\Carbon::parse($item->waktu_dipanggil)->format('H:i') : '--:--' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 text-xs font-mono-num">BELUM ADA DATA ANTRIAN HARI INI</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 2. TAMPILAN MOBILE -->
            <div id="list-antrian-mobile" class="lg:hidden p-4 space-y-2.5">
                @forelse($daftarAntrian as $item)
                    <div class="border {{ $item->status === 'dipanggil' ? 'border-l-4 border-l-emerald-600 border-slate-300 bg-emerald-50/40' : 'border-slate-200 bg-white' }} p-3.5 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <span class="font-mono-num text-lg font-extrabold text-slate-900">{{ $item->kode_antrian }}</span>
                            <div>
                                <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $item->nama_pasien }}</p>
                                <span class="text-[11px] text-slate-500">{{ $item->jenis_poli ?? 'Poli Umum' }}</span>
                            </div>
                        </div>
                        <div>
                            @if($item->status === 'dipanggil')
                                <span class="inline-block font-mono-num text-[11px] font-bold uppercase px-2 py-0.5 bg-emerald-600 text-white">DIPANGGIL</span>
                            @elseif($item->status === 'menunggu')
                                <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-300">MENUNGGU</span>
                            @elseif($item->status === 'selesai')
                                <span class="inline-block font-mono-num text-[11px] uppercase px-2 py-0.5 bg-slate-100 text-slate-500">SELESAI</span>
                            @else
                                <span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2 py-0.5 bg-rose-100 text-rose-800">DILEWATI</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-slate-400 text-xs font-mono-num">BELUM ADA DATA ANTRIAN HARI INI</div>
                @endforelse
            </div>

        </section>

    </main>

    <!-- STICKY BOTTOM BAR (Mobile) -->
    <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-300 p-3 z-30 flex items-center justify-between gap-3">
        <div class="pl-1">
            <span class="block text-[10px] uppercase tracking-wider text-slate-400">Ingin Berobat?</span>
            <span class="text-xs font-bold text-slate-800">Daftar nomor antrian dari HP</span>
        </div>
        <button type="button" onclick="bukaModalMobile(true)" class="bg-slate-900 text-white text-xs font-bold uppercase tracking-wider px-5 py-3 shrink-0">
            + Ambil Nomor
        </button>
    </div>

    <!-- Modal Drawer Mobile -->
    <div id="modal-ambil-mobile" class="lg:hidden fixed inset-0 bg-slate-900/60 z-40 hidden items-end">
        <div class="bg-white w-full p-5 border-t-2 border-slate-900">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wider">Ambil Nomor Antrian</h3>
                <button type="button" onclick="bukaModalMobile(false)" class="text-xs font-mono-num text-slate-500 px-2 py-1 border border-slate-200">TUTUP [X]</button>
            </div>
            <form id="form-ambil-mobile" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Nama Pasien</label>
                    <input type="text" id="nama_pasien_mobile" required placeholder="Masukkan nama lengkap"
                        class="w-full border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:border-slate-900">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 mb-1">Pilih Unit Layanan</label>
                    <select id="jenis_poli_mobile" class="w-full border border-slate-300 px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:border-slate-900">
                        <option value="Poli Umum">Poli Umum</option>
                        <option value="Poli Gigi">Poli Gigi</option>
                        <option value="Poli Anak">Poli Anak</option>
                        <option value="Farmasi / Obat">Farmasi / Obat</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider py-3.5 mt-2">
                    Cetak Nomor Antrian Sekarang
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let kodeTerakhirDipanggil = null;

        function bukaModalMobile(tampil) {
            const modal = document.getElementById('modal-ambil-mobile');
            if (tampil) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function labelStatus(status) {
            const peta = {
                'dipanggil': '<span class="inline-block font-mono-num text-[11px] font-bold uppercase px-2 py-0.5 bg-emerald-600 text-white">DIPANGGIL</span>',
                'menunggu': '<span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-300">MENUNGGU</span>',
                'selesai': '<span class="inline-block font-mono-num text-[11px] uppercase px-2 py-0.5 bg-slate-100 text-slate-500">SELESAI</span>',
                'dilewati': '<span class="inline-block font-mono-num text-[11px] font-semibold uppercase px-2 py-0.5 bg-rose-100 text-rose-800">DILEWATI</span>'
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
                document.getElementById('angka-menunggu').textContent = data.jumlah_menunggu;

                const elKode = document.getElementById('teks-kode-antrian');
                const elPasien = document.getElementById('teks-nama-pasien');
                const elPoli = document.getElementById('teks-poli-aktif');
                const elLoket = document.getElementById('teks-info-loket');
                const elKartu = document.getElementById('kartu-panggilan');

                // Saat ada nomor yang sedang DIPANGGIL, langsung tampilkan di layar publik
                if (data.antrian_sekarang) {
                    elKode.textContent = data.antrian_sekarang.kode_antrian;
                    elPasien.textContent = data.antrian_sekarang.nama_pasien || 'Pasien Umum';
                    elPoli.textContent = data.antrian_sekarang.jenis_poli || 'Poli Umum';
                    elLoket.textContent = 'LOKET UTAMA';

                    if (kodeTerakhirDipanggil !== data.antrian_sekarang.kode_antrian) {
                        kodeTerakhirDipanggil = data.antrian_sekarang.kode_antrian;
                        elKartu.classList.remove('bg-slate-900');
                        elKartu.classList.add('bg-emerald-900');
                        setTimeout(() => {
                            elKartu.classList.remove('bg-emerald-900');
                            elKartu.classList.add('bg-slate-900');
                        }, 1200);
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
                    const kosongHtml = `<div class="py-12 text-center text-slate-400 text-xs font-mono-num">BELUM ADA DATA ANTRIAN HARI INI</div>`;
                    tabelDesktop.innerHTML = `<tr><td colspan="5">${kosongHtml}</td></tr>`;
                    listMobile.innerHTML = kosongHtml;
                    return;
                }

                // 1. Isi Tabel Desktop
                tabelDesktop.innerHTML = data.daftar_antrian.map(item => {
                    const barisAktif = item.status === 'dipanggil' ? 'bg-emerald-50/90 font-semibold' : 'hover:bg-slate-50';
                    const jamPanggil = item.waktu_dipanggil
                        ? new Date(item.waktu_dipanggil).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                        : '--:--';

                    return `
                        <tr class="${barisAktif}">
                            <td class="py-3.5 px-6 font-mono-num font-bold text-slate-900">${item.kode_antrian}</td>
                            <td class="py-3.5 px-6 text-slate-800">${item.nama_pasien}</td>
                            <td class="py-3.5 px-6 text-xs text-slate-600">${item.jenis_poli || 'Poli Umum'}</td>
                            <td class="py-3.5 px-6">${labelStatus(item.status)}</td>
                            <td class="py-3.5 px-6 text-right font-mono-num text-xs text-slate-500">${jamPanggil}</td>
                        </tr>
                    `;
                }).join('');

                // 2. Isi Kartu Mobile
                listMobile.innerHTML = data.daftar_antrian.map(item => {
                    const borderAktif = item.status === 'dipanggil'
                        ? 'border-l-4 border-l-emerald-600 border-slate-300 bg-emerald-50/40'
                        : 'border-slate-200 bg-white';

                    return `
                        <div class="border ${borderAktif} p-3.5 flex items-center justify-between">
                            <div class="flex items-center gap-3.5">
                                <span class="font-mono-num text-lg font-extrabold text-slate-900">${item.kode_antrian}</span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 leading-tight">${item.nama_pasien}</p>
                                    <span class="text-[11px] text-slate-500">${item.jenis_poli || 'Poli Umum'}</span>
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
            Swal.fire({
                title: 'Gagal mengambil nomor',
                text: kesalahan.message || 'Terjadi kesalahan. Silakan coba lagi.',
                icon: 'error',
                confirmButtonText: 'OK',
            });
        }

        function tampilkanNomorAntrian(hasil) {
            Swal.fire({
                title: 'Nomor berhasil diambil',
                text: `Nomor antrian Anda ${hasil.data.kode_antrian}.`,
                icon: 'success',
                confirmButtonText: 'OK',
            });
        }

        document.getElementById('form-ambil-desktop').addEventListener('submit', async function (e) {
            e.preventDefault();
            const namaEl = document.getElementById('nama_pasien_desktop');
            const poliEl = document.getElementById('jenis_poli_desktop');
            try {
                const hasil = await kirimAntrianBaru(namaEl.value, poliEl.value);
                namaEl.value = '';
                tampilkanNomorAntrian(hasil);
            } catch (kesalahan) {
                tampilkanErrorAntrian(kesalahan);
            }
        });

        document.getElementById('form-ambil-mobile').addEventListener('submit', async function (e) {
            e.preventDefault();
            const namaEl = document.getElementById('nama_pasien_mobile');
            const poliEl = document.getElementById('jenis_poli_mobile');
            try {
                const hasil = await kirimAntrianBaru(namaEl.value, poliEl.value);
                namaEl.value = '';
                bukaModalMobile(false);
                tampilkanNomorAntrian(hasil);
            } catch (kesalahan) {
                tampilkanErrorAntrian(kesalahan);
            }
        });

        muatDataAntrian();
        setInterval(muatDataAntrian, 2000);
    </script>
</body>
</html>
