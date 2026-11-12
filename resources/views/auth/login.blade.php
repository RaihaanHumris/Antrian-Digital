<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otentikasi Administrator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono-num { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen flex items-center justify-center p-4 lg:p-8">

    <div class="w-full max-w-4xl bg-white border border-slate-300 grid grid-cols-1 lg:grid-cols-12">

        <!-- PANEL KIRI (Khusus Desktop): Identitas Klinik & Dokumentasi Singkat -->
        <div class="hidden lg:flex lg:col-span-5 bg-slate-900 text-white p-8 flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 bg-emerald-400"></span>
                    <span class="font-mono-num text-xs uppercase tracking-widest text-slate-300">Antrian Digital</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight mt-6 leading-snug">
                    Sistem Manajemen Antrian Terpadu
                </h1>
                <p class="text-xs text-slate-400 mt-3 leading-relaxed">
                    Panel kendali pemanggilan nomor antrian pasien, pencatatan poli tujuan, dan pembaruan layar monitor publik secara langsung.
                </p>
            </div>

            <div class="border-t border-slate-800 pt-5 space-y-2 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Akun Standar</span>
                    <span class="font-mono-num text-slate-200">admin@medika.id</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Kata Sandi</span>
                    <span class="font-mono-num text-slate-200">password123</span>
                </div>
                <button type="button" onclick="isiKredensial()" class="w-full mt-2 border border-slate-700 hover:border-emerald-500 text-emerald-400 font-mono-num text-[11px] uppercase py-2 transition">
                    [ Klik untuk Isi Otomatis ]
                </button>
            </div>
        </div>

        <!-- PANEL KANAN: Form Login -->
        <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-6">
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-wider text-slate-900">Login Administrator</h2>
                        <p class="text-xs text-slate-500">Masukkan email dan kata sandi akun admin</p>
                    </div>
                    <span class="font-mono-num text-[11px] bg-slate-100 border border-slate-200 px-2 py-1 text-slate-600">AUTH</span>
                </div>

                <form action="{{ route('login.proses') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Alamat Email
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="admin@medika.id"
                            class="w-full border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:border-slate-900">
                    </div>

                    <div>
                        <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                placeholder="Masukkan kata sandi"
                                class="w-full border border-slate-300 pl-3.5 pr-16 py-2.5 text-sm focus:outline-none focus:border-slate-900">
                            <button type="button" onclick="toggleSandi()" id="btn-sandi"
                                class="absolute right-3 top-1/2 -translate-y-1/2 font-mono-num text-[11px] uppercase text-slate-500 hover:text-slate-900">
                                LIHAT
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" name="ingat_saya" class="border-slate-300 text-slate-900 focus:ring-0">
                            <span>Simpan sesi di perangkat ini</span>
                        </label>

                        <!-- Tombol Isi Cepat khusus layar HP -->
                        <button type="button" onclick="isiKredensial()" class="lg:hidden font-mono-num text-[11px] text-emerald-700 underline">
                            Isi Demo Admin
                        </button>
                    </div>

                    <button type="submit"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider py-3.5 transition mt-2">
                        Masuk ke Meja Kendali &rarr;
                    </button>
                </form>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-200 flex items-center justify-between text-xs">
                <a href="{{ route('publik.utama') }}" class="font-medium text-slate-600 hover:text-slate-900">
                    &larr; Layar Monitor Publik
                </a>
                <span class="font-mono-num text-[11px] text-slate-400">PT Medika Digital Nusantara</span>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleSandi() {
            const input = document.getElementById('password');
            const btn = document.getElementById('btn-sandi');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'TUTUP';
            } else {
                input.type = 'password';
                btn.textContent = 'LIHAT';
            }
        }

        function isiKredensial() {
            document.getElementById('email').value = 'admin@medik.id';
            document.getElementById('password').value = 'password123';
        }
    </script>
</body>
</html>