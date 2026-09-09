@php
    /** @var \Illuminate\Support\Collection $projects */
    $projects = $projects ?? collect();
    $routeList = request()->route('list');
    $routeListId = is_object($routeList) ? $routeList->id : $routeList;
    $activeProjectId = $activeProjectId ?? $routeListId ?? null;
@endphp

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-40 w-60 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 lg:translate-x-0 lg:static lg:flex -translate-x-full"
>
    <div class="px-5 py-5 border-b border-slate-100">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <x-logo />
            <span class="text-base font-bold text-slate-900 tracking-tight">Jara</span>
        </a>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2 mb-2 mt-1">Workspace</p>

        <a
            href="{{ route('dashboard') }}"
            @click="sidebarOpen = false"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
        >
            <span class="{{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                </svg>
            </span>
            Dashboard
        </a>

        <a
            href="{{ route('lists.index') }}"
            @click="sidebarOpen = false"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('lists.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
        >
            <span class="{{ request()->routeIs('lists.index') ? 'text-indigo-600' : 'text-slate-400' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <line x1="8" y1="6" x2="21" y2="6" />
                    <line x1="8" y1="12" x2="21" y2="12" />
                    <line x1="8" y1="18" x2="21" y2="18" />
                    <circle cx="3.5" cy="6" r="1.5" fill="currentColor" stroke="none" />
                    <circle cx="3.5" cy="12" r="1.5" fill="currentColor" stroke="none" />
                    <circle cx="3.5" cy="18" r="1.5" fill="currentColor" stroke="none" />
                </svg>
            </span>
            Task List
        </a>

        <a
            href="{{ route('lists.kanban') }}"
            @click="sidebarOpen = false"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('lists.kanban') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
        >
            <span class="{{ request()->routeIs('lists.kanban') ? 'text-indigo-600' : 'text-slate-400' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="5" height="18" rx="1.5" />
                    <rect x="10" y="3" width="5" height="12" rx="1.5" />
                    <rect x="17" y="3" width="5" height="8" rx="1.5" />
                </svg>
            </span>
            Kanban Board
        </a>

        <div class="flex items-center justify-between px-2 mb-2 mt-4">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Projects</p>
            <button
                @click="createProjectOpen = true; sidebarOpen = false"
                class="text-slate-400 hover:text-indigo-600 transition-colors"
                title="New project"
            >
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
                </svg>
            </button>
        </div>

        @forelse ($projects as $project)
            <a
                href="{{ route('lists.show', $project) }}"
                @click="sidebarOpen = false"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ (int) $activeProjectId === (int) $project->id ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
            >
                <span class="w-2 h-2 rounded-full shrink-0" style="background: {{ $project->color }}"></span>
                <span class="truncate">{{ $project->name }}</span>
            </a>
        @empty
            <p class="px-3 py-2 text-xs text-slate-400">No projects yet.</p>
        @endforelse
    </nav>

    <div class="px-3 py-4 border-t border-slate-100">
        <div class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-slate-50 transition-colors">
            <x-avatar :name="auth()->user()?->name ?? 'Guest'" :size="30" />
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()?->name ?? 'Guest' }}</p>
                <p class="text-xs text-slate-400 truncate">{{ auth()->user()?->email ?? '' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="text-slate-400 hover:text-rose-600 transition-colors"
                    title="Log out"
                >
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
