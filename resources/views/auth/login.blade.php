<x-layouts.guest title="Log in">
    <div class="min-h-screen flex">
        <div class="flex-1 flex flex-col justify-center px-8 sm:px-16 lg:px-20 xl:px-28 bg-white min-w-0">
            <div class="w-full max-w-sm mx-auto">
                <div class="flex items-center gap-2.5 mb-12">
                    <x-logo />
                    <span class="text-lg font-bold text-slate-900 tracking-tight">Jara</span>
                </div>

                <h1 class="text-[2rem] font-bold text-slate-900 leading-tight mb-2">Welcome back</h1>
                <p class="text-sm text-slate-500 mb-9">Sign in to your workspace to continue.</p>

                @if (session('status'))
                    <div class="p-4 mb-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-sm font-medium">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('info'))
                    <div class="p-4 mb-4 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 text-sm font-medium">
                        {{ session('info') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="email">Email address</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            autofocus
                            required
                            placeholder="alex@jara.app"
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 text-sm border rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-shadow {{ $errors->has('email') ? 'border-rose-300' : 'border-slate-200' }}"
                        />
                        @error('email')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700" for="password">Password</label>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="show ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                placeholder="••••••••"
                                class="w-full px-4 py-3 pr-11 text-sm border rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-shadow {{ $errors->has('password') ? 'border-rose-300' : 'border-slate-200' }}"
                            />
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                                :aria-label="show ? 'Hide password' : 'Show password'"
                            >
                                <svg x-show="!show" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg x-show="show" x-cloak width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" />
                                    <line x1="1" y1="1" x2="23" y2="23" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-xs text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400" />
                        Remember me
                    </label>

                    <button
                        type="submit"
                        class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold transition-all shadow-md shadow-indigo-200 hover:shadow-lg hover:shadow-indigo-200 flex items-center justify-center gap-2 mt-1"
                    >
                        Log In
                    </button>
                </form>
            </div>
        </div>

        <div
            class="hidden lg:flex flex-1 relative items-center justify-center overflow-hidden"
            style="background: linear-gradient(135deg, #3730a3 0%, #4f46e5 45%, #7c3aed 100%)"
        >
            <div
                class="absolute inset-0 opacity-[0.04]"
                style="background-image: linear-gradient(white 1px, transparent 1px), linear-gradient(90deg, white 1px, transparent 1px); background-size: 40px 40px;"
            ></div>
            <div class="absolute top-1/4 left-1/3 w-64 h-64 rounded-full bg-violet-500 opacity-20 blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-1/4 right-1/4 w-48 h-48 rounded-full bg-indigo-300 opacity-15 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col items-center px-12 text-center">
                @include('partials.productivity-illustration')
                <h2 class="mt-6 text-2xl font-bold text-white leading-snug">Every task, one place.</h2>
                <p class="mt-3 text-sm text-indigo-200 leading-relaxed max-w-xs">
                    Assign, track, and ship work together — no status meetings required.
                </p>
            </div>
        </div>
    </div>
</x-layouts.guest>
