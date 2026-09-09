<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pengaturan Profil - JARA Advanced To-Do List</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col">
    <!-- Navbar -->
    <nav class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <a href="{{ $user->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center font-bold">
                            J
                        </div>
                        <span class="text-xl font-bold tracking-tight text-white">JARA</span>
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="text-sm font-medium text-slate-300">Pengaturan Profil</span>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="{{ $user->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                       class="text-xs text-slate-400 hover:text-white transition-all flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Pengaturan Profil & Akun</h1>
            <p class="text-slate-400 text-sm mt-1">Perbarui informasi profil dan kata sandi akun Anda.</p>
        </div>

        <!-- Success Toast Alert -->
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Card 1: Informasi Profil -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="border-b border-slate-800 pb-4">
                <h2 class="text-lg font-semibold text-white">Informasi Profil</h2>
                <p class="text-xs text-slate-400">Perbarui nama dan alamat email akun Anda.</p>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required
                           value="{{ old('name', $user->name) }}"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border @error('name') border-red-500/80 @else border-slate-800 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Alamat Email</label>
                    <input id="email" name="email" type="email" required
                           value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border @error('email') border-red-500/80 @else border-slate-800 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        Role: {{ ucfirst($user->role) }}
                    </span>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md transition-all">
                        Simpan Profil
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Update Kata Sandi -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="border-b border-slate-800 pb-4">
                <h2 class="text-lg font-semibold text-white">Ubah Kata Sandi</h2>
                <p class="text-xs text-slate-400">Pastikan akun Anda menggunakan kata sandi yang kuat dan aman.</p>
            </div>

            <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-300 mb-1">Kata Sandi Saat Ini</label>
                    <input id="current_password" name="current_password" type="password" required
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border @error('current_password') border-red-500/80 @else border-slate-800 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                           placeholder="••••••••">
                    @error('current_password')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Kata Sandi Baru</label>
                    <input id="password" name="password" type="password" required
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border @error('password') border-red-500/80 @else border-slate-800 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1">Konfirmasi Kata Sandi Baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                           placeholder="••••••••">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md transition-all">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
