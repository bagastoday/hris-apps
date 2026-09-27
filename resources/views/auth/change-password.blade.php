<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Password - TalentaCore</title>

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
<body class="font-sans antialiased min-h-screen bg-gradient-to-br from-brand-950 via-brand-900 to-slate-950 flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">

        {{-- Header: banner biru tua dengan aksen gelombang, senada dengan halaman login --}}
        <div class="bg-gradient-to-br from-brand-900 via-brand-800 to-brand-950 pt-8 pb-14 px-8 relative overflow-hidden text-white">

            <svg class="absolute bottom-0 left-0 w-full text-white/[0.06]" viewBox="0 0 500 150" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,90 C90,140 180,30 270,80 C360,120 420,40 500,90 L500,150 L0,150 Z"/>
            </svg>
            <svg class="absolute bottom-0 left-0 w-full text-white/[0.09]" viewBox="0 0 500 120" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,100 C100,60 200,110 300,80 C380,55 440,100 500,70 L500,120 L0,120 Z"/>
            </svg>
            <div class="absolute -top-8 -right-8 w-24 h-24 rounded-full bg-white/5"></div>

            <div class="relative z-10 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white/10 backdrop-blur ring-1 ring-white/10 shadow-lg mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold">Ubah Password</h1>
                <p class="text-sm text-indigo-100/70 mt-1">Amankan akun Anda dengan password baru</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="p-8 pt-6">

            @if ($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-600 text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.change.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition"
                           placeholder="email@perusahaan.com">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Password Lama</label>
                    <input type="password" name="current_password" required
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition"
                           placeholder="Password dari HR">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Password Baru</label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition"
                           placeholder="Minimal 6 karakter">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition"
                           placeholder="Ulangi password baru">
                </div>

                <button type="submit"
                        class="w-full py-3 bg-brand-800 hover:bg-brand-900 text-white text-sm font-semibold rounded-xl transition shadow-md shadow-brand-900/30 hover:shadow-lg hover:shadow-brand-900/40">
                    Simpan Password Baru
                </button>
            </form>

            <p class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm text-brand-700 hover:text-brand-800 font-medium">
                    ← Kembali ke Login
                </a>
            </p>
        </div>
    </div>
</body>
</html>