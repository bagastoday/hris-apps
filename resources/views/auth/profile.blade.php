<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profil - TalentaCore</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e3a8a',
                            900: '#1e3a5f',
                            950: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/buttons.css') }}">
    <style>
        .wave-banner { position: relative; overflow: hidden; }
        .wave-banner::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 28px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 500 60' preserveAspectRatio='none'%3E%3Cpath fill='%23ffffff' d='M0,30 C125,60 250,0 375,30 C440,45 470,38 500,30 L500,60 L0,60 Z'/%3E%3C/svg%3E") no-repeat bottom;
            background-size: cover;
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen flex flex-col">

    {{-- Mini topbar, senada dengan identitas brand di sidebar/login --}}
    <div class="px-4 sm:px-6 h-16 flex items-center justify-between bg-white border-b border-slate-200 shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center shadow-sm shadow-brand-600/30">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="font-bold text-sm text-slate-900 tracking-tight leading-none">Talenta<span class="text-brand-600">Core</span></p>
        </div>

        <a href="{{ auth()->user()->hasHrAdminAccess() ? route('dashboard') : route('karyawan.home') }}"
           class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-800 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <div class="flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">

            {{-- Banner biru dengan aksen gelombang, senada dengan layout admin & kartu login --}}
            <div class="wave-banner bg-gradient-to-br from-brand-600 to-brand-800 px-6 pt-6 pb-10 text-center text-white">
                <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-white/5"></div>
                <p class="relative z-10 text-[10px] font-semibold uppercase tracking-widest text-blue-100/80 mb-1">Pengaturan Akun</p>
                <h1 class="relative z-10 text-lg font-bold">Edit Profil</h1>
                <p class="relative z-10 text-sm text-blue-100/80">Perbarui nama & foto profil kamu</p>
            </div>

            <div class="px-6 pb-6">

                {{-- Avatar melayang di atas banner, ala kartu profil --}}
                <div class="flex flex-col items-center -mt-10 mb-5">
                    <div class="relative">
                        <div id="preview-wrap" class="w-20 h-20 rounded-full overflow-hidden bg-brand-600 ring-4 ring-white flex items-center justify-center text-white text-xl font-bold uppercase shadow-lg shadow-brand-900/20">
                            @if($user->avatar)
                                <img id="avatar-preview" src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover" alt="Avatar">
                            @else
                                <span id="avatar-initial">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                <img id="avatar-preview" src="" class="w-full h-full object-cover hidden" alt="Avatar">
                            @endif
                        </div>

                        <button id="avatar-upload-trigger" type="button" aria-label="Pilih foto profil" class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-brand-700 hover:bg-brand-800 ring-2 ring-white flex items-center justify-center cursor-pointer shadow-sm transition">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                        <input id="avatar-input" type="file" name="avatar" form="edit-profile-form" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewAvatar(this)">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2.5">JPG, PNG, WEBP &middot; maks 2 MB</p>
                </div>

                @if(session('success'))
                    <div class="mb-4 flex items-start gap-2 p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-700">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 flex items-start gap-2 p-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-600">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form id="edit-profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 transition">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Email</label>
                        <input type="email" value="{{ $user->email }}" disabled
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm bg-slate-50 text-slate-400">
                        <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Email tidak bisa diubah
                        </p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-brand-600/30 hover:shadow-md hover:shadow-brand-600/30">
                            Simpan Perubahan
                        </button>
                        <a href="{{ auth()->user()->hasHrAdminAccess() ? route('dashboard') : route('karyawan.home') }}"
                           class="px-5 py-3 border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="avatar-guidance-modal" role="dialog" aria-modal="true" aria-labelledby="avatar-guidance-title" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
        <section class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="1.8"/></svg>
                    </span>
                    <div>
                        <h2 id="avatar-guidance-title" class="text-sm font-bold text-slate-900">Panduan Foto Profil</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Pilih foto yang jelas dan profesional.</p>
                    </div>
                </div>
                <button type="button" data-close-avatar-guidance aria-label="Tutup panduan" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
            <div class="px-5 py-4">
                <ul class="space-y-3 text-sm text-slate-700">
                    <li class="flex gap-2.5"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-600"></span>Gunakan latar belakang putih atau polos dengan pencahayaan yang cukup.</li>
                    <li class="flex gap-2.5"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-600"></span>Hadap kamera dengan wajah terlihat jelas dan berada di tengah foto.</li>
                    <li class="flex gap-2.5"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-600"></span>Gunakan pakaian formal atau rapi; hindari kacamata hitam, filter, dan objek yang menutupi wajah.</li>
                    <li class="flex gap-2.5"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-600"></span>Format JPG, PNG, atau WEBP, ukuran maksimal 2 MB.</li>
                </ul>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-4">
                <button type="button" data-close-avatar-guidance class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Nanti</button>
                <button id="choose-avatar-button" type="button" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Pilih Foto</button>
            </div>
        </section>
    </div>

<script>
const avatarInput = document.getElementById('avatar-input');
const avatarGuidanceModal = document.getElementById('avatar-guidance-modal');

document.getElementById('avatar-upload-trigger').addEventListener('click', () => {
    avatarGuidanceModal.classList.remove('hidden');
});

document.querySelectorAll('[data-close-avatar-guidance]').forEach((button) => {
    button.addEventListener('click', () => avatarGuidanceModal.classList.add('hidden'));
});

document.getElementById('choose-avatar-button').addEventListener('click', () => {
    avatarGuidanceModal.classList.add('hidden');
    avatarInput.click();
});

avatarGuidanceModal.addEventListener('click', (event) => {
    if (event.target === avatarGuidanceModal) avatarGuidanceModal.classList.add('hidden');
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') avatarGuidanceModal.classList.add('hidden');
});

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById('avatar-preview');
            const initial = document.getElementById('avatar-initial');
            img.src = e.target.result;
            img.classList.remove('hidden');
            if (initial) initial.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>