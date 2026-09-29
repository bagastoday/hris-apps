<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - TalentaCore HRIS</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            400: '#5b6ee8',
                            500: '#3448d1',
                            600: '#2536ad',
                            700: '#1c2a8a',
                            800: '#141f66',
                            900: '#0d1547',
                            950: '#080c2e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="relative isolate font-sans antialiased min-h-screen overflow-hidden bg-gradient-to-br from-sky-50 via-white to-indigo-50 flex items-center justify-center p-4 sm:p-6">

    <div aria-hidden="true" class="pointer-events-none absolute -left-24 -top-28 h-80 w-80 rounded-full bg-sky-200/50 blur-3xl"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -right-20 h-96 w-96 rounded-full bg-violet-200/40 blur-3xl"></div>
    <div aria-hidden="true" class="pointer-events-none absolute right-1/4 top-1/4 h-40 w-40 rounded-full bg-blue-100/60 blur-3xl"></div>

    {{-- Card utama --}}
    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[520px]">

        {{-- Kiri: Branding --}}
        <div class="md:w-1/2 bg-gradient-to-br from-brand-900 via-brand-800 to-brand-950 p-8 sm:p-10 flex flex-col justify-between text-white relative overflow-hidden">

            {{-- Aksen gelombang (bawah) --}}
            <svg class="absolute bottom-0 left-0 w-full text-white/[0.06]" viewBox="0 0 500 180" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,90 C90,150 180,30 270,80 C360,130 420,40 500,90 L500,180 L0,180 Z"/>
            </svg>
            <svg class="absolute bottom-0 left-0 w-full text-white/[0.09]" viewBox="0 0 500 150" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,110 C100,60 200,140 300,90 C380,55 440,120 500,80 L500,150 L0,150 Z"/>
            </svg>

            {{-- Aksen lingkaran halus --}}
            <div class="absolute -top-10 -left-10 w-32 h-32 rounded-full bg-white/5"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur flex items-center justify-center ring-1 ring-white/10">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-lg leading-none">Talenta<span class="text-brand-200">Core</span></p>
                        <p class="text-[10px] text-indigo-200/80 uppercase tracking-wider mt-0.5">HRIS Platform</p>
                    </div>
                </div>

                <h2 class="text-2xl sm:text-3xl font-bold leading-snug mb-3">
                    Manajemen SDM modern untuk perusahaan Anda
                </h2>
                <p class="text-indigo-100/80 text-sm leading-relaxed mb-8">
                    Kelola pegawai, absensi, cuti, dan laporan — semua dalam satu ekosistem yang aman dan efisien.
                </p>

                <ul class="space-y-3 text-sm">
                    <li class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-white/10 flex items-center justify-center shrink-0 ring-1 ring-white/10">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Data pegawai & jabatan terpusat
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-white/10 flex items-center justify-center shrink-0 ring-1 ring-white/10">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Absensi real-time & cuti digital
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-white/10 flex items-center justify-center shrink-0 ring-1 ring-white/10">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Role HR & Karyawan terpisah
                    </li>
                </ul>
            </div>

            <p class="relative z-10 text-xs text-indigo-200/60 mt-8">
                © {{ date('Y') }} TalentaCore Enterprise
            </p>
        </div>

        {{-- Kanan: Form Login --}}
        <div class="md:w-1/2 p-8 sm:p-10 flex flex-col justify-center bg-slate-50/50">
            <div class="text-center mb-7">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-800 shadow-lg shadow-brand-900/30 mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-slate-900">Masuk ke TalentaCore</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola SDM perusahaan Anda</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-600 text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Email</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/30 focus:border-brand-700 transition"
                        placeholder="Email atau NIK">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Kata Sandi</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/30 focus:border-brand-700 transition"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-700 focus:ring-brand-700">
                        Ingat saya
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-brand-800 hover:bg-brand-900 text-white text-sm font-semibold rounded-xl transition shadow-md shadow-brand-900/30 hover:shadow-lg hover:shadow-brand-900/40">
                    Masuk
                </button>
                <p class="text-center text-sm text-slate-500 mt-4">
                    <a href="{{ route('password.change') }}" class="text-brand-700 hover:text-brand-800 font-medium">
                        Ubah Password
                    </a>
                </p>
            </form>

            <p class="text-center text-[11px] text-slate-400 mt-6 leading-relaxed">
                Demo: <span class="font-medium text-slate-500">hr@talentacore.id</span>
                Password: <span class="font-medium text-slate-500">password</span>
            </p>
        </div>
    </div>

</body>
</html>