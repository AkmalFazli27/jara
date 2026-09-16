<x-layouts.app title="Task List">
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-900">Task List</h2>
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

        <form method="GET" action="{{ route('lists.index') }}" class="flex flex-wrap items-center gap-3 mb-5">
            <div class="relative flex-1 min-w-[200px] max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search projects..."
                    class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 placeholder-slate-400"
                />
            </div>
            <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-700 transition-colors">Search</button>
            @if ($search !== '')
                <a href="{{ route('lists.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">Clear</a>
            @endif
        </form>

        @if ($lists->isNotEmpty())
            @foreach ([['title' => 'My Projects', 'items' => $ownedLists], ['title' => 'Shared Projects', 'items' => $sharedLists]] as $group)
                @if ($group['items']->isNotEmpty())
                    <h3 class="text-sm font-bold text-slate-700 mb-3">{{ $group['title'] }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
                        @foreach ($group['items'] as $project)
                    <a href="{{ route('lists.show', $project) }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $project->color }}"></span>
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $project->name }}</p>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">{{ $project->tasks_count }} tasks · {{ $project->completed_tasks_count }} completed</p>
                        <x-progress-bar :progress="$project->tasks_count > 0 ? round($project->completed_tasks_count / $project->tasks_count * 100) : 0" />
                    </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left text-xs font-semibold text-slate-500 px-5 py-3">Task</th>
                        <th class="text-left text-xs font-semibold text-slate-500 px-4 py-3 hidden md:table-cell">Status</th>
                        <th class="text-left text-xs font-semibold text-slate-500 px-4 py-3 hidden lg:table-cell">Priority</th>
                        <th class="text-left text-xs font-semibold text-slate-500 px-4 py-3 hidden lg:table-cell">Project</th>
                        <th class="text-left text-xs font-semibold text-slate-500 px-5 py-3">Due</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr class="hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('lists.show', $task->list_id) }}?focus={{ $task->id }}" class="block">
                                    <p class="text-sm font-semibold text-slate-800">{{ $task->title }}</p>
                                    @if ($task->description)
                                        <p class="text-xs text-slate-400 mt-0.5 line-clamp-1 hidden sm:block">{{ $task->description }}</p>
                                    @endif
                                </a>
                            </td>
                            <td class="px-4 py-3.5 hidden md:table-cell">
                                <x-status-badge :completed="$task->is_completed" />
                            </td>
                            <td class="px-4 py-3.5 hidden lg:table-cell">
                                <x-priority-badge :priority="$task->priority" />
                            </td>
                            <td class="px-4 py-3.5 hidden lg:table-cell">
                                <span class="text-xs text-slate-600 font-medium">{{ $task->list?->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-medium {{ $task->is_overdue ? 'text-rose-500' : 'text-slate-500' }}">{{ $task->due_label }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><x-empty-state /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $tasks->links() }}</div>
    </div>

    @include('partials.create-project-modal')
</x-layouts.app>
