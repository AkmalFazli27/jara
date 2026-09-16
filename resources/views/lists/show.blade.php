<x-layouts.app :title="$list->name">
    <div x-data="{ taskDetailOpen: {{ $focusedTask ? 'true' : 'false' }} }">
        <div class="flex items-center gap-2 mb-5">
            <a href="{{ route('lists.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium transition-colors flex items-center gap-1.5">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Projects
            </a>
            <svg width="14" height="14" fill="none" stroke="#cbd5e1" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6" /></svg>
            <span class="text-sm font-semibold text-slate-800">{{ $list->name }}</span>
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="w-3 h-3 rounded-full" style="background: {{ $list->color }}"></span>
                    <h2 class="text-xl font-bold text-slate-900">{{ $list->name }}</h2>
                </div>
                <p class="text-sm text-slate-500 ml-6">{{ $total }} tugas · {{ $doneCount }} selesai</p>
                @if ($list->description)
                    <p class="text-sm text-slate-500 ml-6 mt-1 max-w-xl">{{ $list->description }}</p>
                @endif
            </div>
            <button
                @click="$dispatch('open-create-task')"
                onclick="document.getElementById('create-task-form').scrollIntoView({ behavior: 'smooth', block: 'center' }); document.getElementById('task-title-input')?.focus()"
                class="flex items-center gap-1.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm"
            >
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Tambah tugas
            </button>
        </div>

        <div class="mb-6 bg-white border border-slate-200 rounded-2xl px-5 py-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-600">Progress keseluruhan</span>
                <span class="text-xs font-bold text-indigo-600">{{ $progress }}%</span>
            </div>
            <x-progress-bar :progress="$progress" />
        </div>

        <form method="GET" action="{{ route('lists.show', $list) }}" class="flex flex-wrap items-center gap-2.5 mb-5">
            <div class="relative flex-1 min-w-[180px] max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari tugas..."
                    class="w-full pl-8 pr-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 placeholder-slate-400"
                />
            </div>

            <select name="priority" onchange="this.form.submit()" class="text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="all" @selected($priority === 'all')>Semua prioritas</option>
                <option value="high" @selected($priority === 'high')>Tinggi</option>
                <option value="medium" @selected($priority === 'medium')>Sedang</option>
                <option value="low" @selected($priority === 'low')>Rendah</option>
            </select>

            <select name="status" onchange="this.form.submit()" class="text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="all" @selected($statusFilter === 'all')>Semua status</option>
                <option value="todo" @selected($statusFilter === 'todo')>Belum selesai</option>
                <option value="done" @selected($statusFilter === 'done')>Selesai</option>
            </select>

            <select name="deadline" onchange="this.form.submit()" class="text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="all" @selected($deadlineFilter === 'all')>Semua tenggat</option>
                <option value="today" @selected($deadlineFilter === 'today')>Hari ini</option>
                <option value="overdue" @selected($deadlineFilter === 'overdue')>Terlambat</option>
            </select>

            <select name="sort" onchange="this.form.submit()" class="text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="dueDate" @selected($sort === 'dueDate')>Urutkan: Tenggat</option>
                <option value="priority" @selected($sort === 'priority')>Urutkan: Prioritas</option>
                <option value="name" @selected($sort === 'name')>Urutkan: Nama</option>
            </select>

            <select name="group" onchange="this.form.submit()" class="text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="none" @selected($groupBy === 'none')>Tanpa pengelompokan</option>
                <option value="priority" @selected($groupBy === 'priority')>Kelompokkan: Prioritas</option>
                <option value="status" @selected($groupBy === 'status')>Kelompokkan: Status</option>
            </select>

            <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-700 transition-colors">Terapkan</button>
        </form>

        <div class="space-y-4">
            @forelse ($groups as $group)
                <div>
                    @if ($group['label'])
                        <div class="flex items-center gap-2 mb-2 px-1">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">{{ $group['label'] }}</span>
                            <span class="text-xs text-slate-400">({{ $group['items']->count() }})</span>
                            <div class="flex-1 h-px bg-slate-100"></div>
                        </div>
                    @endif
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        @forelse ($group['items'] as $task)
                            @php($isDone = $task->is_completed)
                            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50 transition-colors {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        aria-label="{{ $isDone ? 'Reopen task' : 'Complete task' }}"
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all {{ $isDone ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300 hover:border-indigo-400' }}"
                                    >
                                        @if ($isDone)
                                            <svg width="9" height="9" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                        @endif
                                    </button>
                                </form>

                                <a href="{{ route('lists.show', ['list' => $list, ...request()->except('focus'), 'focus' => $task->id]) }}" class="flex-1 text-sm font-medium min-w-0 truncate {{ $isDone ? 'line-through text-slate-400' : 'text-slate-800' }}">
                                    {{ $task->title }}
                                </a>

                                <div class="hidden sm:block shrink-0">
                                    <x-priority-badge :priority="$task->priority" />
                                </div>

                                <span class="text-xs font-semibold shrink-0 {{ $task->is_overdue ? 'text-rose-500' : 'text-slate-400' }}">
                                    {{ $task->is_overdue ? 'Terlambat · ' : '' }}{{ $task->due_label }}
                                </span>

                                <x-avatar :name="$task->list?->owner?->name ?? '—'" :size="26" />
                            </div>
                        @empty
                            <x-empty-state />
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm"><x-empty-state /></div>
            @endforelse
        </div>

        <form
            id="create-task-form"
            method="POST"
            action="{{ route('tasks.store', $list) }}"
            class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-5"
        >
            @csrf
            <h3 class="text-sm font-bold text-slate-800 mb-4">Buat tugas di {{ $list->name }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <input
                    id="task-title-input"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    maxlength="255"
                    placeholder="Judul tugas *"
                    class="px-3 py-2 text-sm border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 placeholder-slate-400 {{ $errors->has('title') ? 'border-rose-400' : 'border-slate-200' }}"
                />
                <select name="priority" class="text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="medium" @selected(old('priority', 'medium') === 'medium')>Prioritas: Sedang</option>
                    <option value="high" @selected(old('priority') === 'high')>Prioritas: Tinggi</option>
                    <option value="low" @selected(old('priority') === 'low')>Prioritas: Rendah</option>
                </select>
                <input
                    type="date"
                    name="deadline"
                    value="{{ old('deadline') }}"
                    class="text-sm border rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 {{ $errors->has('deadline') ? 'border-rose-400' : 'border-slate-200' }}"
                />
                <input
                    name="description"
                    value="{{ old('description') }}"
                    maxlength="2000"
                    placeholder="Deskripsi singkat (opsional)"
                    class="px-3 py-2 text-sm border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 placeholder-slate-400 {{ $errors->has('description') ? 'border-rose-400' : 'border-slate-200' }}"
                />
            </div>
            @if ($errors->hasAny(['title', 'description', 'priority', 'deadline']))
                <div class="mt-3 space-y-1 text-sm text-rose-600">
                    @foreach (['title', 'description', 'priority', 'deadline'] as $field)
                        @error($field)
                            <p>{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>
            @endif
            <button type="submit" class="mt-3 px-4 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">Tambah tugas</button>
        </form>

        @if ($focusedTask)
            <div x-show="taskDetailOpen" x-cloak>
                @include('partials.task-detail-panel', ['task' => $focusedTask])
            </div>
        @endif
    </div>

    @include('partials.create-project-modal')
</x-layouts.app>
