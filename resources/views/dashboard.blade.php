<x-layouts.app title="Dashboard">
    <div class="space-y-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 mb-1">Good morning, {{ auth()->user()?->name ?? 'there' }}</h2>
            <p class="text-sm text-slate-500">Here's what's on your plate for today, {{ now()->format('F j') }}.</p>
        </div>

        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($stats as $stat)
                <x-stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :sub="$stat['sub']"
                    :accent="$stat['accent']"
                />
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-sm font-bold text-slate-800">Recent Tasks</h3>
                    <a href="{{ route('lists.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                        View all →
                    </a>
                </div>
                <div class="space-y-2">
                    @forelse ($recent as $task)
                        <a
                            href="{{ route('lists.show', $task->list_id) }}?focus={{ $task->id }}"
                            class="flex items-center justify-between gap-4 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-colors"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <x-avatar :name="$task->list?->owner?->name ?? '—'" :size="26" />
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">{{ $task->title }}</p>
                                    <p class="text-xs text-slate-400">Due {{ $task->due_label }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <x-status-badge :completed="$task->is_completed" />
                                <x-priority-badge :priority="$task->priority" />
                            </div>
                        </a>
                    @empty
                        <x-empty-state message="No active tasks. Enjoy the calm." />
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-5">Team Workload</h3>
                <div class="space-y-4">
                    @forelse ($workload as $row)
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <x-avatar :name="$row['name']" :size="28" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-semibold text-slate-700 truncate">{{ $row['name'] }}</p>
                                        <p class="text-xs text-slate-500 ml-2">{{ $row['count'] }} tasks</p>
                                    </div>
                                </div>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 bg-indigo-500" style="width: {{ $row['pct'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state message="No workload data yet." />
                    @endforelse
                </div>
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-700 mb-3">By Priority</p>
                    @foreach (['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $p => $label)
                        <div class="flex items-center justify-between mb-2">
                            <x-priority-badge :priority="$p" />
                            <span class="text-xs font-semibold text-slate-700">{{ $priorityCounts[$p] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @include('partials.create-project-modal')
</x-layouts.app>
