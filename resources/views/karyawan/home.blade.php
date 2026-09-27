<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Karyawan - TalentaCore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { brand: { 600: '#2563eb', 700: '#1d4ed8' } }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">
    <div class="max-w-lg mx-auto px-4 py-12 text-center">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-brand-600 flex items-center justify-center mb-6 shadow-lg shadow-brand-600/30">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Portal Karyawan</h1>
        <p class="text-slate-500 mb-1">Halo, <strong>{{ auth()->user()->name }}</strong></p>
        <p class="text-sm text-slate-400 mb-8">Halaman absensi In/Out akan dikerjakan developer lain.</p>

        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm mb-6">
            <p class="text-sm text-slate-500 mb-4">Fitur yang akan tersedia:</p>
            <ul class="text-left text-sm text-slate-600 space-y-2">
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                    Absensi Check-in / Check-out
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                    Riwayat kehadiran
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                    Pengajuan cuti
                </li>
            </ul>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-slate-500 hover:text-red-600 transition">
                Keluar
            </button>
        </form>
    </div>
</body>
</html>