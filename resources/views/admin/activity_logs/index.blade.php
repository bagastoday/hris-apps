<!-- resources/views/admin/activity_logs/index.blade.php -->
@extends('layouts.admin')

@section('title', 'Audit Log Aktivitas')
@section('page-title', 'Audit Log & Riwayat Aktivitas')
@section('page-subtitle', 'Rekam jejak seluruh transaksi, perubahan data sensitif, dan aksi keamanan sistem')

@section('content')
<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Log Hari Ini</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ $stats['total_today'] }} <span class="text-xs font-semibold text-slate-400">Aksi</span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Aksi Cuti & Izin</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ $stats['total_leave_actions'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.12-3 2.5S10.343 13 12 13s3 1.12 3 2.5S13.657 18 12 18m0-10V6m0 2v10m0 0v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Aksi Gaji & Payroll</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600 mt-0.5">{{ $stats['total_finance_actions'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Aksi Data Pegawai</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">{{ $stats['total_employee_actions'] }}</p>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Cari Keterangan / User</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik kata kunci..."
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe Aksi</label>
                <select name="action" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>{{ str_replace('_', ' ', strtoupper($act)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Peran Pengguna</label>
                <select name="role" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <option value="">Semua Peran</option>
                    <option value="hr" {{ $role === 'hr' ? 'selected' : '' }}>HR Admin</option>
                    <option value="finance" {{ $role === 'finance' ? 'selected' : '' }}>Finance</option>
                    <option value="karyawan" {{ $role === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                    Filter
                </button>
                @if($search || $action || $role || $date)
                    <a href="{{ route('activity-logs.index') }}" class="py-2 px-3 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Activity Log Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Riwayat Log Sistem</h3>
                <p class="text-xs text-slate-400">Total {{ $logs->total() }} catatan aktivitas terekam</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">Realtime WIB</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="px-5 py-3.5 font-medium">Waktu Transaksi</th>
                        <th class="px-4 py-3.5 font-medium">Pengguna</th>
                        <th class="px-4 py-3.5 font-medium">Tipe Aksi</th>
                        <th class="px-4 py-3.5 font-medium">Rincian Aktivitas</th>
                        <th class="px-4 py-3.5 font-medium text-right">IP & Klien</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                        @php
                            $badgeColor = match($log->action) {
                                'approve_leave' => 'emerald',
                                'reject_leave' => 'rose',
                                'update_salary', 'finalize_payroll', 'paid_payroll' => 'purple',
                                'create_announcement', 'pin_announcement' => 'blue',
                                'delete_announcement', 'delete_employee' => 'rose',
                                default => 'slate'
                            };

                            $roleBadge = match($log->user_role) {
                                'hr' => 'bg-brand-50 text-brand-700',
                                'finance' => 'bg-purple-50 text-purple-700',
                                'karyawan' => 'bg-emerald-50 text-emerald-700',
                                default => 'bg-slate-100 text-slate-600'
                            };
                            $roleLabel = $log->user_title ?: match($log->user_role) {
                                'hr' => 'HR Admin',
                                'finance' => 'Finance Admin',
                                'karyawan' => 'Karyawan',
                                'system' => 'Sistem',
                                default => $log->user_role ?? 'Tidak diketahui'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-mono text-xs font-semibold text-slate-800">{{ $log->created_at->format('d M Y') }}</p>
                                <p class="text-[11px] font-mono text-slate-400">{{ $log->created_at->format('H:i:s') }} WIB &bull; {{ $log->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-xs text-slate-900">{{ $log->user_name }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $roleBadge }}">
                                        {{ $roleLabel }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $badgeColor }}-50 text-{{ $badgeColor }}-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $badgeColor }}-500"></span>
                                    {{ str_replace('_', ' ', strtoupper($log->action)) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-700 font-medium">
                                {{ $log->description }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right">
                                <p class="font-mono text-xs font-semibold text-slate-600">{{ $log->ip_address ?? '127.0.0.1' }}</p>
                                <p class="text-[10px] text-slate-400 max-w-[150px] truncate ml-auto" title="{{ $log->user_agent }}">{{ $log->user_agent ?? 'Web Browser' }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                Belum ada riwayat aktivitas yang tercatat sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
