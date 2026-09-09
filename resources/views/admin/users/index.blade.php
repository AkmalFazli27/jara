<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daftar Pengguna - Admin JARA</title>

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
                    <span class="text-sm font-medium text-slate-300">Daftar Pengguna</span>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-xs text-slate-400 hover:text-white transition-all">
                        ← Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Daftar Pengguna Sistem</h1>
                <p class="text-slate-400 text-sm mt-1">Kelola dan pantau seluruh akun pengguna terdaftar.</p>
            </div>

            <!-- Placeholders for F-05 Create User -->
            <div>
                <button type="button" disabled
                        class="px-4 py-2.5 rounded-xl bg-amber-600/50 text-amber-200 text-xs font-semibold cursor-not-allowed opacity-75">
                    + Tambah Akun Baru (F-05)
                </button>
            </div>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-6 shadow-xl">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <!-- Search Input -->
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari berdasarkan nama atau email..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Role Filter Dropdown -->
                <div class="w-full sm:w-48">
                    <select name="role" onchange="this.form.submit()"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                        <option value="">Semua Role</option>
                        <option value="admin" {{ $selectedRole === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ $selectedRole === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                </div>

                <!-- Submit & Reset Buttons -->
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold transition-all">
                        Cari
                    </button>
                    @if($search || $selectedRole)
                        <a href="{{ route('admin.users.index') }}"
                           class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-all flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- User Data Table -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Nama Pengguna</th>
                            <th class="px-6 py-4">Alamat Email</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Terdaftar</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-sm">
                        @forelse($users as $userItem)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">#{{ $userItem->id }}</td>
                                <td class="px-6 py-4 font-semibold text-white">{{ $userItem->name }}</td>
                                <td class="px-6 py-4 text-slate-300">{{ $userItem->email }}</td>
                                <td class="px-6 py-4">
                                    @if($userItem->isAdmin())
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                            Admin
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/30">
                                            User
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-400">{{ $userItem->created_at?->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-xs text-slate-500 italic">No Actions (F-06)</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    Tidak ada pengguna yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </main>
</body>
</html>
