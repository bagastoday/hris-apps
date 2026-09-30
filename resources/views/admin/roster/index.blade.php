@extends('layouts.admin')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Roster</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $weekStart->format('d M Y') }} - {{ $weekEnd->format('d M Y') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @php
                $previousDate = $selectedDate->copy()->subWeek();
                $nextDate = $selectedDate->copy()->addWeek();
                if ($previousDate->lessThan($minimumDate)) $previousDate = $minimumDate->copy();
                if ($nextDate->greaterThan($maximumDate)) $nextDate = $maximumDate->copy();
            @endphp
            @if($selectedDate->greaterThan($minimumDate))
                <a href="{{ route('roster.index', ['date' => $previousDate->toDateString(), 'department_id' => $departmentId]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" aria-label="Minggu sebelumnya">‹</a>
            @endif
            <form method="GET" action="{{ route('roster.index') }}" class="flex items-center gap-2">
                <label for="roster-date" class="sr-only">Pilih tanggal roster</label>
                <input id="roster-date" type="date" name="date" value="{{ $selectedDate->toDateString() }}" min="{{ $minimumDate->toDateString() }}" max="{{ $maximumDate->toDateString() }}" class="rounded-md border-slate-300 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                <label for="department_id" class="sr-only">Filter departemen</label>
                <select id="department_id" name="department_id" class="rounded-md border-slate-300 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Semua departemen</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">Filter</button>
            </form>
            @if($selectedDate->lessThan($maximumDate))
                <a href="{{ route('roster.index', ['date' => $nextDate->toDateString(), 'department_id' => $departmentId]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" aria-label="Minggu berikutnya">›</a>
            @endif
            <a href="{{ route('roster.schedule.edit', ['date' => $selectedDate->toDateString()]) }}" class="rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">Atur Jadwal</a>
        </div>
    </div>

    <div class="overflow-x-auto border-y border-slate-200 bg-white">
        <table class="w-full min-w-[960px] text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold text-slate-600">
                <tr>
                    <th class="sticky left-0 z-10 min-w-56 bg-slate-50 px-4 py-3">Pegawai</th>
                    @foreach($days as $day)
                        <th class="min-w-28 px-3 py-3 {{ $day->isSunday() ? 'text-slate-400' : '' }}">
                            {{ ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][$day->dayOfWeekIso - 1] }}
                            <span class="mt-1 block font-normal">{{ $day->format('d M') }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($employees as $employee)
                    <tr>
                        <td class="sticky left-0 z-10 bg-white px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $employee->full_name }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $employee->department?->name ?? 'Tanpa Departemen' }}</p>
                        </td>
                        @foreach($days as $day)
                            @php
                                $dateKey = $day->toDateString();
                                $attendance = $attendances->get($employee->id.'|'.$dateKey);
                                $leave = $leaveByEmployeeAndDate[$employee->id][$dateKey] ?? null;
                                $schedule = $schedules->get($employee->id.'|'.$dateKey);
                                $isOvertime = $day->isWeekend() && $attendance?->check_in && $attendance?->check_out;
                                $startTime = substr($schedule?->start_time ?? \App\Models\RosterSchedule::DEFAULT_START_TIME, 0, 5);
                                $endTime = substr($schedule?->end_time ?? \App\Models\RosterSchedule::DEFAULT_END_TIME, 0, 5);
                            @endphp
                            <td class="px-3 py-3 {{ $day->isSunday() ? 'bg-slate-50/70' : '' }}">
                                @if($leave)
                                    <span class="font-medium text-amber-700">Cuti</span>
                                @elseif($isOvertime)
                                    <span class="font-medium text-sky-700">Lembur</span>
                                    <span class="mt-1 block text-xs text-slate-500">Rp200.000</span>
                                @elseif($day->isSunday())
                                    <span class="text-slate-400">Libur</span>
                                @elseif($day->isSaturday())
                                    <span class="text-slate-400">Libur</span>
                                @else
                                    <span class="whitespace-nowrap text-slate-700">{{ $startTime }} - {{ $endTime }}</span>
                                    @if($schedule)
                                        <span class="mt-1 block max-w-36 truncate text-xs text-slate-500" title="{{ $schedule->reason }}">{{ $schedule->reason }}</span>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada pegawai pada departemen ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection