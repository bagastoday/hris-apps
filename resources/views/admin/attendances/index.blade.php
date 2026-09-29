{{-- resources/views/admin/attendances/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Absensi')
@section('page-title', 'Absensi')
@section('page-subtitle', 'Rekap kehadiran harian pegawai')

@section('content')
@php
    $badge = [
        'hadir'     => 'text-emerald-700 bg-emerald-50',
        'terlambat' => 'text-amber-700 bg-amber-50',
        'izin'      => 'text-sky-700 bg-sky-50',
        'cuti'      => 'text-sky-700 bg-sky-50',
        'sakit'     => 'text-violet-700 bg-violet-50',
        'alpha'     => 'text-red-700 bg-red-50',
    ];
    $fmt = fn ($t) => $t ? \Carbon\Carbon::parse($t)->format('H:i') : '-';
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-900">Rekap Kehadiran</h2>
        <p class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y') }}</p>
    </div>

    <form method="GET" action="{{ route('attendances.index') }}" class="w-full sm:w-auto">
        <input type="hidden" name="search" value="{{ $search }}">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
            <input type="date" name="date" value="{{ $date }}"
                   onchange="this.form.submit()"
                   class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10">
            <select name="department_id"
                    onchange="this.form.submit()"
                    class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10">
                <option value="">Semua Departemen</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected($departmentId == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'Hadir',     'value' => $summary['hadir'],     'color' => 'text-emerald-600'],
        ['label' => 'Terlambat', 'value' => $summary['terlambat'], 'color' => 'text-amber-600'],
        ['label' => 'Izin/Cuti', 'value' => $summary['izin'],      'color' => 'text-sky-600'],
        ['label' => 'Alpha',     'value' => $summary['alpha'],     'color' => 'text-red-600'],
    ] as $card)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs uppercase tracking-wide text-slate-400">{{ $card['label'] }}</p>
            <p class="text-3xl font-bold mt-2 {{ $card['color'] }}">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('attendances.index') }}" class="mb-4 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm sm:p-4">
    <input type="hidden" name="date" value="{{ $date }}">
    <input type="hidden" name="department_id" value="{{ $departmentId }}">
    <div class="flex flex-col gap-2.5 sm:flex-row">
        <label class="relative flex-1">
            <span class="sr-only">Cari pegawai</span>
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7" stroke-width="1.8"></circle>
                <path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"></path>
            </svg>
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama, NIK, atau kode pegawai..."
                   class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/10">
        </label>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-brand-600/20">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-width="1.8"></circle><path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"></path></svg>
            Cari
        </button>
        @if($search !== '')
            <a href="{{ route('attendances.index', ['date' => $date, 'department_id' => $departmentId]) }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                Reset
            </a>
        @endif
    </div>
</form>

{{-- Tabel --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                    <th class="px-5 py-3.5 font-medium">Pegawai</th>
                    <th class="px-4 py-3.5 font-medium">Departemen</th>
                    <th class="px-4 py-3.5 font-medium">Jam Masuk</th>
                    <th class="px-4 py-3.5 font-medium">Jam Pulang</th>
                    <th class="px-4 py-3.5 font-medium">Status</th>
                    <th class="px-4 py-3.5 font-medium">Catatan</th>
                    <th class="px-4 py-3.5 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($attendances as $att)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($att->employee->full_name ?? '?', 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $att->employee->full_name ?? '-' }}</p>
                                    <p class="text-xs text-slate-400">{{ $att->employee->employee_code ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ $att->employee->department->name ?? '-' }}</td>
                        <td class="px-4 py-4 text-slate-600">{{ $fmt($att->check_in) }}</td>
                        <td class="px-4 py-4 text-slate-600">{{ $fmt($att->check_out) }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex text-xs font-medium px-2.5 py-1 rounded-full {{ $badge[$att->status] ?? 'text-slate-600 bg-slate-100' }}">
                                {{ ucfirst($att->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-slate-500">{{ $att->notes ?? '-' }}</td>
                        <td class="px-4 py-4 text-center">
                            @if($att->check_in_photo || $att->check_out_photo)
                                <button type="button" onclick="showPhotos(this)"
                                    data-name="{{ $att->employee->full_name ?? '-' }}"
                                    data-date="{{ $att->date->format('d M Y') }}"
                                    data-in="{{ $att->check_in_photo ? asset('storage/' . $att->check_in_photo) : '' }}"
                                    data-in-time="{{ $fmt($att->check_in) }}"
                                    data-out="{{ $att->check_out_photo ? asset('storage/' . $att->check_out_photo) : '' }}"
                                    data-out-time="{{ $fmt($att->check_out) }}"
                                    class="group inline-flex items-center gap-1.5 rounded-lg border border-sky-100 bg-sky-50 px-2.5 py-1.5 text-xs font-semibold text-sky-700 transition hover:border-sky-200 hover:bg-sky-100 hover:shadow-sm">
                                    <svg class="h-3.5 w-3.5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5" stroke-width="1.8"/></svg>
                                    Lihat Foto
                                </button>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            {{ $search !== '' ? 'Tidak ada data absensi yang cocok dengan pencarian ini.' : 'Belum ada data absensi pada tanggal ini.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal foto absen --}}
<div id="photo-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
     onclick="if (event.target === this) closePhotos()">
    <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-xl">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 id="photo-name" class="font-semibold text-slate-900"></h3>
                <p id="photo-date" class="text-xs text-slate-400"></p>
            </div>
            <button type="button" onclick="closePhotos()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach(['in' => 'Foto Masuk', 'out' => 'Foto Pulang'] as $key => $label)
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-2">
                        {{ $label }} <span id="photo-{{ $key }}-time" class="text-slate-400"></span>
                    </p>
                    <img id="photo-{{ $key }}-img" src="" alt="{{ $label }}" class="hidden w-full aspect-[3/4] object-cover rounded-xl border border-slate-100">
                    <div id="photo-{{ $key }}-empty" class="w-full aspect-[3/4] rounded-xl border border-dashed border-slate-200 flex items-center justify-center text-xs text-slate-400">
                        Tidak ada foto
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    function showPhotos(btn) {
        document.getElementById('photo-name').textContent = btn.dataset.name;
        document.getElementById('photo-date').textContent = btn.dataset.date;

        ['in', 'out'].forEach(function (key) {
            const src = btn.dataset[key];
            const img = document.getElementById('photo-' + key + '-img');
            const empty = document.getElementById('photo-' + key + '-empty');

            document.getElementById('photo-' + key + '-time').textContent = '· ' + btn.dataset[key + 'Time'];

            if (src) {
                img.src = src;
                img.classList.remove('hidden');
                empty.classList.add('hidden');
            } else {
                img.removeAttribute('src');
                img.classList.add('hidden');
                empty.classList.remove('hidden');
            }
        });

        document.getElementById('photo-modal').classList.remove('hidden');
    }

    function closePhotos() {
        document.getElementById('photo-modal').classList.add('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePhotos();
    });
</script>
@endsection