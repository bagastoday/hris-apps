<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - TalentaCore HRIS</title>

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
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e3a8a',
                            900: '#1e3a5f',
                            950: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .wave-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 45%, #2563eb 100%);
            position: relative;
            overflow: hidden;
        }
        .wave-banner::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 40px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 80' preserveAspectRatio='none'%3E%3Cpath fill='%23f8fafc' d='M0,40 C360,80 720,0 1080,40 C1260,60 1380,50 1440,40 L1440,80 L0,80 Z'/%3E%3C/svg%3E") no-repeat bottom;
            background-size: cover;
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-50 text-slate-800" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-brand-950 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10">
            <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center shadow-lg shadow-brand-600/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-sm tracking-tight leading-none">Talenta<span class="text-brand-400">Core</span></p>
                <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wider">HRIS Admin</p>
            </div>
        </div>

        <nav class="px-3 py-5 space-y-1">
            <a href="{{ url('/') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('/') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Beranda
            </a>

            <a href="{{ route('employees.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('employees*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Pegawai
            </a>
            <a href="{{ route('departments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('departments*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Departemen
            </a>

            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Absensi
            </a>

            <a href="{{ route('leaves.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('leaves*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Cuti
            </a>

            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan
            </a>
        </nav>

        <div class="absolute bottom-0 left-0 right-0 px-4 py-4 border-t border-white/10">
            <p class="text-[11px] text-slate-500 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                TalentaCore · Online
            </p>
        </div>
    </aside>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
         class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 shrink-0">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-base font-semibold text-slate-900">@yield('page-title', 'Beranda')</h1>
                    <p class="text-xs text-slate-500 hidden sm:block">@yield('page-subtitle', 'Ringkasan manajemen SDM')</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 text-xs font-medium text-slate-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    admin
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                        Keluar
                    </button>
                </form>
                <a href="#" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Pegawai
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>