@extends('layouts.guest')

@section('title', 'Data Pegawai - HRIS')

@section('content')
<div class="min-h-screen bg-slate-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-navy-900">Data Pegawai</h2>
                <p class="text-sm text-slate-500 mt-1">Manajemen data pegawai, posisi, dan status aktif.</p>
            </div>
            <div>
                <button class="bg-corporate-blue hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Tambah Pegawai
                </button>
            </div>
        </div>

        <!-- Alert Example -->
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg shadow-sm flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <p class="text-sm text-green-700 font-medium">Sukses! Data pegawai atas nama "Budi Santoso" berhasil diperbarui.</p>
            </div>
            <button class="text-green-500 hover:text-green-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <div class="flex space-x-3 w-full sm:w-auto">
                <select class="border-slate-200 text-sm rounded-lg focus:ring-corporate-blue focus:border-corporate-blue py-2">
                    <option>Semua Departemen</option>
                    <option>IT & Engineering</option>
                    <option>Human Resources</option>
                    <option>Finance</option>
                </select>
                <select class="border-slate-200 text-sm rounded-lg focus:ring-corporate-blue focus:border-corporate-blue py-2">
                    <option>Semua Status</option>
                    <option>Aktif</option>
                    <option>Cuti</option>
                </select>
            </div>
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" class="pl-10 w-full border-slate-200 text-sm rounded-lg focus:ring-corporate-blue focus:border-corporate-blue py-2" placeholder="Cari nama pegawai...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-medium border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-6 py-4">Nama Pegawai</th>
                            <th scope="col" class="px-6 py-4">Posisi & Departemen</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4">Tanggal Bergabung</th>
                            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold mr-3">
                                        BS
                                    </div>
                                    <div>
                                        <p class="font-medium text-navy-900">Budi Santoso</p>
                                        <p class="text-xs text-slate-500">EMP-001</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-navy-900 font-medium">Senior Developer</p>
                                <p class="text-xs text-slate-500">IT & Engineering</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> Aktif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                12 Jan 2023
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button class="p-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold mr-3">
                                        AR
                                    </div>
                                    <div>
                                        <p class="font-medium text-navy-900">Anita Rahman</p>
                                        <p class="text-xs text-slate-500">EMP-002</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-navy-900 font-medium">HR Manager</p>
                                <p class="text-xs text-slate-500">Human Resources</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5"></span> Cuti
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                05 Feb 2022
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button class="p-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <p class="text-sm text-slate-500">Menampilkan 1 hingga 2 dari 24 entri</p>
                <div class="flex space-x-1">
                    <button class="px-3 py-1 border border-slate-200 rounded-md text-sm text-slate-500 bg-white hover:bg-slate-50 disabled:opacity-50" disabled>Sebelumnya</button>
                    <button class="px-3 py-1 border border-corporate-blue bg-corporate-blue text-white rounded-md text-sm">1</button>
                    <button class="px-3 py-1 border border-slate-200 rounded-md text-sm text-slate-600 bg-white hover:bg-slate-50">2</button>
                    <button class="px-3 py-1 border border-slate-200 rounded-md text-sm text-slate-600 bg-white hover:bg-slate-50">3</button>
                    <button class="px-3 py-1 border border-slate-200 rounded-md text-sm text-slate-600 bg-white hover:bg-slate-50">Selanjutnya</button>
                </div>
            </div>
        </div>
        
        <div class="mt-8">
            <a href="{{ url('/') }}" class="text-corporate-blue hover:underline text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
