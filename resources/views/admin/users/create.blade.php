<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tambah Akun Pengguna - Admin JARA</title>

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
    <!-- Admin Navbar -->
    <nav class="bg-slate-900/80 backdrop-blur-md border-b border-amber-500/20 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold">
                            A
                        </div>
                        <span class="text-xl font-bold tracking-tight text-white">JARA Admin</span>
                    </a>
                    <span class="text-slate-600">/</span>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-400 hover:text-white transition-all">Daftar Pengguna</a>
                    <span class="text-slate-600">/</span>
                    <span class="text-sm font-medium text-slate-300">Tambah Akun</span>
                </div>

                <div>
                    <a href="{{ route('admin.users.index') }}"
                       class="text-xs text-slate-400 hover:text-white transition-all flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Daftar</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-2xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Tambah Akun Pengguna Baru</h1>
            <p class="text-slate-400 text-sm mt-1">Buat akun baru untuk pengguna sistem JARA.</p>
        </div>

        <!-- Create User Form Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Name Input -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required
                           value="{{ old('name') }}"
                           placeholder="Masukkan nama lengkap..."
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border @error('name') border-red-500/80 @else border-slate-800 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm transition-all">
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Alamat Email</label>
                    <input id="email" name="email" type="email" required
                           value="{{ old('email') }}"
                           placeholder="contoh@email.com"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border @error('email') border-red-500/80 @else border-slate-800 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm transition-all">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Selector -->
                <div>
                    <label for="role" class="block text-sm font-medium text-slate-300 mb-1.5">Role Akun</label>
                    <select id="role" name="role" required
                            class="w-full px-4 py-3 rounded-xl bg-slate-950 border @error('role') border-red-500/80 @else border-slate-800 @enderror text-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>-- Pilih Role --</option>
                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User (Pengguna Reguler)</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Administrator)</option>
                    </select>
                    @error('role')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-slate-800 pt-5 space-y-4">
                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Kata Sandi</label>
                        <input id="password" name="password" type="password" required
                               placeholder="••••••••"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border @error('password') border-red-500/80 @else border-slate-800 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm transition-all">
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">Konfirmasi Kata Sandi</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               placeholder="••••••••"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm transition-all">
                    </div>
                </div>

                <!-- Submit & Cancel Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.users.index') }}"
                       class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition-all">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold text-sm shadow-md shadow-amber-600/20 transition-all">
                        Buat Akun
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
