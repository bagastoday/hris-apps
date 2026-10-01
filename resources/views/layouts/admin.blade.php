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
                <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wider">{{ auth()->user()?->isFinance() ? 'Finance & Accounting' : 'HRIS Admin' }}</p>
            </div>
        </div>

        <nav class="px-3 py-5 space-y-1">
            @if(auth()->user()?->isFinance())
                <a href="{{ route('finance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->is('finance') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.12-3 2.5S10.343 13 12 13s3 1.12 3 2.5S13.657 18 12 18m0-10V6m0 2v10m0 0v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Payroll
                    @if($financeActionCount > 0)
                        <span class="ml-auto rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-800">{{ $financeActionCount }} tindakan</span>
                    @endif
                </a>
                <a href="{{ route('finance.salaries') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->is('finance/salaries') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.12-3 2.5S10.343 13 12 13s3 1.12 3 2.5S13.657 18 12 18m0-10V6m0 2v10m0 0v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Gaji Pokok
                </a>
                <a href="{{ route('finance.reimbursements') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->is('finance/reimbursements*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.042-.133-2.053-.382-3.016z"/></svg>
                    Approval Reimburse
                    @if($pendingReimbursementCount > 0)
                        <span class="ml-auto rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-800">{{ $pendingReimbursementCount }}</span>
                    @endif
                </a>
                <a href="{{ route('finance.transactions') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->is('finance/transactions*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7l1 13h12l1-13M9 7V4h6v3m-6 5h6m-6 4h6"/></svg>
                    Kas & Pengeluaran
                </a>
                <a href="{{ route('karyawan.home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->is('karyawan*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Portal Karyawan
                </a>
                <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->is('tickets*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Tiket Finance
                    @if($ticketUnreadCount > 0)
                        <span class="ml-auto rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">{{ $ticketUnreadCount }} tiket baru</span>
                    @endif
                </a>
            @else
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

            <a href="{{ route('attendances.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('attendances*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Absensi
            </a>

            <a href="{{ route('roster.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('roster*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Roster
            </a>

            <a href="{{ route('leaves.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('leaves*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Cuti
                @if($pendingLeaveCount > 0)
                    <span class="ml-auto rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-800">{{ $pendingLeaveCount }} perlu diproses</span>
                @endif
            </a>

            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('reports*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan
            </a>

            <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('announcements*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Pengumuman
            </a>

            <a href="{{ route('activity-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('activity-logs*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Log Aktivitas
            </a>

            <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('tickets*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Tiket Bantuan
                @if($ticketUnreadCount > 0)
                    <span class="ml-auto rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">{{ $ticketUnreadCount }} tiket baru</span>
                @endif
            </a>

            <a href="{{ route('karyawan.home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                {{ request()->is('karyawan*') ? 'bg-white text-brand-900 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Portal Karyawan
            </a>
            @endif
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

            <div class="flex items-center gap-4">
                @if(!request()->is('tickets*'))
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100/80 text-xs text-slate-600 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="admin-live-clock" class="font-mono font-semibold text-slate-800">--:--:-- WIB</span>
                </div>
                @endif

                {{-- Tombol Profil + Popup --}}
                <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" type="button"
                        class="group flex items-center gap-2 sm:gap-2.5 pl-1.5 pr-2 sm:pr-3 py-1.5 rounded-full border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 shadow-sm transition-all"
                        :class="open && 'border-brand-300 bg-brand-50/60 ring-2 ring-brand-100'">
                    <div class="relative shrink-0">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 overflow-hidden flex items-center justify-center text-xs font-bold uppercase text-white ring-2 ring-white shadow-sm">
                            @if(auth()->user()->avatar ?? false)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-full h-full object-cover" alt="">
                            @else
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            @endif
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-white"></span>
                    </div>
                    <span class="hidden sm:block text-left leading-tight">
                        <span class="block text-xs font-semibold text-slate-800 max-w-[110px] truncate">{{ auth()->user()->name ?? 'Profil' }}</span>
                        <span class="block text-[10px] text-slate-400 uppercase tracking-wide">{{ auth()->user()->display_title }}</span>
                    </span>
                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 shrink-0"
                         :class="open && 'rotate-180 text-brand-600'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open"
                     @click.outside="open = false"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2.5 w-72 origin-top-right bg-white rounded-2xl ring-1 ring-slate-900/5 shadow-xl shadow-slate-300/40 z-50 overflow-hidden">

                    {{-- Header --}}
                    <div class="relative bg-gradient-to-br from-brand-600 to-brand-800 px-5 py-4 text-white overflow-hidden">
                        <div class="absolute -top-6 -right-6 w-20 h-20 rounded-full bg-white/10"></div>
                        <div class="relative z-10 flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-white/15 overflow-hidden flex items-center justify-center text-lg font-bold uppercase shrink-0 ring-2 ring-white/30">
                                @if(auth()->user()->avatar ?? false)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-full h-full object-cover" alt="">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-blue-100/80 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Data singkat --}}
                    <div class="px-5 py-3.5 flex items-center gap-2 border-b border-slate-100">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-brand-50 text-brand-700">
                            {{ auth()->user()->display_title }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-50 text-slate-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            {{ auth()->user()->nik ?? auth()->user()->employee?->employee_code ?? auth()->user()->id }}
                        </span>
                        @if(auth()->user()->employee?->department)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-50 text-slate-500 truncate">
                            {{ auth()->user()->employee->department->name }}
                        </span>
                        @endif
                    </div>

                    {{-- Menu --}}
                    <div class="px-2 py-2 space-y-0.5">
                        <a href="{{ route('profile.edit') }}"
                           class="group/item flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-brand-50/70 transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 group-hover/item:bg-brand-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <span class="font-medium">Edit Profil</span>
                            <svg class="w-3.5 h-3.5 text-slate-300 ml-auto opacity-0 -translate-x-1 group-hover/item:opacity-100 group-hover/item:translate-x-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('password.change') }}"
                           class="group/item flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-brand-50/70 transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 group-hover/item:bg-brand-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </span>
                            <span class="font-medium">Ubah Password</span>
                            <svg class="w-3.5 h-3.5 text-slate-300 ml-auto opacity-0 -translate-x-1 group-hover/item:opacity-100 group-hover/item:translate-x-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <div class="my-1.5 border-t border-slate-100"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="group/item flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <span class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center shrink-0 group-hover/item:bg-red-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </span>
                                <span class="font-medium">Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6">
            @yield('content')
        </main>
    </div>
</div>

{{-- Global Confirmation Modal --}}
<div x-data="{
    open: false,
    title: 'Konfirmasi Tindakan',
    message: '',
    confirmText: 'Ya, Lanjutkan',
    targetForm: null,
    trigger(form, msg, btnText = 'Ya, Lanjutkan', titleText = 'Konfirmasi Tindakan') {
        this.targetForm = form;
        this.message = msg;
        this.confirmText = btnText;
        this.title = titleText;
        this.open = true;
    },
    submit() {
        if (this.targetForm) {
            this.targetForm._confirmed = true;
            this.targetForm.submit();
        }
        this.open = false;
    }
}"
@open-confirm.window="trigger($event.detail.form, $event.detail.message, $event.detail.confirmText, $event.detail.title)"
x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div @click.away="open = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <h4 class="font-bold text-base text-slate-900" x-text="title"></h4>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed" x-text="message"></p>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" @click="open = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
            <button type="button" @click="submit()" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-red-600 hover:bg-red-700 shadow-md shadow-red-600/20 transition" x-text="confirmText"></button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('submit', function (e) {
        const form = e.target;
        const msg = form.getAttribute('data-confirm');
        if (msg && !form._confirmed) {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('open-confirm', {
                detail: {
                    form: form,
                    message: msg,
                    confirmText: form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan',
                    title: form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan'
                }
            }));
        }
    });

    @if(!request()->is('tickets*'))
    function updateAdminClock() {
        const el = document.getElementById('admin-live-clock');
        if (!el) return;
        const now = new Date();
        const timeStr = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hourCycle: 'h23',
        }).format(now);
        el.textContent = timeStr + ' WIB';
    }
    setInterval(updateAdminClock, 1000);
    updateAdminClock();
    @endif
</script>
</body>
</html>