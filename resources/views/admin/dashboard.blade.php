@extends('layouts.admin')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan manajemen SDM')

@section('content')
{{-- Welcome Banner --}}
<div class="wave-banner rounded-2xl p-6 sm:p-8 mb-6 text-white relative overflow-hidden">
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-medium mb-4 backdrop-blur-sm">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/></svg>
                TALENTACORE · {{ strtoupper(now()->translatedFormat('D, d M Y')) }}
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold mb-2">Selamat datang!</h2>
            <p class="text-blue-100 text-sm sm:text-base max-w-md mb-5">
                Kelola data pegawai, absensi, cuti, dan laporan SDM — semua dalam satu tempat.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="#" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-brand-800 text-sm font-semibold rounded-xl hover:bg-blue-50 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Pegawai
                </a>
                <a href="#" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/15 text-white text-sm font-medium rounded-xl hover:bg-white/25 transition backdrop-blur-sm border border-white/20">
                    Lihat Absensi
                </a>
            </div>
        </div>

        <div class="text-right hidden sm:block">
            <p class="text-xs text-blue-200 uppercase tracking-wider mb-1">Total Pegawai Aktif</p>
<p class="text-4xl font-extrabold">{{ $totalEmployees }}</p>
<p class="text-xs text-blue-200 mt-1">{{ $cutiPending }} cuti pending · {{ $totalDepartments }} departemen</p>        </div>
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

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Pegawai --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Pegawai</p>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $totalEmployees }}</p>
        <p class="text-xs text-slate-400 mt-1">total pegawai aktif</p>
    </div>

    {{-- Hadir Hari Ini --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Hadir Hari Ini</p>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $hadirHariIni }}</p>
        <p class="text-xs text-slate-400 mt-1">{{ $terlambatHariIni }} terlambat · {{ $alphaHariIni }} alpha</p>
    </div>

    {{-- Cuti Pending --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Cuti Pending</p>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $cutiPending }}</p>
        <p class="text-xs text-slate-400 mt-1">menunggu persetujuan</p>
    </div>

    {{-- Departemen --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Departemen</p>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $totalDepartments }}</p>
        <p class="text-xs text-slate-400 mt-1">departemen aktif</p>
    </div>

</div>

{{-- Bottom sections --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <h3 class="font-semibold text-slate-900">Hari ini</h3>
            </div>
            <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                {{ now()->translatedFormat('D, d M Y') }}
            </span>
        </div>

<div class="grid grid-cols-4 gap-2 px-5 py-4 border-b border-slate-50">
    <div class="text-center">
        <p class="text-[10px] uppercase tracking-wide text-slate-400 font-medium">Hadir</p>
        <p class="text-xl font-bold text-slate-900">{{ $hadirHariIni }}</p>
    </div>
    <div class="text-center">
        <p class="text-[10px] uppercase tracking-wide text-slate-400 font-medium">Terlambat</p>
        <p class="text-xl font-bold text-slate-900">{{ $terlambatHariIni }}</p>
    </div>
    <div class="text-center">
        <p class="text-[10px] uppercase tracking-wide text-slate-400 font-medium">Cuti</p>
        <p class="text-xl font-bold text-slate-900">{{ $cutiHariIni }}</p>
    </div>
    <div class="text-center">
        <p class="text-[10px] uppercase tracking-wide text-slate-400 font-medium">Alpha</p>
        <p class="text-xl font-bold text-slate-900">{{ $alphaHariIni }}</p>
    </div>
</div>

        <div class="px-5 py-8 text-center text-sm text-slate-400">
            Belum ada data absensi hari ini
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full uppercase tracking-wide">Pending</span>
                <h3 class="font-semibold text-slate-900">Pengajuan Cuti</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-50">
                        <th class="px-5 py-3 font-medium">Pegawai</th>
                        <th class="px-3 py-3 font-medium">Jenis</th>
                        <th class="px-3 py-3 font-medium">Tanggal</th>
                        <th class="px-3 py-3 font-medium">Status</th>
                    </tr>
                </thead>
<tbody>
    @forelse($pendingLeaves as $leave)
        <tr class="border-b border-slate-50 hover:bg-slate-50">
            <td class="px-5 py-3 font-medium text-slate-800">
                {{ $leave->employee->full_name ?? '-' }}
            </td>
            <td class="px-3 py-3 text-slate-600">
                {{ str_replace('_', ' ', $leave->leave_type) }}
            </td>
            <td class="px-3 py-3 text-slate-600">
                {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}
            </td>
            <td class="px-3 py-3">
                <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                    pending
                </span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-5 py-10 text-center text-slate-400">
                Tidak ada pengajuan cuti pending
            </td>
        </tr>
    @endforelse
</tbody>
            </table>
        </div>
    </div>
</div>
@endsection