<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard with statistics.
     */
    public function __invoke(): View
    {
        $user = Auth::user();

        $listIds = $this->accessibleListIds((int) $user->id);

        $tasks = Task::with('list.owner')
            ->whereIn('list_id', $listIds)
            ->orderByDesc('updated_at')
            ->get();

        $total = $tasks->count();
        $completed = $tasks->where('is_completed', true)->count();
        $active = $total - $completed;
        $overdue = $tasks->filter->is_overdue->count();
        $dueThisWeek = $tasks->filter(
            fn (Task $t) => ! $t->is_completed && $t->deadline && $t->deadline->between(now()->startOfDay(), now()->addWeek())
        )->count();

        $recent = $tasks->where('is_completed', false)->take(4);

        $workload = $tasks
            ->groupBy(fn (Task $t) => $t->list?->owner?->name ?? 'Unassigned')
            ->map(fn ($group) => [
                'name' => $group->first()->list?->owner?->name ?? 'Unassigned',
                'count' => $group->count(),
                'pct' => $total > 0 ? (int) round($group->count() / $total * 100) : 0,
            ])
            ->take(6);

        return view('dashboard', [
            'stats' => [
                ['label' => 'Total Tasks', 'value' => $total, 'sub' => 'across all projects', 'accent' => 'bg-indigo-50 text-indigo-700'],
                ['label' => 'In Progress', 'value' => $active, 'sub' => 'active right now', 'accent' => 'bg-violet-50 text-violet-700'],
                ['label' => 'Due This Week', 'value' => $dueThisWeek, 'sub' => 'need attention', 'accent' => 'bg-amber-50 text-amber-700'],
                ['label' => 'Completed', 'value' => $completed, 'sub' => 'tasks closed', 'accent' => 'bg-emerald-50 text-emerald-700'],
            ],
            'recent' => $recent,
            'workload' => $workload,
            'priorityCounts' => [
                'high' => $tasks->where('priority', 'high')->count(),
                'medium' => $tasks->where('priority', 'medium')->count(),
                'low' => $tasks->where('priority', 'low')->count(),
            ],
            'overdueCount' => $overdue,
        ]);
    }

    public function index(): View
    {
        return $this->__invoke();
    }

    /** Id daftar yang boleh dibuka user: owner ATAU tercatat di list_members. */
    private function accessibleListIds(int $userId): array
    {
        $owned = TaskList::where('owner_id', $userId)->pluck('id');
        $member = \App\Models\ListMember::where('user_id', $userId)->pluck('list_id');

        return $owned->merge($member)->unique()->values()->all() ?: [0];
    }
}
