@extends('layouts.admin')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan manajemen SDM')

@section('content')
{{-- Welcome Banner --}}
<div class="wave-banner rounded-2xl p-6 sm:p-8 mb-6 text-white relative overflow-hidden shadow-sm">
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="max-w-xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-medium mb-3 backdrop-blur-sm border border-white/20">
                <svg class="w-3.5 h-3.5 text-blue-200" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/></svg>
                <span>TALENTACORE &middot; {{ strtoupper(now()->locale('id')->translatedFormat('l, d F Y')) }}</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2">Selamat Datang di Portal HR!</h2>
            <p class="text-blue-100/90 text-sm sm:text-base leading-relaxed mb-6">
                Kelola data pegawai, absensi, pengajuan cuti, dan laporan operasional SDM dalam satu dashboard terpadu.
            </p>
            {{-- Widget Waktu Realtime --}}
            <div x-data="{
                time: '{{ now()->format('H:i:s') }} WIB',
                init() {
                    const update = () => {
                        const now = new Date();
                        this.time = now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
                    };
                    update();
                    setInterval(update, 1000);
                }
            }" class="inline-flex items-center gap-3 px-4 py-2.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-white shadow-sm">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                </span>
                <span class="text-xs font-semibold uppercase tracking-wider text-blue-200">Waktu Realtime:</span>
                <span class="font-mono text-base sm:text-lg font-bold tracking-wider text-white" x-text="time">{{ now()->format('H:i:s') }} WIB</span>
            </div>
        </div>

        {{-- Widget Ringkasan Kanan --}}
        <div class="hidden sm:block shrink-0">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 text-right min-w-[220px]">
                <p class="text-xs text-blue-200 uppercase tracking-wider font-semibold">Total Pegawai Aktif</p>
                <p class="text-4xl font-black tracking-tight text-white my-1">{{ $totalEmployees }}</p>
                <div class="flex items-center justify-end gap-2 text-xs text-blue-100/90 pt-1 border-t border-white/15">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        {{ $cutiPending }} cuti pending
                    </span>
                    <span>&bull;</span>
                    <span>{{ $totalDepartments }} divisi</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Aksen lingkaran melayang --}}
    <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/5 pointer-events-none banner-float-a"></div>
    <div class="absolute right-20 top-4 w-24 h-24 rounded-full bg-white/5 pointer-events-none banner-float-b"></div>

    {{-- Gelombang berjalan --}}
    <div class="absolute inset-x-0 bottom-0 h-20 sm:h-24 overflow-hidden pointer-events-none">
        <div class="banner-wave-track banner-wave-track--slow flex h-full w-[200%]">
            <svg class="w-1/2 h-full shrink-0 text-white/10" viewBox="0 0 1000 120" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,60 C150,110 350,10 500,60 C650,110 850,10 1000,60 L1000,120 L0,120 Z"/>
            </svg>
            <svg class="w-1/2 h-full shrink-0 text-white/10" viewBox="0 0 1000 120" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,60 C150,110 350,10 500,60 C650,110 850,10 1000,60 L1000,120 L0,120 Z"/>
            </svg>
        </div>
        <div class="banner-wave-track banner-wave-track--fast flex h-full w-[200%] -mt-14 sm:-mt-16">
            <svg class="w-1/2 h-full shrink-0 text-white/[0.14]" viewBox="0 0 1000 120" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,80 C120,30 260,110 400,70 C540,30 680,100 820,60 C900,40 950,55 1000,70 L1000,120 L0,120 Z"/>
            </svg>
            <svg class="w-1/2 h-full shrink-0 text-white/[0.14]" viewBox="0 0 1000 120" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,80 C120,30 260,110 400,70 C540,30 680,100 820,60 C900,40 950,55 1000,70 L1000,120 L0,120 Z"/>
            </svg>
        </div>
    </div>
</div>

<style>
    @keyframes banner-wave-scroll {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }
    .banner-wave-track { animation: banner-wave-scroll linear infinite; will-change: transform; }
    .banner-wave-track--slow { animation-duration: 16s; }
    .banner-wave-track--fast { animation-duration: 9s; animation-direction: reverse; }

    @keyframes banner-float {
        0%, 100% { transform: translate(0, 0); }
        50%      { transform: translate(-6px, -10px); }
    }
    .banner-float-a { animation: banner-float 7s ease-in-out infinite; }
    .banner-float-b { animation: banner-float 5.5s ease-in-out infinite 0.6s; }

    @media (prefers-reduced-motion: reduce) {
        .banner-wave-track, .banner-float-a, .banner-float-b { animation: none; }
    }
</style>

{{-- Kartu Statistik Utama --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Pegawai Aktif --}}
    <a href="{{ route('employees.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition-all">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai</span>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalEmployees }}</p>
        <div class="flex items-center justify-between text-xs text-slate-500 mt-2">
            <span>Total pegawai aktif</span>
            <span class="font-medium text-brand-600 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
        </div>
    </a>

    {{-- Hadir Hari Ini --}}
    <a href="{{ route('attendances.index', ['date' => $today]) }}" class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Hadir Hari Ini</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $hadirHariIni }}</p>
        <div class="flex items-center justify-between text-xs text-slate-500 mt-2">
            <span class="truncate">{{ $hadirTepatWaktuHariIni }} tepat waktu &middot; {{ $terlambatHariIni }} terlambat</span>
            <span class="font-medium text-emerald-600 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
        </div>
    </a>

    {{-- Cuti Pending --}}
    <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-amber-200 transition-all">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cuti Pending</span>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $cutiPending }}</p>
        <div class="flex items-center justify-between text-xs text-slate-500 mt-2">
            <span>Menunggu persetujuan</span>
            <span class="font-medium text-amber-600 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
        </div>
    </a>

    {{-- Departemen --}}
    <a href="{{ route('departments.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-violet-200 transition-all">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Departemen</span>
            <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalDepartments }}</p>
        <div class="flex items-center justify-between text-xs text-slate-500 mt-2">
            <span>Departemen aktif</span>
            <span class="font-medium text-violet-600 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
        </div>
    </a>

</div>

{{-- Section Perlu Ditindaklanjuti --}}
<section class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-4">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Perlu ditindaklanjuti</h2>
            <p class="text-xs sm:text-sm text-slate-500">Antrean tugas dan verifikasi data yang membutuhkan perhatian tim HR.</p>
        </div>
        <span class="text-xs font-medium text-slate-400">Sinkronisasi terakhir: {{ now()->format('H:i') }} WIB</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- 1. Pengajuan Cuti --}}
        <article class="flex flex-col justify-between h-full bg-white rounded-2xl border border-amber-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2Z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 text-sm">Pengajuan Cuti</h3>
                            <p class="text-xs text-slate-500">Menunggu persetujuan</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-amber-50 border border-amber-200/60 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                        {{ $cutiPending }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @forelse($pendingLeaves->take(3) as $leave)
                        <div class="flex items-center justify-between gap-3 text-xs p-2.5 rounded-xl bg-slate-50/80 border border-slate-100">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800 truncate">{{ $leave->employee->full_name ?? 'Pegawai tidak ditemukan' }}</p>
                                <p class="text-[11px] text-slate-400 capitalize">{{ str_replace('_', ' ', $leave->leave_type) }}</p>
                            </div>
                            <span class="shrink-0 text-[11px] font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                                {{ $leave->start_date->format('d M') }}
                            </span>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-center">
                            <p class="text-xs text-slate-500">Tidak ada pengajuan cuti yang pending.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 mt-auto">
                <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-800 transition">
                    Tinjau semua pengajuan
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </article>

        {{-- 2. Belum Absen --}}
        <article class="flex flex-col justify-between h-full bg-white rounded-2xl border border-sky-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600 shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v5l3 2"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 text-sm">Belum Tercatat Absensi</h3>
                            <p class="text-xs text-slate-500">Pegawai aktif (di luar cuti)</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-sky-50 border border-sky-200/60 px-2.5 py-0.5 text-xs font-bold text-sky-700">
                        {{ $notCheckedInCount }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @forelse($notCheckedInEmployees->take(3) as $employee)
                        <div class="flex items-center justify-between gap-3 text-xs p-2.5 rounded-xl bg-slate-50/80 border border-slate-100">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800 truncate">{{ $employee->full_name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $employee->employee_code }}</p>
                            </div>
                            <span class="shrink-0 text-[11px] text-slate-500 bg-white px-2 py-0.5 rounded-md border border-slate-200/60">
                                {{ $employee->department->name ?? 'Tanpa Divisi' }}
                            </span>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-center">
                            <p class="text-xs text-slate-500">Semua pegawai aktif telah mencatatkan kehadiran.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 mt-auto">
                <a href="{{ route('attendances.index', ['date' => $today]) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-sky-700 hover:text-sky-800 transition">
                    Lihat absensi hari ini
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </article>

        {{-- 3. Data Belum Lengkap --}}
        <article class="flex flex-col justify-between h-full bg-white rounded-2xl border border-violet-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4m0 4h.01M4.93 19h14.14a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.2 16a2 2 0 001.73 3Z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 text-sm">Data Pegawai Kosong</h3>
                            <p class="text-xs text-slate-500">Divisi atau jabatan belum diisi</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-violet-50 border border-violet-200/60 px-2.5 py-0.5 text-xs font-bold text-violet-700">
                        {{ $incompleteEmployeeCount }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @forelse($incompleteEmployees->take(3) as $employee)
                        <div class="flex items-center justify-between gap-3 text-xs p-2.5 rounded-xl bg-slate-50/80 border border-slate-100">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800 truncate">{{ $employee->full_name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $employee->employee_code }}</p>
                            </div>
                            <span class="shrink-0 text-[10px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                                {{ !$employee->department_id ? 'Divisi Kosong' : 'Jabatan Kosong' }}
                            </span>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-center">
                            <p class="text-xs text-slate-500">Seluruh data pegawai sudah terisi lengkap.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 mt-auto">
                <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-violet-700 hover:text-violet-800 transition">
                    Lengkapi data pegawai
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </article>

    </div>
</section>

{{-- Bottom sections: Presensi Hari Ini & Tabel Pengajuan Cuti --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

    {{-- Kolom Kiri: Ringkasan Presensi Hari Ini (5 cols) --}}
    <div class="xl:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between overflow-hidden">
        <div>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Status Kehadiran Hari Ini</h3>
                        <p class="text-[11px] text-slate-400">{{ now()->locale('id')->translatedFormat('l, d M Y') }}</p>
                    </div>
                </div>
                <span class="text-[11px] font-semibold text-brand-700 bg-brand-50 border border-brand-100 px-2.5 py-1 rounded-full">
                    Realtime
                </span>
            </div>

            {{-- Breakdown Angka 4 Status --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-5">
                <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl p-3 text-center">
                    <p class="text-[10px] uppercase tracking-wider text-emerald-700 font-bold">Tepat Waktu</p>
                    <p class="text-2xl font-black text-emerald-900 mt-1">{{ $hadirTepatWaktuHariIni }}</p>
                </div>
                <div class="bg-amber-50/70 border border-amber-100 rounded-xl p-3 text-center">
                    <p class="text-[10px] uppercase tracking-wider text-amber-700 font-bold">Terlambat</p>
                    <p class="text-2xl font-black text-amber-900 mt-1">{{ $terlambatHariIni }}</p>
                </div>
                <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3 text-center">
                    <p class="text-[10px] uppercase tracking-wider text-blue-700 font-bold">Cuti</p>
                    <p class="text-2xl font-black text-blue-900 mt-1">{{ $cutiHariIni }}</p>
                </div>
                <div class="bg-rose-50/70 border border-rose-100 rounded-xl p-3 text-center">
                    <p class="text-[10px] uppercase tracking-wider text-rose-700 font-bold">Alpha</p>
                    <p class="text-2xl font-black text-rose-900 mt-1">{{ $alphaHariIni }}</p>
                </div>
            </div>

            {{-- Progress Rasio Kehadiran --}}
            <div class="px-5 pb-5">
                @php
                    $attendanceRate = $totalEmployees > 0 ? round(($hadirHariIni / $totalEmployees) * 100) : 0;
                @endphp
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <span class="font-semibold text-slate-700">Rasio Kehadiran Pegawai</span>
                        <span class="font-bold text-slate-900">{{ $attendanceRate }}% ({{ $hadirHariIni }} / {{ $totalEmployees }})</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $attendanceRate }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">
                        {{ $totalAttendanceHariIni }} catatan absensi berhasil tercatat pada sistem hari ini.
                    </p>
                </div>
            </div>
        </div>

        <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 text-center">
            <a href="{{ route('attendances.index', ['date' => $today]) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-800 transition">
                Buka rekap presensi lengkap
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    {{-- Kolom Kanan: Pengajuan Cuti Terbaru (7 cols) --}}
    <div class="xl:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between overflow-hidden">
        <div>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Pengajuan Cuti Pending</h3>
                        <p class="text-[11px] text-slate-400">Daftar permohonan yang menunggu verifikasi HR</p>
                    </div>
                </div>
                <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-500 uppercase tracking-wider text-[10px]">
                            <th class="px-5 py-3 font-semibold">Pegawai</th>
                            <th class="px-4 py-3 font-semibold">Jenis Cuti</th>
                            <th class="px-4 py-3 font-semibold">Periode</th>
                            <th class="px-5 py-3 font-semibold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingLeaves as $leave)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($leave->employee->full_name ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-800 truncate">{{ $leave->employee->full_name ?? '-' }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $leave->employee->department->name ?? 'Umum' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium capitalize text-[11px]">
                                        {{ str_replace('_', ' ', $leave->leave_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 font-medium whitespace-nowrap">
                                    {{ $leave->start_date->format('d M') }} &ndash; {{ $leave->end_date->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pending
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <p class="text-xs font-medium text-slate-600">Tidak ada pengajuan cuti pending</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Semua permohonan cuti telah diproses dengan baik.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-400">Total antrean: <strong class="text-slate-700">{{ $cutiPending }} pengajuan</strong></span>
            <a href="{{ route('leaves.index') }}" class="font-semibold text-brand-600 hover:text-brand-800 transition">
                Buka Manajemen Cuti &rarr;
            </a>
        </div>
    </div>

</div>
@endsection