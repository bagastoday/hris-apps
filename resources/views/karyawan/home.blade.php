<!-- resources/views/karyawan/home.blade.php -->
@extends('layouts.karyawan')

@section('title', 'Portal Presensi')

@section('content')
<div x-data="{
    cameraModal: false,
    previewPhotoModal: false,
    previewPhotoUrl: '',
    previewPhotoTitle: '',
    actionType: 'in',
    photoData: '',
    stream: null,
    facingMode: 'user',
    photoTaken: false,
    openPreview(url, title) {
        this.previewPhotoUrl = url;
        this.previewPhotoTitle = title;
        this.previewPhotoModal = true;
    }
}" class="space-y-6">

    {{-- Top Hero: Identity & Realtime Clock --}}
    <div class="gradient-hero rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-medium backdrop-blur-md mb-3 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    PRESENSI REALTIME PEGAWAI
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, {{ $employee->full_name ?? auth()->user()?->name ?? 'Pegawai' }}! 👋
                </h1>
                <p class="text-blue-100/80 text-sm mt-1">
                    {{ $employee->employee_code ?? 'EMP' }} &bull; {{ $employee->position->name ?? 'Pegawai' }} &bull; {{ $employee->department->name ?? 'Departemen Umum' }}
                </p>
            </div>

            {{-- Realtime Digital Clock Widget --}}
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl px-6 py-4 text-center md:text-right shrink-0 shadow-lg">
                <p id="live-date" class="text-xs font-medium text-blue-200 uppercase tracking-wider mb-1">
                    {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                </p>
                <div class="font-mono text-3xl sm:text-4xl font-extrabold text-white tracking-widest drop-shadow-sm flex items-center justify-center md:justify-end gap-1">
                    <span id="live-clock">--:--:--</span>
                    <span class="text-xs font-sans text-blue-200 font-semibold ml-1">WIB</span>
                </div>
                <p class="text-[11px] text-blue-200/80 mt-1">Batas Masuk: 08:30 WIB &bull; Pulang: 17:00 WIB</p>
            </div>
        </div>

        {{-- Decorative circles --}}
        <div class="absolute -right-10 -bottom-10 w-44 h-44 rounded-full bg-white/5 pointer-events-none"></div>
        <div class="absolute left-1/3 -top-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-xl"></div>
    </div>

    {{-- Pinned Announcement Banner (if any) --}}
    @if(isset($pinnedAnnouncements) && $pinnedAnnouncements->isNotEmpty())
        <div class="space-y-3">
            @foreach($pinnedAnnouncements as $pinned)
                <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-200/80 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 shadow-sm shadow-amber-500/30">
                            📢
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                                    {{ $pinned->category }}
                                </span>
                                <span class="text-[11px] text-slate-400 font-mono">
                                    {{ $pinned->published_at ? $pinned->published_at->format('d M Y') : $pinned->created_at->format('d M Y') }}
                                </span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-900 leading-snug">
                                {{ $pinned->title }}
                            </h4>
                            <p class="text-xs text-slate-600 line-clamp-1 mt-0.5">
                                {{ $pinned->content }}
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 pl-13 sm:pl-0">
                        <a href="{{ route('karyawan.announcements') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                            Lihat Pengumuman
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Monthly Attendance Statistics Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Hadir Bulan Ini</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ $monthlyStats['hadir'] }} <span class="text-xs font-semibold text-slate-400">Hari</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Terlambat</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600 mt-0.5">{{ $monthlyStats['terlambat'] }} <span class="text-xs font-semibold text-amber-500">Kali</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Izin & Cuti</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ $monthlyStats['izin_cuti'] }} <span class="text-xs font-semibold text-slate-400">Hari</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Presensi</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600 mt-0.5">{{ $monthlyStats['total_kehadiran'] }} <span class="text-xs font-semibold text-emerald-500">Hari</span></p>
            </div>
        </div>
    </div>

    {{-- Today's Status & Action Buttons --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Card Absen Masuk --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between relative overflow-hidden">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </div>
                    @if($todayAttendance && $todayAttendance->check_in)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $todayAttendance->status === 'terlambat' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $todayAttendance->status === 'terlambat' ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                            {{ ucfirst($todayAttendance->status) }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
                            Belum Absen
                        </span>
                    @endif
                </div>

                <h3 class="text-base font-bold text-slate-900">Presensi Masuk</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    @if($isScheduledWorkday)
                        Jadwal masuk {{ $expectedStartTime }} WIB
                    @else
                        Hari libur. Presensi akhir pekan dicatat sebagai lembur.
                    @endif
                </p>

                @if($todayAttendance && $todayAttendance->check_in)
                    <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-3">
                        @if($todayAttendance->check_in_photo)
                            <img src="{{ asset('storage/' . $todayAttendance->check_in_photo) }}" alt="Foto Masuk"
                                 @click="openPreview('{{ asset('storage/' . $todayAttendance->check_in_photo) }}', 'Foto Absen Masuk')"
                                 class="w-14 h-14 rounded-lg object-cover border border-slate-200 cursor-pointer hover:opacity-90 transition">
                        @else
                            <div class="w-14 h-14 rounded-lg bg-slate-200 flex items-center justify-center text-xs text-slate-400 font-medium">No Foto</div>
                        @endif
                        <div>
                            <p class="text-[11px] text-slate-400 font-medium">Jam Masuk Tercatat:</p>
                            <p class="text-lg font-bold text-slate-800 font-mono">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i:s') }} WIB</p>
                        </div>
                    </div>
                @else
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center">
                        <p class="text-xs text-slate-400">Siapkan kamera wajah kamu untuk melakukan absensi masuk hari ini.</p>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                @if(!$todayAttendance || !$todayAttendance->check_in)
                    <button type="button" @click="openCamera('in')"
                            class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-brand-600/30 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Absen Masuk Sekarang
                    </button>
                @else
                    <div class="py-2.5 px-4 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-semibold text-center flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Sudah Absen Masuk
                    </div>
                @endif
            </div>
        </div>

        {{-- Card Absen Pulang --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between relative overflow-hidden">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </div>
                    @if($todayAttendance && $todayAttendance->check_out)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Selesai
                        </span>
                    @elseif($todayAttendance && $todayAttendance->check_in)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                            Sedang Bekerja
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-400">
                            Menunggu Masuk
                        </span>
                    @endif
                </div>

                <h3 class="text-base font-bold text-slate-900">Presensi Pulang</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    @if($isScheduledWorkday)
                        Jadwal pulang {{ $expectedEndTime }} WIB
                    @else
                        Presensi masuk dan pulang lengkap dihitung sebagai lembur.
                    @endif
                </p>

                @if($todayAttendance && $todayAttendance->check_out)
                    <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-3">
                        @if($todayAttendance->check_out_photo)
                            <img src="{{ asset('storage/' . $todayAttendance->check_out_photo) }}" alt="Foto Pulang"
                                 @click="openPreview('{{ asset('storage/' . $todayAttendance->check_out_photo) }}', 'Foto Absen Pulang')"
                                 class="w-14 h-14 rounded-lg object-cover border border-slate-200 cursor-pointer hover:opacity-90 transition">
                        @else
                            <div class="w-14 h-14 rounded-lg bg-slate-200 flex items-center justify-center text-xs text-slate-400 font-medium">No Foto</div>
                        @endif
                        <div>
                            <p class="text-[11px] text-slate-400 font-medium">Jam Pulang Tercatat:</p>
                            <p class="text-lg font-bold text-slate-800 font-mono">{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i:s') }} WIB</p>
                        </div>
                    </div>
                @else
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center">
                        <p class="text-xs text-slate-400">
                            {{ ($todayAttendance && $todayAttendance->check_in) ? 'Bisa dilakukan menjelang atau setelah jam selesai kerja.' : 'Lakukan absen masuk terlebih dahulu.' }}
                        </p>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                @if($todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out)
                    <button type="button" @click="openCamera('out')"
                            class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-purple-600/30 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Absen Pulang Sekarang
                    </button>
                @elseif($todayAttendance && $todayAttendance->check_out)
                    <div class="py-2.5 px-4 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-semibold text-center flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Sudah Selesai Hari Ini
                    </div>
                @else
                    <button type="button" disabled
                            class="w-full py-3 px-4 bg-slate-100 text-slate-400 font-semibold text-sm rounded-xl cursor-not-allowed">
                        Belum Bisa Absen Pulang
                    </button>
                @endif
            </div>
        </div>

        {{-- Ringkasan Status & Info Pegawai --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 mb-4">Informasi Kehadiran Hari Ini</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center justify-between pb-2 border-b border-slate-50">
                        <span class="text-slate-500">Tanggal</span>
                        <span class="font-semibold text-slate-800">{{ now()->format('d M Y') }}</span>
                    </li>
                    <li class="flex items-center justify-between pb-2 border-b border-slate-50">
                        <span class="text-slate-500">Status Kehadiran</span>
                        <span class="font-semibold text-slate-800">
                            @if($todayAttendance)
                                <span class="capitalize text-brand-600 font-bold">{{ $todayAttendance->status }}</span>
                            @else
                                <span class="text-slate-400">Belum hadir</span>
                            @endif
                        </span>
                    </li>
                    <li class="flex items-center justify-between pb-2 border-b border-slate-50">
                        <span class="text-slate-500">Catatan Presensi</span>
                        <span class="text-slate-700 text-xs font-medium text-right max-w-[150px] truncate">
                            {{ $todayAttendance->notes ?? 'Tidak ada' }}
                        </span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-slate-500">Verifikasi Kamera</span>
                        <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">Selfie Wajib</span>
                    </li>
                </ul>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-400 leading-relaxed">
                    Data absensi langsung tersinkronisasi realtime ke Dashboard HR. Pastikan posisi wajah terlihat jelas di area pencahayaan yang memadai.
                </p>
            </div>
        </div>
    </div>

    {{-- Riwayat Absensi Terakhir --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Riwayat Presensi Terbaru</h3>
                <p class="text-xs text-slate-400">Daftar kehadiran harian kamu</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="px-5 py-3.5 font-medium">Tanggal</th>
                        <th class="px-4 py-3.5 font-medium">Jam Masuk</th>
                        <th class="px-4 py-3.5 font-medium">Jam Pulang</th>
                        <th class="px-4 py-3.5 font-medium">Durasi Kerja</th>
                        <th class="px-4 py-3.5 font-medium">Status</th>
                        <th class="px-4 py-3.5 font-medium">Foto Selfie</th>
                        <th class="px-4 py-3.5 font-medium">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentAttendances as $att)
                        @php
                            $workDuration = '-';
                            if ($att->check_in && $att->check_out) {
                                $in = \Carbon\Carbon::parse($att->check_in);
                                $out = \Carbon\Carbon::parse($att->check_out);
                                $diff = $in->diff($out);
                                $workDuration = sprintf('%dj %dm', $diff->h, $diff->i);
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-4 font-semibold text-slate-900">
                                {{ $att->date?->format('d M Y') }}
                            </td>
                            <td class="px-4 py-4 font-mono text-slate-700">
                                {{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '-' }}
                            </td>
                            <td class="px-4 py-4 font-mono text-slate-700">
                                {{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '-' }}
                            </td>
                            <td class="px-4 py-4 font-mono text-xs text-slate-600">
                                {{ $workDuration }}
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $badgeColor = match($att->status) {
                                        'hadir' => 'emerald',
                                        'terlambat' => 'amber',
                                        'izin', 'cuti' => 'sky',
                                        'sakit' => 'purple',
                                        default => 'red'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $badgeColor }}-50 text-{{ $badgeColor }}-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $badgeColor }}-500"></span>
                                    {{ ucfirst($att->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    @if($att->check_in_photo)
                                        <button type="button" @click="openPreview('{{ asset('storage/' . $att->check_in_photo) }}', 'Foto Presensi Masuk ({{ $att->date?->format('d M Y') }})')"
                                                class="group relative block" title="Lihat Foto Masuk">
                                            <img src="{{ asset('storage/' . $att->check_in_photo) }}" class="w-8 h-8 rounded-lg object-cover ring-1 ring-slate-200 group-hover:ring-brand-500 transition shadow-sm">
                                            <span class="absolute -top-1 -right-1 bg-brand-600 text-[8px] font-bold text-white px-1 rounded shadow">In</span>
                                        </button>
                                    @endif
                                    @if($att->check_out_photo)
                                        <button type="button" @click="openPreview('{{ asset('storage/' . $att->check_out_photo) }}', 'Foto Presensi Pulang ({{ $att->date?->format('d M Y') }})')"
                                                class="group relative block" title="Lihat Foto Pulang">
                                            <img src="{{ asset('storage/' . $att->check_out_photo) }}" class="w-8 h-8 rounded-lg object-cover ring-1 ring-slate-200 group-hover:ring-purple-500 transition shadow-sm">
                                            <span class="absolute -top-1 -right-1 bg-purple-600 text-[8px] font-bold text-white px-1 rounded shadow">Out</span>
                                        </button>
                                    @endif
                                    @if(!$att->check_in_photo && !$att->check_out_photo)
                                        <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500 max-w-xs truncate">
                                {{ $att->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                                Belum ada data riwayat presensi yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Interactive Camera Modal --}}
    <div x-show="cameraModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="closeCamera()"
             class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col max-h-[92vh]">

            {{-- Header modal --}}
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base" x-text="actionType === 'in' ? '📸 Ambil Foto Absen Masuk' : '📸 Ambil Foto Absen Pulang'"></h3>
                    <p class="text-xs text-slate-400">Posisikan wajahmu dengan jelas di dalam kamera</p>
                </div>
                <button type="button" @click="closeCamera()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body: Video Viewport & Canvas --}}
            <div class="p-5 flex-1 overflow-y-auto space-y-4">
                <div class="relative bg-slate-900 rounded-2xl overflow-hidden aspect-[4/3] flex items-center justify-center shadow-inner">
                    {{-- Live video --}}
                    <video id="webcam" autoplay playsinline class="w-full h-full object-cover"
                           :class="facingMode === 'user' ? 'transform -scale-x-100' : ''"
                           x-show="!photoTaken"></video>

                    {{-- Canvas (hidden, used for snapshot) --}}
                    <canvas id="photoCanvas" class="hidden"></canvas>

                    {{-- Image preview after snapshot --}}
                    <img id="photoPreview" :src="photoData" class="w-full h-full object-cover" x-show="photoTaken" alt="Hasil Foto">

                    {{-- Realtime stamp watermark on video/photo --}}
                    <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between px-3 py-1.5 rounded-lg bg-black/60 text-white backdrop-blur-sm text-[11px] font-mono pointer-events-none">
                        <span id="cam-user-name">{{ $employee->full_name ?? auth()->user()?->name ?? 'Pegawai' }}</span>
                        <span id="cam-clock">--:--:--</span>
                    </div>

                    {{-- Camera switch button --}}
                    <button type="button" @click="switchCamera()" x-show="!photoTaken"
                            class="absolute top-2.5 right-2.5 p-2 rounded-xl bg-black/50 text-white hover:bg-black/70 transition backdrop-blur-sm"
                            title="Balik Kamera">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>

                {{-- Action to take / retake photo --}}
                <div class="flex items-center justify-center gap-3">
                    <template x-if="!photoTaken">
                        <button type="button" @click="snapPhoto()"
                                class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-brand-600/20 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Ambil Foto Sekarang
                        </button>
                    </template>
                    <template x-if="photoTaken">
                        <button type="button" @click="retakePhoto()"
                                class="w-full py-2.5 px-4 border border-slate-200 text-slate-700 font-semibold text-sm rounded-xl hover:bg-slate-50 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Ambil Foto Ulang
                        </button>
                    </template>
                </div>

                {{-- Form submit --}}
                <form :action="actionType === 'in' ? '{{ route('karyawan.attendance.checkin') }}' : '{{ route('karyawan.attendance.checkout') }}'"
                      method="POST" enctype="multipart/form-data" class="space-y-3 pt-2 border-t border-slate-100">
                    @csrf
                    <input type="hidden" name="photo_base64" :value="photoData">

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan / Keterangan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: WFO di kantor / Meeting klien"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                    </div>

                    {{-- Fallback file upload --}}
                    <div class="text-center">
                        <label class="text-[11px] text-brand-600 hover:underline cursor-pointer inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Atau upload foto langsung dari galeri / kamera HP</span>
                            <input type="file" name="photo" accept="image/*" class="hidden" @change="handleFileUpload($event)">
                        </label>
                    </div>

                    <button type="submit" :disabled="!photoData"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="actionType === 'in' ? 'Kirim Absen Masuk' : 'Kirim Absen Pulang'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Interactive Photo Preview Modal --}}
    <div x-show="previewPhotoModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="previewPhotoModal = false"
             class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-800" x-text="previewPhotoTitle"></span>
                <button type="button" @click="previewPhotoModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 bg-slate-900 flex items-center justify-center">
                <img :src="previewPhotoUrl" class="w-full h-auto max-h-96 object-contain rounded-xl" alt="Preview Foto">
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Realtime Live Clock WIB
    function updateClock() {
        const now = new Date();
        const timeStr = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hourCycle: 'h23',
        }).format(now);
        const dateStr = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        }).format(now);

        const clockEl = document.getElementById('live-clock');
        if (clockEl) clockEl.textContent = timeStr;

        const dateEl = document.getElementById('live-date');
        if (dateEl) dateEl.textContent = dateStr;

        const camClock = document.getElementById('cam-clock');
        if (camClock) camClock.textContent = timeStr + ' WIB';
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Webcam & Modal Helpers for Alpine.js
    window.openCamera = function(type) {
        const root = document.querySelector('[x-data]');
        const alpine = Alpine.$data(root);
        alpine.actionType = type;
        alpine.photoTaken = false;
        alpine.photoData = '';
        alpine.cameraModal = true;

        setTimeout(() => {
            startStream(alpine.facingMode);
        }, 150);
    };

    window.closeCamera = function() {
        const root = document.querySelector('[x-data]');
        const alpine = Alpine.$data(root);
        alpine.cameraModal = false;
        stopStream();
    };

    window.startStream = async function(facingMode = 'user') {
        const video = document.getElementById('webcam');
        if (!video) return;

        stopStream();

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert("Akses kamera otomatis membutuhkan koneksi aman (HTTPS) atau localhost. Kamu dapat langsung memilih opsi 'Upload foto dari galeri/kamera HP' di bawah.");
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: facingMode, width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            const root = document.querySelector('[x-data]');
            const alpine = Alpine.$data(root);
            alpine.stream = stream;
            video.srcObject = stream;
        } catch (err) {
            console.warn("Webcam access error:", err);
            alert("Tidak dapat mengakses kamera secara langsung. Silakan berikan izin kamera pada browser atau gunakan tombol 'Upload foto dari galeri/kamera HP'.");
        }
    };

    window.stopStream = function() {
        const root = document.querySelector('[x-data]');
        if (!root) return;
        const alpine = Alpine.$data(root);
        if (alpine && alpine.stream) {
            alpine.stream.getTracks().forEach(track => track.stop());
            alpine.stream = null;
        }
    };

    window.switchCamera = function() {
        const root = document.querySelector('[x-data]');
        const alpine = Alpine.$data(root);
        alpine.facingMode = (alpine.facingMode === 'user') ? 'environment' : 'user';
        startStream(alpine.facingMode);
    };

    window.snapPhoto = function() {
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('photoCanvas');
        if (!video || !canvas) return;

        const w = video.videoWidth || 640;
        const h = video.videoHeight || 480;
        canvas.width = w;
        canvas.height = h;

        const ctx = canvas.getContext('2d');
        const root = document.querySelector('[x-data]');
        const alpine = Alpine.$data(root);

        // Hanya mirror jika menggunakan kamera depan
        if (alpine.facingMode === 'user') {
            ctx.save();
            ctx.scale(-1, 1);
            ctx.drawImage(video, -w, 0, w, h);
            ctx.restore();
        } else {
            ctx.drawImage(video, 0, 0, w, h);
        }

        // Watermark timestamp
        const now = new Date();
        const timeStamp = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + now.toLocaleTimeString('id-ID') + ' WIB';
        ctx.fillStyle = 'rgba(0, 0, 0, 0.55)';
        ctx.fillRect(10, h - 36, w - 20, 26);
        ctx.fillStyle = '#ffffff';
        ctx.font = '14px Inter, sans-serif';
        ctx.fillText(timeStamp, 20, h - 18);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

        alpine.photoData = dataUrl;
        alpine.photoTaken = true;
        stopStream();
    };

    window.retakePhoto = function() {
        const root = document.querySelector('[x-data]');
        const alpine = Alpine.$data(root);
        alpine.photoTaken = false;
        alpine.photoData = '';
        startStream(alpine.facingMode);
    };

    window.handleFileUpload = function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(evt) {
            const root = document.querySelector('[x-data]');
            const alpine = Alpine.$data(root);
            alpine.photoData = evt.target.result;
            alpine.photoTaken = true;
            stopStream();
        };
        reader.readAsDataURL(file);
    };
</script>
@endpush