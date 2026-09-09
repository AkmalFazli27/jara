{{-- Kartu tugas untuk Kanban. Menerima model Task Eloquent.
      Assignee diderivasi dari list owner (tasks tidak punya assignee di DB). --}}
@props(['task'])

@php
    $due = $task->deadline ? $task->deadline->format('M j') : '—';
    $ownerName = $task->list?->owner?->name ?? '—';
@endphp

<a
    href="{{ route('lists.show', $task->list_id) }}?focus={{ $task->id }}"
    {{ $attributes->merge(['class' => 'block bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md hover:border-indigo-200 transition-all duration-150 group']) }}
>
    <div class="flex items-start justify-between gap-3 mb-2">
        <p class="text-sm font-semibold text-slate-800 leading-snug group-hover:text-indigo-700 transition-colors">
            {{ $task->title }}
        </p>
        <x-priority-badge :priority="$task->priority" />
    </div>

    @if ($task->description)
        <p class="text-xs text-slate-500 leading-relaxed mb-3 line-clamp-2">{{ $task->description }}</p>
    @endif

    <div class="flex items-center justify-between">
        <x-status-badge :completed="$task->is_completed" />
        <div class="flex items-center gap-2">
            <span class="text-xs {{ $task->is_overdue ? 'text-rose-500 font-semibold' : 'text-slate-400' }}">{{ $due }}</span>
            <x-avatar :name="$ownerName" :size="24" />
        </div>
    </div>
</a>
