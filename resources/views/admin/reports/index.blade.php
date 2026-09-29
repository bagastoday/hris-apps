{{-- resources/views/admin/reports/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Laporan')
@section('page-title', 'Laporan')
@section('page-subtitle', 'Ringkasan kehadiran dan cuti pegawai')

@section('content')
@php
    // Warna tiap potongan donut
    $palette = ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#84cc16'];

    // Hitung geometri donut (lingkaran SVG r=80)
    $radius = 80;
    $circumference = 2 * pi() * $radius;
    $sum = max($perDepartemen->sum('percent'), 1);
    $offset = 0;
    $segments = [];

    foreach ($perDepartemen as $i => $d) {
        $len = $d['percent'] / $sum * $circumference;
        $segments[] = [
            'name'    => $d['name'],
            'percent' => $d['percent'],
            'color'   => $palette[$i % count($palette)],
            'len'     => $len,
            'offset'  => $offset,
        ];
        $offset += $len;
    }
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-900">Laporan Bulanan</h2>
        <p class="text-sm text-slate-500">Periode {{ \Carbon\Carbon::createFromFormat('!Y-m', $period)->translatedFormat('F Y') }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" action="{{ route('reports.index') }}">
            <input type="month" name="period" value="{{ $period }}" onchange="this.form.submit()"
                   class="rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
            <select name="department_id" onchange="this.form.submit()"
                    class="rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">Semua Departemen</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('reports.export.excel', ['period' => $period, 'department_id' => $departmentId]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4m-5 8h18"/></svg>
            Export Excel
        </a>
        <a href="{{ route('reports.export.pdf', ['period' => $period, 'department_id' => $departmentId]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4m-5 8h18"/></svg>
            Export PDF
        </a>
    </div>
</div>

{{-- Kartu (kiri, ditumpuk) + chart donut (kanan) --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- 4 kartu ringkasan, atas ke bawah --}}
    <div class="flex flex-col gap-4 lg:col-span-1">
        <div class="flex-1 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Pegawai Aktif</p>
                <p class="text-3xl font-bold mt-2 text-slate-900">{{ $stats['total_pegawai'] }}</p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </span>
        </div>

        <div class="flex-1 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Rata-rata Kehadiran</p>
                <p class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['rata_kehadiran'] }}%</p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </span>
        </div>

        <div class="flex-1 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Total Terlambat</p>
                <p class="text-3xl font-bold mt-2 text-amber-600">{{ $stats['total_terlambat'] }}</p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
        </div>

        <div class="flex-1 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Cuti Disetujui</p>
                <p class="text-3xl font-bold mt-2 text-sky-600">{{ $stats['cuti_disetujui'] }}</p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </span>
        </div>
    </div>

    {{-- Chart donut kehadiran per departemen --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-semibold text-slate-900">Kehadiran per Departemen</h3>
        <p class="text-xs text-slate-400 mb-4">Arahkan kursor ke chart atau daftar untuk melihat detail</p>

        @if(count($segments))
            <div class="flex flex-col sm:flex-row items-center gap-8">
                {{-- Donut --}}
                <div class="relative w-56 h-56 shrink-0">
                    <svg viewBox="0 0 200 200" class="w-full h-full">
                        <g transform="rotate(-90 100 100)">
                            <circle cx="100" cy="100" r="{{ $radius }}" fill="none" stroke="#f1f5f9" stroke-width="28"/>
                            @foreach($segments as $i => $s)
                                <circle class="donut-seg" data-index="{{ $i }}"
                                        cx="100" cy="100" r="{{ $radius }}" fill="none"
                                        stroke="{{ $s['color'] }}" stroke-width="28"
                                        stroke-dasharray="{{ max($s['len'] - 2, 0) }} {{ $circumference }}"
                                        stroke-dashoffset="{{ -$s['offset'] }}"
                                        style="cursor:pointer; transition: stroke-width .2s, opacity .2s;"/>
                            @endforeach
                        </g>
                    </svg>

                    {{-- Teks di tengah donut --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center px-10">
                        <p id="donut-name" class="text-xs text-slate-400 leading-tight">Rata-rata</p>
                        <p id="donut-value" class="text-2xl font-bold text-slate-900">{{ $stats['rata_kehadiran'] }}%</p>
                    </div>
                </div>

                {{-- Legenda --}}
                <div class="w-full space-y-1">
                    @foreach($segments as $i => $s)
                        <div class="donut-row flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer transition"
                             data-index="{{ $i }}">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full" style="background: {{ $s['color'] }}"></span>
                                <span class="text-sm text-slate-700">{{ $s['name'] }}</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-900">{{ $s['percent'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <p class="text-sm text-slate-400 py-10 text-center">Belum ada data pegawai aktif</p>
        @endif
    </div>
</div>

{{-- Rekap per pegawai --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-slate-900">Rekap per Pegawai</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                    <th class="px-5 py-3.5 font-medium">Pegawai</th>
                    <th class="px-4 py-3.5 font-medium">Departemen</th>
                    <th class="px-4 py-3.5 font-medium text-center">Hadir</th>
                    <th class="px-4 py-3.5 font-medium text-center">Terlambat</th>
                    <th class="px-4 py-3.5 font-medium text-center">Izin</th>
                    <th class="px-4 py-3.5 font-medium text-center">Sakit</th>
                    <th class="px-4 py-3.5 font-medium text-center">Cuti</th>
                    <th class="px-4 py-3.5 font-medium text-center">Alpha</th>
                    <th class="px-4 py-3.5 font-medium text-center">Kehadiran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($rekap as $row)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">{{ $row['name'] }}</p>
                            <p class="text-xs text-slate-400">{{ $row['code'] }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ $row['dept'] }}</td>
                        <td class="px-4 py-4 text-center text-slate-600">{{ $row['hadir'] }}</td>
                        <td class="px-4 py-4 text-center text-amber-600">{{ $row['terlambat'] }}</td>
                        <td class="px-4 py-4 text-center text-sky-600">{{ $row['izin'] }}</td>
                        <td class="px-4 py-4 text-center text-sky-600">{{ $row['sakit'] }}</td>
                        <td class="px-4 py-4 text-center text-sky-600">{{ $row['cuti'] }}</td>
                        <td class="px-4 py-4 text-center text-red-600">{{ $row['alpha'] }}</td>
                        <td class="px-4 py-4 text-center font-semibold text-slate-900">{{ $row['persen'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                            Belum ada data pegawai aktif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Interaksi donut --}}
<script>
    (function () {
        const data = @json($segments);
        const segs = document.querySelectorAll('.donut-seg');
        const rows = document.querySelectorAll('.donut-row');
        const nameEl = document.getElementById('donut-name');
        const valueEl = document.getElementById('donut-value');
        const defaultName = 'Rata-rata';
        const defaultValue = '{{ $stats['rata_kehadiran'] }}%';

        if (!segs.length) return;

        function activate(index) {
            segs.forEach(function (s, i) {
                s.style.strokeWidth = i === index ? '36' : '28';
                s.style.opacity = i === index ? '1' : '0.3';
            });
            rows.forEach(function (r, i) {
                r.style.background = i === index ? '#f8fafc' : 'transparent';
                r.style.opacity = i === index ? '1' : '0.55';
            });
            nameEl.textContent = data[index].name;
            valueEl.textContent = data[index].percent + '%';
        }

        function reset() {
            segs.forEach(function (s) {
                s.style.strokeWidth = '28';
                s.style.opacity = '1';
            });
            rows.forEach(function (r) {
                r.style.background = 'transparent';
                r.style.opacity = '1';
            });
            nameEl.textContent = defaultName;
            valueEl.textContent = defaultValue;
        }

        [...segs, ...rows].forEach(function (el) {
            const index = parseInt(el.dataset.index, 10);
            el.addEventListener('mouseenter', function () { activate(index); });
            el.addEventListener('mouseleave', reset);
            el.addEventListener('click', function () { activate(index); }); // untuk layar sentuh
        });
    })();
</script>
@endsection