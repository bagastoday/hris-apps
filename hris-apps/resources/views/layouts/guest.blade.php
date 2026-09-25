<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TalentaCore - Enterprise HRIS Solution')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS (CDN Preview) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            800: '#1E293B',
                            900: '#0F172A',
                        },
                        corporate: {
                            blue: '#2563EB',
                            hover: '#1D4ED8',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <span class="text-2xl font-bold text-navy-900 tracking-tight">Talenta<span class="text-corporate-blue">Core</span></span>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#fitur" class="text-sm font-medium text-slate-600 hover:text-corporate-blue hover:-translate-y-0.5 transition-all duration-300">Fitur</a>
                    <a href="#solusi" class="text-sm font-medium text-slate-600 hover:text-corporate-blue hover:-translate-y-0.5 transition-all duration-300">Solusi</a>
                    <a href="#modul" class="text-sm font-medium text-slate-600 hover:text-corporate-blue hover:-translate-y-0.5 transition-all duration-300">Modul HR</a>
                    <a href="#harga" class="text-sm font-medium text-slate-600 hover:text-corporate-blue hover:-translate-y-0.5 transition-all duration-300">Harga</a>
                </div>

                <!-- CTA Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="/login" class="text-sm font-medium text-navy-900 hover:text-corporate-blue hover:-translate-y-0.5 transition-all duration-300">Masuk Portal Karyawan</a>
                    <a href="#demo" class="px-4 py-2 text-sm font-medium text-white bg-corporate-blue hover:bg-corporate-hover hover:-translate-y-0.5 rounded-lg transition-all duration-300 shadow-sm hover:shadow-md">Jadwalkan Demo</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-navy-900 text-slate-300 py-12 border-t border-navy-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-1 md:col-span-2">
                    <span class="text-2xl font-bold text-white tracking-tight block mb-4">Talenta<span class="text-blue-400">Core</span></span>
                    <p class="text-sm text-slate-400 max-w-sm">Solusi enterprise HRIS terpadu untuk menyederhanakan manajemen SDM, penggajian, dan operasional bisnis perusahaan Anda.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Navigasi</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-white transition">Karir</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition">Kontak</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal & Operasional</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-white transition">Syarat & Ketentuan</a></li>
                        <li><div class="flex items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>Sistem Operasional: Normal</div></li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 border-t border-navy-800 text-sm text-slate-500 flex justify-between items-center">
                <p>&copy; {{ date('Y') }} TalentaCore Enterprise. Hak Cipta Dilindungi.</p>
                <div class="flex space-x-4">
                    <span>ISO 27001 Certified</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
