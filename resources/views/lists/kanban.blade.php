<x-layouts.app title="Kanban Board">
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Kanban Board</h2>
                <p class="text-xs text-slate-500 mt-1">In Progress &amp; Review are visual-only — the database tracks Done vs To Do (see ERD).</p>
            </div>
            <button
                @click="createProjectOpen = true"
                class="flex items-center gap-1.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm"
            >
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                New Project
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 items-start">
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-3">
                <div class="flex items-center gap-2 mb-3 px-1">
                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                    <span class="text-xs font-bold text-slate-700">To Do</span>
                    <span class="ml-auto text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded-full px-2 py-0.5">{{ $todo->count() }}</span>
                </div>
                <div class="space-y-2">
                    @forelse ($todo as $task)
                        <x-task-card :task="$task" />
                    @empty
                        <div class="text-center py-8 text-xs text-slate-400">No tasks</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-slate-50 rounded-2xl border border-dashed border-slate-200 p-3">
                <div class="flex items-center gap-2 mb-3 px-1">
                    <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                    <span class="text-xs font-bold text-slate-700">In Progress</span>
                    <span class="ml-auto text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded-full px-2 py-0.5">0</span>
                </div>
                <div class="text-center py-8 text-xs text-slate-400 px-3">Visual-only in this phase — no status column in DB.</div>
            </div>

            <div class="bg-slate-50 rounded-2xl border border-dashed border-slate-200 p-3">
                <div class="flex items-center gap-2 mb-3 px-1">
                    <span class="w-2 h-2 rounded-full bg-violet-400"></span>
                    <span class="text-xs font-bold text-slate-700">Review</span>
                    <span class="ml-auto text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded-full px-2 py-0.5">0</span>
                </div>
                <div class="text-center py-8 text-xs text-slate-400 px-3">Visual-only in this phase — no status column in DB.</div>
            </div>

            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-3">
                <div class="flex items-center gap-2 mb-3 px-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-bold text-slate-700">Done</span>
                    <span class="ml-auto text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded-full px-2 py-0.5">{{ $done->count() }}</span>
                </div>
                <div class="space-y-2">
                    @forelse ($done as $task)
                        <x-task-card :task="$task" />
                    @empty
                        <div class="text-center py-8 text-xs text-slate-400">No tasks</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @include('partials.create-project-modal')
</x-layouts.app>
