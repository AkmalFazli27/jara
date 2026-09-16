<x-layouts.app title="Task List">
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-900">Task List</h2>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('lists.index', $showArchived ? [] : ['archived' => 1]) }}"
                    class="text-xs font-semibold text-slate-500 hover:text-indigo-600 px-3 py-2"
                >
                    {{ $showArchived ? 'Active Projects' : 'Archived Projects' }}
                </a>
                @unless ($showArchived)
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
                @endunless
            </div>
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
            @foreach ([['title' => $showArchived ? 'My Archived Projects' : 'My Projects', 'items' => $ownedLists], ['title' => $showArchived ? 'Shared Archived Projects' : 'Shared Projects', 'items' => $sharedLists]] as $group)
                @if ($group['items']->isNotEmpty())
                    <h3 class="text-sm font-bold text-slate-700 mb-3">{{ $group['title'] }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
                        @foreach ($group['items'] as $project)
                            <div x-data="{ editOpen: false }" class="relative bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <a href="{{ route('lists.show', $project) }}" class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $project->color }}"></span>
                                        <p class="text-sm font-bold text-slate-900 truncate">{{ $project->name }}</p>
                                    </a>
                                    @if ((int) $project->owner_id === (int) auth()->id())
                                        <div class="flex shrink-0 items-center gap-1.5">
                                            @unless ($showArchived)
                                                <button
                                                    type="button"
                                                    @click="editOpen = true"
                                                    class="rounded-lg bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-100 hover:text-indigo-800"
                                                >
                                                    Edit
                                                </button>
                                            @endunless
                                            <form method="POST" action="{{ route('lists.archive', $project) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200">
                                                    {{ $showArchived ? 'Unarchive' : 'Archive' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('lists.destroy', $project) }}" onsubmit="return confirm('Delete this project permanently?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-100">Delete</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('lists.show', $project) }}" class="block">
                                    <p class="text-xs text-slate-500 mb-3">{{ $project->tasks_count }} tasks · {{ $project->completed_tasks_count }} completed</p>
                                    <x-progress-bar :progress="$project->tasks_count > 0 ? round($project->completed_tasks_count / $project->tasks_count * 100) : 0" />
                                </a>

                                @if ((int) $project->owner_id === (int) auth()->id())
                                    <div
                                        x-show="editOpen"
                                        x-cloak
                                        @click.self="editOpen = false"
                                        @keydown.escape.window="editOpen = false"
                                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
                                        role="dialog"
                                        aria-modal="true"
                                        aria-label="Edit project"
                                    >
                                        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden text-left">
                                            <form method="POST" action="{{ route('lists.update', $project) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="px-6 pt-6">
                                                    <h2 class="text-base font-bold text-slate-900">Edit Project</h2>
                                                    <p class="text-xs text-slate-400 mt-1">Update your project details.</p>
                                                </div>
                                                <div class="px-6 py-5 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="project-name-{{ $project->id }}">Project Name</label>
                                                        <input
                                                            id="project-name-{{ $project->id }}"
                                                            name="name"
                                                            value="{{ $project->name }}"
                                                            required
                                                            maxlength="255"
                                                            class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="project-description-{{ $project->id }}">Description</label>
                                                        <textarea
                                                            id="project-description-{{ $project->id }}"
                                                            name="description"
                                                            rows="3"
                                                            class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent resize-none"
                                                        >{{ $project->description }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
                                                    <button type="button" @click="editOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl">Cancel</button>
                                                    <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl">Save changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
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
