<!-- resources/views/layouts/karyawan.blade.php -->
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Pegawai') - TalentaCore HRIS</title>

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
        .gradient-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #1d4ed8 100%);
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col font-sans" x-data="{ mobileMenu: false, profileMenu: false }">

    {{-- Top Navigation Bar --}}
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30 no-print shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            
            {{-- Brand Logo & Title --}}
            <div class="flex items-center gap-6">
                <a href="{{ route('karyawan.home') }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white shadow-md shadow-brand-600/30 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-base text-slate-900 tracking-tight leading-none">Talenta<span class="text-brand-600">Core</span></span>
                        <span class="block text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Portal Pegawai</span>
                    </div>
                </a>

                {{-- Desktop Navigation Tabs --}}
                <div class="hidden md:flex items-center gap-1.5 ml-2">
                    <a href="{{ route('karyawan.home') }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('karyawan.home') || request()->routeIs('karyawan.attendance*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Presensi Realtime
                    </a>

                    <a href="{{ route('leaves.my') }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('leaves.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Cuti & Izin
                    </a>

                    <a href="{{ route('karyawan.payroll') }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('karyawan.payroll') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.12-3 2.5S10.343 13 12 13s3 1.12 3 2.5S13.657 18 12 18m0-10V6m0 2v10m0 0v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Slip Gaji
                    </a>

                    <a href="{{ route('karyawan.announcements') }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('karyawan.announcements*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        Pengumuman
                    </a>

                    <a href="{{ route('karyawan.tickets') }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('karyawan.tickets*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        Pusat Bantuan
                    </a>
                </div>
            </div>

            {{-- Right Actions & Profile Menu --}}
            <div class="flex items-center gap-3">
                @if(auth()->user()?->isFinance())
                    <a href="{{ route('finance.index') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition border border-emerald-200/60">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Dashboard Finance
                    </a>
                @elseif(auth()->user()?->hasHrAdminAccess())
                    <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition border border-slate-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Dashboard HR
                    </a>
                @endif

                {{-- User Profile Dropdown --}}
                <div class="relative" @click.away="profileMenu = false">
                    <button type="button" @click="profileMenu = !profileMenu"
                            class="flex items-center gap-2 p-1.5 sm:px-3 sm:py-1.5 bg-slate-100 hover:bg-slate-200 rounded-full transition focus:outline-none">
                        @if(auth()->user()?->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-7 h-7 rounded-full object-cover ring-1 ring-white" alt="Avatar">
                        @else
                            <div class="w-7 h-7 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs font-bold uppercase shadow-sm">
                                {{ strtoupper(substr(auth()->user()?->name ?? 'P', 0, 1)) }}
                            </div>
                        @endif
                        <span class="text-xs font-semibold text-slate-700 max-w-[120px] truncate hidden sm:inline">
                            {{ auth()->user()?->name ?? 'Pegawai' }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown Card --}}
                    <div x-show="profileMenu" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()?->name ?? 'Pegawai' }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()?->email ?? auth()->user()?->nik }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Edit Profil
                        </a>
                        <a href="{{ route('password.change') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            Ubah Password
                        </a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Keluar Aplikasi
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Mobile Menu Toggler --}}
                <button type="button" @click="mobileMenu = !mobileMenu"
                        class="md:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Dropdown Menu --}}
        <div x-show="mobileMenu" x-cloak class="md:hidden border-t border-slate-100 bg-white px-4 py-3 space-y-1">
            <a href="{{ route('karyawan.home') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('karyawan.home') || request()->routeIs('karyawan.attendance*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Presensi Realtime
            </a>
            <a href="{{ route('leaves.my') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('leaves.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Cuti & Izin
            </a>
            <a href="{{ route('karyawan.payroll') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('karyawan.payroll') ? 'bg-brand-50 text-brand-700' : 'text-slate-600' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.12-3 2.5S10.343 13 12 13s3 1.12 3 2.5S13.657 18 12 18m0-10V6m0 2v10m0 0v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Slip Gaji
            </a>
            <a href="{{ route('karyawan.announcements') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('karyawan.announcements*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Pengumuman
            </a>
            <a href="{{ route('karyawan.tickets') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('karyawan.tickets*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Pusat Bantuan
            </a>
            @if(auth()->user()?->isFinance())
                <a href="{{ route('finance.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-semibold text-emerald-700 bg-emerald-50">
                    Dashboard Finance
                </a>
            @elseif(auth()->user()?->hasHrAdminAccess())
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-semibold text-slate-700 bg-slate-100">
                    Dashboard HR Admin
                </a>
            @endif
        </div>
    </nav>

    {{-- Main Container --}}
    <main class="flex-1 max-w-6xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

        {{-- Alerts --}}
        @if(!request()->routeIs('karyawan.payroll'))
            @if(session('success'))
                <div class="no-print p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-semibold text-emerald-900">Berhasil</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="no-print p-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 text-sm flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-semibold text-red-900">Perhatian</p>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="no-print p-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 text-sm flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <p class="font-semibold text-red-900">Terdapat kesalahan:</p>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs text-red-700">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-auto py-6 text-center text-xs text-slate-400 border-t border-slate-200/60 no-print">
        <p>&copy; {{ date('Y') }} TalentaCore HRIS &bull; Portal Layanan Mandiri Pegawai</p>
    </footer>

    @stack('scripts')
</body>
</html>
