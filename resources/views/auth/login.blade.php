<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - TalentaCore HRIS</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e3a8a',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        /* Matikan ikon mata bawaan browser Edge / IE */
        input::-ms-reveal,
        input::-ms-clear {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        .card-custom-shadow {
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(226, 232, 240, 0.8);
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen bg-gradient-to-br from-sky-50 via-white to-indigo-50 flex items-center justify-center p-4 sm:p-6 lg:p-8 relative overflow-hidden text-slate-800 selection:bg-brand-800 selection:text-white"
      x-data="{ showPassword: false }">

    {{-- DEKORASI GEOMETRIS LATAR BELAKANG (Sesuai Referensi Gambar dengan Warna Halus Elegan) --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden select-none -z-0">
        
        {{-- Lingkaran Besar & Cincin Outline --}}
        <div class="absolute -top-24 left-1/4 w-80 h-80 rounded-full border-[6px] border-blue-400/20"></div>
        <div class="absolute top-1/3 -left-20 w-96 h-96 rounded-full border-[8px] border-indigo-400/15"></div>
        <div class="absolute -bottom-28 right-12 w-96 h-96 rounded-full border-[6px] border-blue-400/20"></div>
        <div class="absolute -bottom-16 left-1/3 w-64 h-64 rounded-full border-[4px] border-sky-400/20"></div>

        {{-- Setengah Lingkaran Padat --}}
        <div class="absolute top-12 left-1/3 w-40 h-20 rounded-t-full bg-blue-300/15"></div>
        <div class="absolute bottom-16 right-1/4 w-32 h-16 rounded-b-full bg-indigo-300/15"></div>

        {{-- Panah Chevron Bertumpuk Sisi Kiri Atas (Top Left >>>) --}}
        <div class="absolute top-12 left-10 opacity-30 text-blue-500 hidden sm:block">
            <svg class="w-8 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 56">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6l8 8 8-8 M4 18l8 8 8-8 M4 30l8 8 8-8 M4 42l8 8 8-8"/>
            </svg>
        </div>

        {{-- Panah Chevron Bertumpuk Sisi Kanan (Right Center <<<) --}}
        <div class="absolute top-1/2 right-10 -translate-y-1/2 opacity-30 text-indigo-500 hidden sm:block">
            <svg class="w-8 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 56">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 14l8-8 8 8 M4 26l8-8 8 8 M4 38l8-8 8 8 M4 50l8-8 8 8"/>
            </svg>
        </div>

        {{-- Gelombang Wavy Sisi Kanan Tengah --}}
        <div class="absolute top-1/3 right-1/4 opacity-25 text-blue-500 hidden lg:block">
            <svg class="w-20 h-16" fill="none" stroke="currentColor" viewBox="0 0 70 50">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10 Q18 0 32 10 T60 10 M5 24 Q18 14 32 24 T60 24 M5 38 Q18 28 32 38 T60 38"/>
            </svg>
        </div>

        {{-- Gelombang Wavy Sisi Bawah Tengah --}}
        <div class="absolute bottom-6 left-1/3 opacity-30 text-sky-500 hidden sm:block">
            <svg class="w-24 h-12" fill="none" stroke="currentColor" viewBox="0 0 90 40">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 10 Q22 0 40 10 T75 10 M5 26 Q22 16 40 26 T75 26"/>
            </svg>
        </div>

        {{-- Dot Matrix Grid (Titik-Titik Sisi Kanan Atas) --}}
        <div class="absolute top-16 right-16 opacity-35 hidden sm:grid grid-cols-5 gap-3">
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
        </div>

        {{-- Bola Cahaya Blur (Ambient Glow Orbs) --}}
        <div class="absolute -left-20 -top-20 h-96 w-96 rounded-full bg-blue-200/40 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-20 h-96 w-96 rounded-full bg-violet-200/40 blur-3xl"></div>
        <div class="absolute right-1/4 top-1/4 h-40 w-40 rounded-full bg-blue-100/60 blur-3xl"></div>
    </div>

    {{-- CARD LOGIN UTAMA DI TENGAH (Sesuai Referensi Gambar) --}}
    <main class="w-full max-w-4xl bg-white rounded-3xl card-custom-shadow overflow-hidden flex flex-col md:flex-row relative z-10 my-auto border border-slate-100">

        {{-- SISI KIRI: Background Biru Orisinal TalentaCore (Deep Midnight Navy) + 'Hello, welcome!' --}}
        <div class="md:w-1/2 bg-gradient-to-br from-brand-900 via-brand-800 to-brand-950 p-8 sm:p-12 flex flex-col justify-between text-white relative overflow-hidden min-h-[340px] md:min-h-[490px]">
            
            {{-- Geometri Halus di Dalam Panel Kiri --}}
            <div class="absolute -top-12 -right-12 w-56 h-56 rounded-full border-4 border-white/10 pointer-events-none"></div>
            <div class="absolute -bottom-16 -left-16 w-60 h-60 rounded-full border-4 border-white/10 pointer-events-none"></div>
            <div class="absolute top-1/2 right-6 w-32 h-16 rounded-b-full bg-white/5 pointer-events-none"></div>
            
            {{-- Aksen Gelombang Halus Khas TalentaCore --}}
            <svg class="absolute bottom-0 left-0 w-full text-white/[0.05] pointer-events-none" viewBox="0 0 500 180" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,90 C90,150 180,30 270,80 C360,130 420,40 500,90 L500,180 L0,180 Z"/>
            </svg>

            {{-- Dot Matrix Halus di Pojok Kanan Bawah Panel Kiri --}}
            <div class="absolute bottom-6 right-6 opacity-25 grid grid-cols-4 gap-2 pointer-events-none">
                <div class="w-1 h-1 rounded-full bg-white"></div>
                <div class="w-1 h-1 rounded-full bg-white"></div>
                <div class="w-1 h-1 rounded-full bg-white"></div>
                <div class="w-1 h-1 rounded-full bg-white"></div>
                <div class="w-1 h-1 rounded-full bg-white"></div>
                <div class="w-1 h-1 rounded-full bg-white"></div>
                <div class="w-1 h-1 rounded-full bg-white"></div>
                <div class="w-1 h-1 rounded-full bg-white"></div>
            </div>

            {{-- Brand Logo (Top Left: YOUR LOGO) --}}
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur flex items-center justify-center ring-1 ring-white/15 text-white shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="font-extrabold text-base tracking-tight text-white">Talenta<span class="text-brand-200">Core</span></span>
                    <span class="block text-[9px] uppercase tracking-wider text-indigo-200/80 font-bold -mt-0.5">HRIS Platform</span>
                </div>
            </div>

            {{-- Pesan Ucapan Utama (Hello, welcome!) Sesuai Referensi Gambar --}}
            <div class="my-auto py-8 relative z-10">
                <h1 class="text-3xl sm:text-4xl lg:text-[42px] font-black tracking-tight leading-tight mb-3">
                    Hello,<br>
                    <span class="text-blue-200">welcome!</span>
                </h1>
                <p class="text-indigo-100/80 text-xs sm:text-sm leading-relaxed max-w-xs">
                    Kelola pegawai, absensi realtime, cuti digital, dan manajemen SDM perusahaan Anda dalam satu platform terpadu.
                </p>
            </div>

            {{-- Footer Panel Kiri --}}
            <div class="relative z-10 pt-4 border-t border-white/10 text-xs text-indigo-200/70">
                <span>&copy; {{ date('Y') }} TalentaCore Enterprise</span>
            </div>
        </div>

        {{-- SISI KANAN: Formulir Login Putih Bersih --}}
        <div class="md:w-1/2 p-8 sm:p-12 flex flex-col justify-center bg-white">
            
            {{-- Pesan Eror Validasi --}}
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium leading-relaxed">{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Form Login Sesuai Desain Referensi Gambar --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Input Email atau NIK --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Email address</label>
                    <div class="relative">
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-800/10 focus:border-brand-800 transition shadow-2xs"
                               placeholder="Email atau NIK">
                    </div>
                </div>

                {{-- Input Password --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" required
                               class="w-full pl-4 pr-11 py-3 rounded-xl border border-slate-300 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-800/10 focus:border-brand-800 transition shadow-2xs"
                               placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition z-10"
                                title="Tampilkan / Sembunyikan Password"
                                tabindex="-1">
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Baris Ingat Saya & Ubah Password --}}
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer select-none group">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brand-700 focus:ring-brand-700">
                        <span class="font-medium group-hover:text-slate-900 transition">Remember me</span>
                    </label>
                    <a href="{{ route('password.change') }}" class="font-semibold text-brand-700 hover:text-brand-900 transition">
                        Forgot password?
                    </a>
                </div>

                {{-- Tombol Login Diperpanjang Full-Width & Center --}}
                <div class="pt-3">
                    <button type="submit"
                            class="w-full py-3 px-4 bg-brand-800 hover:bg-brand-900 active:bg-brand-950 text-white text-sm font-bold rounded-xl transition duration-150 shadow-md shadow-brand-900/25 hover:shadow-lg flex items-center justify-center">
                        Login
                    </button>
                </div>
            </form>

            <p class="text-center text-[11px] text-slate-400 mt-6 leading-relaxed">
                Demo: <span class="font-medium text-slate-500">hr@talentacore.id</span> &bull; Password: <span class="font-medium text-slate-500">password</span>
            </p>

        </div>

    </main>

</body>
</html>