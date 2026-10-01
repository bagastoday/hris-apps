    @extends('layouts.admin')

    @section('title', 'Pegawai')
    @section('page-title', 'Data Pegawai')
    @section('page-subtitle', 'Manajemen data pegawai')

    @section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Daftar Pegawai</h2>
            <p class="text-sm text-slate-500">Menampilkan {{ $employees->count() }} pegawai</p>
        </div>
        <a href="{{ route('employees.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/20 hover:shadow-lg hover:-translate-y-0.5 transition duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pegawai
        </a>
    </div>

    <form method="GET" action="{{ route('employees.index') }}" class="mb-5 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm sm:p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_220px_auto_auto]">
            <label class="relative">
                <span class="sr-only">Cari pegawai</span>
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" stroke-width="1.8"></circle>
                    <path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"></path>
                </svg>
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama, NIK, email, atau kode..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/10">
            </label>
            <label>
                <span class="sr-only">Filter departemen</span>
                <select name="department_id" class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-700 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/10">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-brand-600/20">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-width="1.8"></circle><path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"></path></svg>
                Cari
            </button>
            @if($search !== '' || $departmentId !== '')
                <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="px-5 py-3.5 font-medium">Pegawai</th>
                        <th class="px-4 py-3.5 font-medium">Jabatan</th>
                        <th class="px-4 py-3.5 font-medium">Status</th>
                        <th class="px-4 py-3.5 font-medium">Bergabung</th>
                        <th class="px-4 py-3.5 font-medium text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($emp->user?->avatar)
                                        <img src="{{ asset('storage/' . $emp->user->avatar) }}" alt="Foto profil {{ $emp->full_name }}" class="h-10 w-10 rounded-full border border-slate-200 object-cover">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">
                                            {{ strtoupper(substr($emp->full_name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $emp->full_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $emp->employee_code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-medium text-slate-800">{{ $emp->position->name ?? '-' }}</p>
                                <p class="text-xs text-slate-400">{{ $emp->department->name ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @if($emp->employment_status === 'aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-full">
                                        {{ ucfirst($emp->employment_status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-500">
                                {{ $emp->join_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('employees.edit', $emp) }}" class="group inline-flex items-center gap-1.5 rounded-lg border border-brand-100 bg-brand-50 px-2.5 py-1.5 text-xs font-semibold text-brand-700 transition hover:border-brand-200 hover:bg-brand-100 hover:shadow-sm">
                                        <svg class="h-3.5 w-3.5 transition-transform group-hover:-rotate-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.862 4.487 2.651 2.651M8 16l8.5-8.5a2.121 2.121 0 0 1 3 3L11 19H8v-3Z"/></svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('employees.destroy', $emp) }}" method="POST" onsubmit="return confirm('Apakah kamu yakin ingin menghapus data pegawai {{ $emp->full_name }}? Data absensi dan cuti terkait juga akan terhapus.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="group inline-flex items-center gap-1.5 rounded-lg border border-rose-100 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:border-rose-200 hover:bg-rose-100 hover:shadow-sm">
                                            <svg class="h-3.5 w-3.5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M10 11v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                {{ $search !== '' || $departmentId !== '' ? 'Tidak ada pegawai yang cocok dengan filter.' : 'Belum ada data pegawai.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endsection