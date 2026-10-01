@extends('layouts.admin')

@section('title', 'Atur Jadwal')
@section('page-title', 'Atur Jadwal')
@section('page-subtitle', 'Perubahan jadwal kerja pegawai')

@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Atur Jadwal</h2>
            <p class="mt-1 text-sm text-slate-500">Pilih pegawai dan tanggal untuk melihat atau mengubah jadwal.</p>
        </div>
        <a href="{{ route('roster.index', ['date' => $selectedDate->toDateString()]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Kembali ke Roster</a>
    </div>

    <form method="GET" action="{{ route('roster.schedule.edit') }}" class="grid gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <label class="text-sm font-medium text-slate-700">Pegawai
            <select name="employee_id" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">Pilih pegawai</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" @selected((string) $selectedEmployee?->id === (string) $employee->id)>{{ $employee->full_name }} ({{ $employee->employee_code }})</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-medium text-slate-700">Tanggal
            <input type="date" name="date" value="{{ $selectedDate->toDateString() }}" min="{{ $minimumDate->toDateString() }}" max="{{ $maximumDate->toDateString() }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </label>
        <button class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Tampilkan Jadwal</button>
    </form>

    @if($selectedEmployee)
        @if($selectedDate->isWeekend())
            <div class="rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">{{ $selectedDate->translatedFormat('l, d F Y') }} adalah hari libur. Jadwal hanya dapat diatur untuk hari kerja.</div>
        @elseif($leave)
            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Pegawai sedang cuti yang disetujui pada tanggal ini. Jadwal tidak dapat diubah.</div>
        @else
            <form method="POST" action="{{ route('roster.schedule.update', ['employee_id' => $selectedEmployee->id, 'date' => $selectedDate->toDateString()]) }}" class="space-y-5 rounded-lg border border-slate-200 bg-white p-5">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">
                <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">
                <div class="border-b border-slate-100 pb-4">
                    <p class="font-semibold text-slate-900">{{ $selectedEmployee->full_name }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $selectedDate->translatedFormat('l, d F Y') }}</p>
                    @if($schedule)
                        <p class="mt-2 text-xs text-slate-500">Jadwal saat ini {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</p>
                    @endif
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-medium text-slate-700">Jam masuk
                        <input type="time" name="start_time" value="{{ old('start_time', substr($schedule?->start_time ?? \App\Models\RosterSchedule::DEFAULT_START_TIME, 0, 5)) }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        @error('start_time')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700">Jam pulang
                        <input type="time" name="end_time" value="{{ old('end_time', substr($schedule?->end_time ?? \App\Models\RosterSchedule::DEFAULT_END_TIME, 0, 5)) }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        @error('end_time')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
                <label class="block text-sm font-medium text-slate-700">Alasan perubahan
                    <textarea name="reason" rows="3" maxlength="1000" required placeholder="Contoh: Penyesuaian jam kerja atas koordinasi dengan HR" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('reason', $schedule?->reason) }}</textarea>
                    @error('reason')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                @error('date')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <div class="flex justify-end">
                    <button class="rounded-md bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Simpan Jadwal</button>
                </div>
            </form>
        @endif
    @endif
</div>
@endsection