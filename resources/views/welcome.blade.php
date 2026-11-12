<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Layar Antrian Online - PT Medika Digital Nusantara</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 font-sans">

    <!-- Header / Navigasi Atas -->
    <header class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-teal-600 flex items-center justify-center text-white font-bold text-xl">
                    M
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900">PT Medika Digital Nusantara</h1>
                    <p class="text-xs text-slate-500">Monitor Informasi Antrian Pasien Realtime</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span id="jam-server" class="text-sm font-mono bg-slate-100 px-3 py-1.5 rounded-md text-slate-600">--:--:--</span>
                <a href="{{ route('panel.dashboard') }}" class="text-sm font-medium text-teal-600 hover:text-teal-700">
                    Login Petugas / Admin &rarr;
                </a>
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Bagian Kiri: Nomor Antrian Saat Ini & Form Ambil Tiket Mandiri -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            
            <!-- Kartu Nomor Panggilan Aktif -->
            <div id="kartu-panggilan" class="bg-white rounded-2xl shadow-md border border-teal-100 p-8 text-center transition-all duration-300">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-ping"></span>
                    Nomor Antrian Saat Ini
                </span>

                <div id="teks-kode-antrian" class="text-7xl font-extrabold text-teal-600 my-6 tracking-tight">
                    ---
                </div>

                <p id="teks-nama-pasien" class="text-base font-semibold text-slate-700">
                    Belum ada panggilan aktif
                </p>
                <p id="teks-info-loket" class="text-xs text-slate-400 mt-1"></p>
            </div>

            <!-- Form Ambil Nomor Antrian Mandiri oleh Pengunjung -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-base font-bold text-slate-800 mb-1">Ambil Tiket Antrian Mandiri</h2>
                <p class="text-xs text-slate-500 mb-4">Silakan isi nama dan poli tujuan Anda untuk mengambil nomor antrian.</p>

                <form id="form-ambil-antrian" class="space-y-3">
                    <input type="text" id="input_nama_pasien" placeholder="Masukkan Nama Anda" required
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    
                    <select id="input_jenis_poli" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="Poli Umum">Poli Umum</option>
                        <option value="Poli Gigi">Poli Gigi</option>
                        <option value="Poli Anak">Poli Anak</option>
                    </select>

                    <button type="submit" 
                        class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 px-4 rounded-xl shadow-sm transition">
                        + Ambil Nomor Antrian Sekarang
                    </button>
                </form>
            </div>
        </div>

        <!-- Bagian Kanan: Tabel Daftar Antrian Realtime -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Daftar Antrian Hari Ini</h2>
                        <p class="text-xs text-slate-500">Diperbarui otomatis tanpa perlu refresh halaman</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-500 block">Sisa Menunggu</span>
                        <span id="angka-menunggu" class="text-xl font-bold text-teal-600">0</span>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase sticky top-0">
                            <tr>
                                <th class="py-3.5 px-6 font-semibold">No. Antrian</th>
                                <th class="py-3.5 px-6 font-semibold">Nama Pasien</th>
                                <th class="py-3.5 px-6 font-semibold">Poli</th>
                                <th class="py-3.5 px-6 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody id="isi-tabel-antrian" class="divide-y divide-slate-100 text-sm">
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">Sedang memuat data antrian...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Script JavaScript untuk Realtime Update -->
    <script>
        let kodeTerakhirDipanggil = null;

        // Fungsi untuk membuat label warna sesuai status antrian
        function buatLabelStatus(status) {
            const daftarLabel = {
                'dipanggil': '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800 animate-pulse">Sedang Dipanggil</span>',
                'menunggu': '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Menunggu</span>',
                'selesai': '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Selesai</span>',
                'dilewati': '<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Dilewati</span>'
            };
            return daftarLabel[status] || status;
        }

        // Fungsi utama pengambil data dari server (AJAX Polling)
        async function muatDataAntrian() {
            try {
                const respon = await fetch("{{ route('publik.data') }}", {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await respon.json();

                // 1. Perbarui jam server & jumlah antrian menunggu
                document.getElementById('jam-server').textContent = data.jam_server;
                document.getElementById('angka-menunggu').textContent = data.jumlah_menunggu;

                // 2. Perbarui Kartu Nomor Antrian Saat Ini
                const elemenKode = document.getElementById('teks-kode-antrian');
                const elemenPasien = document.getElementById('teks-nama-pasien');
                const elemenLoket = document.getElementById('teks-info-loket');
                const elemenKartu = document.getElementById('kartu-panggilan');

                if (data.antrian_sekarang) {
                    elemenKode.textContent = data.antrian_sekarang.kode_antrian;
                    elemenPasien.textContent = data.antrian_sekarang.nama_pasien + ' (' + data.antrian_sekarang.jenis_poli + ')';
                    
                    if (data.antrian_sekarang.petugas_pelayan) {
                        const loket = data.antrian_sekarang.petugas_pelayan.nama_loket || 'Loket Pelayanan';
                        elemenLoket.textContent = 'Silakan menuju ke ' + loket + ' (' + data.antrian_sekarang.petugas_pelayan.name + ')';
                    }

                    // Beri efek kedip hijau jika nomor panggilan baru saja berubah
                    if (kodeTerakhirDipanggil !== data.antrian_sekarang.kode_antrian) {
                        kodeTerakhirDipanggil = data.antrian_sekarang.kode_antrian;
                        elemenKartu.classList.add('ring-4', 'ring-teal-400', 'scale-[1.02]');
                        setTimeout(() => elemenKartu.classList.remove('ring-4', 'ring-teal-400', 'scale-[1.02]'), 1200);
                    }
                } else {
                    elemenKode.textContent = '---';
                    elemenPasien.textContent = 'Belum ada panggilan aktif';
                    elemenLoket.textContent = '';
                    kodeTerakhirDipanggil = null;
                }

                // 3. Perbarui Isi Tabel Daftar Antrian
                const isiTabel = document.getElementById('isi-tabel-antrian');
                if (data.daftar_antrian.length === 0) {
                    isiTabel.innerHTML = `<tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada antrian hari ini.</td></tr>`;
                    return;
                }

                isiTabel.innerHTML = data.daftar_antrian.map(item => {
                    const warnaBaris = item.status === 'dipanggil' ? 'bg-teal-50/80 font-semibold' : 'hover:bg-slate-50';
                    return `
                        <tr class="${warnaBaris} transition">
                            <td class="py-3.5 px-6 font-mono font-bold text-slate-800">${item.kode_antrian}</td>
                            <td class="py-3.5 px-6 text-slate-700">${item.nama_pasien}</td>
                            <td class="py-3.5 px-6 text-slate-500 text-xs">${item.jenis_poli}</td>
                            <td class="py-3.5 px-6">${buatLabelStatus(item.status)}</td>
                        </tr>
                    `;
                }).join('');

            } catch (kesalahan) {
                console.error('Gagal mengambil data realtime:', kesalahan);
            }
        }

        // Event saat pengunjung menekan tombol Ambil Nomor Antrian
        document.getElementById('form-ambil-antrian').addEventListener('submit', async function (e) {
            e.preventDefault();
            const inputNama = document.getElementById('input_nama_pasien');
            const inputPoli = document.getElementById('input_jenis_poli');
            const tokenCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const respon = await fetch("{{ route('publik.ambil') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': tokenCsrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nama_pasien: inputNama.value,
                        jenis_poli: inputPoli.value
                    })
                });
                const hasil = await respon.json();

                if (!respon.ok) {
                    throw new Error(hasil.message || hasil.pesan || 'Nomor antrian gagal diambil. Silakan coba lagi.');
                }

                inputNama.value = '';
                muatDataAntrian(); // Langsung perbarui tabel seketika
                Swal.fire({
                    title: 'Nomor berhasil diambil',
                    text: `Nomor antrian Anda ${hasil.data.kode_antrian}.`,
                    icon: 'success',
                    confirmButtonText: 'OK',
                });
            } catch (kesalahan) {
                Swal.fire({
                    title: 'Gagal mengambil nomor',
                    text: kesalahan.message || 'Terjadi kesalahan. Silakan coba lagi.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                });
            }
        });

        // Jalankan pertama kali saat halaman dibuka, lalu ulangi otomatis setiap 2000ms (2 detik)
        muatDataAntrian();
        setInterval(muatDataAntrian, 2000);
    </script>
</body>
</html>