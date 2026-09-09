<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Masuk - JARA Advanced To-Do List</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 text-slate-100 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-slate-900 to-black">
    <div class="max-w-md w-full space-y-8 bg-slate-800/80 backdrop-blur-xl p-8 rounded-2xl border border-slate-700/60 shadow-2xl">
        <!-- Logo & Title -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-3xl font-bold tracking-tight text-white">JARA</h2>
            <p class="text-sm text-slate-400">Masuk ke akun Anda untuk mengelola tugas & proyek</p>
        </div>

        <!-- Session Flash Message -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('info'))
            <div class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-400 text-sm">
                {{ session('info') }}
            </div>
        @endif

        <!-- Form Login -->
        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Alamat Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border @error('email') border-red-500/80 @else border-slate-700 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                           placeholder="nama@email.com">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Kata Sandi</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="w-full px-4 py-3 rounded-xl bg-slate-900/80 border @error('password') border-red-500/80 @else border-slate-700 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 block text-sm text-slate-400">Ingat saya</label>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-500/50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                    Masuk ke Sistem
                </button>
            </div>
        </form>
    </div>
</body>
</html>
