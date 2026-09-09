@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title . ' — Jara' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
<div
    class="min-h-screen flex"
    x-data="{ sidebarOpen: false, createProjectOpen: false, taskDetailOpen: false }"
    @keydown.escape.window="sidebarOpen = false; createProjectOpen = false"
>
    @include('partials.sidebar')

    <div
        x-show="sidebarOpen"
        x-cloak
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-slate-900/20 z-30 lg:hidden"
    ></div>

    <div class="flex-1 flex flex-col min-w-0">
        @include('partials.topbar')

        <main class="flex-1 px-5 py-7 md:px-8 max-w-[1400px] mx-auto w-full">
            @if (session('status'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@stack('modals')
@stack('scripts')
</body>
</html>
