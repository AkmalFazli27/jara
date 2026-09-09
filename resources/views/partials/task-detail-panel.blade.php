{{-- Panel detail tugas (pengganti TaskDetailPanel React).
      Dibuka bila ?focus=id ada; form PUT/DELETE + toggle selesai. --}}
@props(['task'])

<div class="fixed inset-0 z-40 bg-slate-900/20" @click="taskDetailOpen = false"></div>

<div class="fixed inset-y-0 right-0 z-50 w-full max-w-[480px] bg-white shadow-2xl flex flex-col border-l border-slate-200 overflow-hidden">
    <div class="flex items-center gap-2 px-5 py-3.5 border-b border-slate-100 bg-slate-50/80">
        <form method="POST" action="{{ route('tasks.toggle', $task) }}" class="flex-1">
            @csrf
            @method('PATCH')
            <button
                type="submit"
                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm {{ $task->is_completed ? 'bg-emerald-100 text-emerald-700' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-200' }}"
            >
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                {{ $task->is_completed ? 'Completed' : 'Mark as Complete' }}
            </button>
        </form>
        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="p-2.5 rounded-xl text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition-colors" title="Delete task">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14H6L5 6" />
                    <path d="M10 11v6M14 11v6" />
                    <path d="M9 6V4h6v2" />
                </svg>
            </button>
        </form>
        <a href="{{ route('lists.show', $task->list_id) }}" class="p-2.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" aria-label="Close">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </a>
    </div>

    <form method="POST" action="{{ route('tasks.update', $task) }}" class="flex-1 overflow-y-auto px-5 py-5 space-y-6">
        @csrf
        @method('PUT')

        <input
            name="title"
            value="{{ old('title', $task->title) }}"
            placeholder="Task title"
            required
            class="w-full text-xl font-bold text-slate-900 bg-transparent border-none outline-none focus:ring-0 placeholder-slate-300 leading-snug"
        />

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5" for="task-priority">Priority</label>
                <select
                    id="task-priority"
                    name="priority"
                    class="w-full text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent"
                >
                    @foreach (['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', $task->priority) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5" for="task-deadline">Deadline</label>
                <input
                    id="task-deadline"
                    type="date"
                    name="deadline"
                    value="{{ old('deadline', $task->deadline?->format('Y-m-d')) }}"
                    class="w-full text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent"
                />
            </div>
        </div>

        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Assignee</p>
            <div class="flex items-center gap-2.5">
                <x-avatar :name="$task->list?->owner?->name ?? '—'" :size="28" />
                <span class="text-sm font-medium text-slate-700">{{ $task->list?->owner?->name ?? '—' }}</span>
                <span class="ml-auto text-[11px] text-slate-400">from list owner</span>
            </div>
        </div>

        <div class="h-px bg-slate-100"></div>

        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2" for="task-desc">Description</label>
            <textarea
                id="task-desc"
                name="description"
                rows="4"
                placeholder="Add a more detailed description…"
                class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent resize-none transition-shadow"
            >{{ old('description', $task->description) }}</textarea>
        </div>

        <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition-colors">
            Save changes
        </button>

        <p class="text-[11px] text-slate-400 text-center">Subtasks &amp; activity comments are UI-only in the Figma design — no columns in DB (see ERD).</p>
    </form>
</div>
