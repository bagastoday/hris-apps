@extends('layouts.guest')

@section('title', 'TalentaCore - Automasi Manajemen SDM Enterprise')

@section('content')
<!-- Hero Section -->
<section class="relative bg-slate-50 overflow-hidden">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+CjxwYXRoIGQ9Ik0wIDBoNDB2NDBIMHoiIGZpbGw9Im5vbmUiLz4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMSIgZmlsbD0icmdiYSgwLCAwLCAwLCAwLjA1KSIvPgo8L3N2Zz4=')] opacity-50"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative pt-20 pb-16 lg:pt-32 lg:pb-24 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-navy-900 tracking-tight mb-6 max-w-4xl mx-auto leading-tight">
            Automasi Manajemen SDM, Penggajian, dan Absensi dalam <span class="text-corporate-blue">Satu Ekosistem</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-600 mb-10 max-w-2xl mx-auto">
            Tingkatkan efisiensi operasional perusahaan Anda dengan platform HRIS terintegrasi yang dirancang khusus untuk skala enterprise.
        </p>
        
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-16">
            <a href="#trial" class="px-8 py-3.5 text-base font-medium text-white bg-corporate-blue hover:bg-corporate-hover hover:-translate-y-1 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl w-full sm:w-auto">
                Mulai Uji Coba Gratis
            </a>
            <a href="#demo-video" class="px-8 py-3.5 text-base font-medium text-navy-900 bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 hover:-translate-y-1 rounded-lg transition-all duration-300 shadow-sm hover:shadow-md w-full sm:w-auto group">
                <span class="group-hover:text-corporate-blue transition-colors">Lihat Live Demo</span>
            </a>
        </div>

        <!-- Mockup UI HRIS Preview -->
        <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all duration-500 hover:scale-[1.02] hover:shadow-blue-500/10">
            <div class="bg-slate-100 border-b border-slate-200 px-4 py-3 flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
            </div>
            <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50/50">
                <!-- Stat Card 1 -->
                <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm text-left hover:-translate-y-1 hover:shadow-md transition-all duration-300 cursor-default">
                    <p class="text-sm font-medium text-slate-500 mb-1">Total Karyawan Aktif</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-bold text-navy-900">1,248</h3>
                        <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-md">+12 bulan ini</span>
                    </div>
                </div>
                <!-- Stat Card 2 -->
                <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm text-left hover:-translate-y-1 hover:shadow-md transition-all duration-300 cursor-default">
                    <p class="text-sm font-medium text-slate-500 mb-1">Metrik Presensi Hari Ini</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-bold text-navy-900">98.5%</h3>
                        <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2 py-1 rounded-md">Tepat waktu</span>
                    </div>
                </div>
                <!-- Stat Card 3 -->
                <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm text-left hover:-translate-y-1 hover:shadow-md transition-all duration-300 cursor-default">
                    <p class="text-sm font-medium text-slate-500 mb-1">Status Payroll Bulan Ini</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-xl font-bold text-navy-900">Terverifikasi</h3>
                        <span class="text-xs font-medium text-corporate-blue bg-blue-50 px-2 py-1 rounded-md flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Siap Transfer
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Proof Section -->
<section class="py-12 border-y border-slate-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="py-4">
                <h4 class="text-4xl font-extrabold text-corporate-blue mb-2">99.9%</h4>
                <p class="text-sm font-medium text-slate-500">SLA Uptime Sistem</p>
            </div>
            <div class="py-4">
                <h4 class="text-4xl font-extrabold text-corporate-blue mb-2">ISO 27001</h4>
                <p class="text-sm font-medium text-slate-500">Enkripsi & Keamanan Standar Global</p>
            </div>
            <div class="py-4">
                <h4 class="text-4xl font-extrabold text-corporate-blue mb-2">80%</h4>
                <p class="text-sm font-medium text-slate-500">Peningkatan Efisiensi Waktu Payroll</p>
            </div>
        </div>
    </div>
</section>

<!-- Modul Unggulan Section -->
<section id="modul" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl font-bold text-navy-900 mb-4">Modul Terintegrasi untuk Skala Enterprise</h2>
            <p class="text-slate-600">Berbagai fitur canggih yang saling terhubung untuk mengotomatisasi pekerjaan administratif HR yang repetitif.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Modul 1 -->
            <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer">
                <div class="w-12 h-12 bg-blue-50 text-corporate-blue rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-corporate-blue group-hover:text-white transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-navy-900 mb-3">Presensi & Geolocation</h3>
                <p class="text-slate-600 text-sm leading-relaxed mb-4">Pelacakan kehadiran real-time berbasis GPS, manajemen shift dinamis, dan perhitungan lembur otomatis yang akurat.</p>
            </div>

            <!-- Modul 2 -->
            <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer">
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-green-500 group-hover:text-white transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-navy-900 mb-3">Penggajian & Payroll Otomatis</h3>
                <p class="text-slate-600 text-sm leading-relaxed mb-4">Perhitungan gaji komprehensif termasuk potongan PPh 21, BPJS, tunjangan, dan distribusi slip gaji digital terenkripsi.</p>
            </div>

            <!-- Modul 3 -->
            <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group cursor-pointer">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-purple-500 group-hover:text-white transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-navy-900 mb-3">Cuti & Dokumen Karyawan</h3>
                <p class="text-slate-600 text-sm leading-relaxed mb-4">Alur persetujuan cuti bertingkat secara digital dan repositori aman untuk penyimpanan kontrak kerja atau dokumen penting HR.</p>
            </div>
        </div>
    </div>
</section>
@endsection
