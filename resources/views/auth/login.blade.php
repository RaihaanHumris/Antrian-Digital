<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otentikasi Administrator — Klinik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-image: url("{{ route('gambar.bg-login') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .font-mono-num { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 lg:p-8 relative">

    <!-- OVERLAY AMBIENT HALUS UNTUK KONTEN -->
    <div class="fixed inset-0 bg-white/30 backdrop-blur-[2px] pointer-events-none"></div>

    <!-- KARTU LOGIN UTAMA -->
    <div class="w-full max-w-4xl bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-2xl rounded-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 relative z-10">

        <!-- PANEL KIRI (Khusus Desktop): Identitas Klinik & Informasi -->
        <div class="hidden lg:flex lg:col-span-5 bg-slate-900 text-white p-8 sm:p-10 flex-col justify-between">
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

            <div class="border-t border-slate-800 pt-5 text-xs text-slate-400">
                <span class="block font-semibold text-slate-300">Klinik Kesehatan</span>
                <span class="text-[11px] text-slate-500">Sistem Antrian Pasien Terintegrasi</span>
            </div>
        </div>

        <!-- PANEL KANAN: Form Login Bersih & Elegan -->
        <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between bg-white/90">
            <div>
                <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-6">
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-wider text-slate-900">Login Administrator</h2>
                        <p class="text-xs text-slate-500">Silakan masukkan email dan kata sandi Anda</p>
                    </div>
                    <span class="font-mono-num text-[11px] bg-slate-100 border border-slate-200 px-2 py-1 text-slate-600 rounded">AUTH</span>
                </div>

                @if($errors->any())
                    <div class="mb-4 border border-rose-300 bg-rose-50 text-rose-800 px-3.5 py-2.5 text-xs font-medium rounded-lg">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login.proses') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Alamat Email
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="nama@email.com"
                            class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition">
                    </div>

                    <div>
                        <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                placeholder="Masukkan kata sandi"
                                class="w-full border border-slate-300 rounded-lg pl-3.5 pr-16 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition">
                            <button type="button" onclick="toggleSandi()" id="btn-sandi"
                                class="absolute right-3 top-1/2 -translate-y-1/2 font-mono-num text-[11px] uppercase text-slate-500 hover:text-slate-900 font-bold">
                                LIHAT
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" name="ingat_saya" class="rounded border-slate-300 text-slate-900 focus:ring-0">
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider py-3.5 rounded-lg shadow-sm transition mt-2">
                        Masuk ke Meja Kendali &rarr;
                    </button>
                </form>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-200 flex items-center justify-between text-xs">
                <a href="{{ route('publik.utama') }}" class="font-medium text-slate-600 hover:text-slate-900 transition">
                    &larr; Layar Monitor Publik
                </a>
                <span class="font-mono-num text-[11px] text-slate-400">Klinik Kesehatan</span>
            </div>
        </div>

    </div>

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
    </script>
</body>
</html>
