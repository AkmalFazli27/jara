<?php

namespace App\Http\Controllers;

use App\Models\ListMember;
use App\Models\TaskList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ListController extends Controller
{
    /** Daftar milik + shared + pencarian (F-08). */
    public function index(Request $request): View
    {
        $userId = (int) Auth::id();
        $search = trim((string) $request->query('search', ''));

        $lists = TaskList::with(['owner', 'tasks'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn ($q) => $q->where('is_completed', true)])
            ->where(fn ($q) => $q
                ->where('owner_id', $userId)
                ->orWhereIn('id', ListMember::where('user_id', $userId)->select('list_id')))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc('updated_at')
            ->get();

        $tasks = \App\Models\Task::with('list.owner')
            ->whereIn('list_id', $lists->pluck('id')->all() ?: [0])
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('lists.index', compact('lists', 'tasks', 'search'));
    }

    /** Kanban: kolom To Do / Done nyata; In Progress & Review UI-only (tak ada kolom status di DB). */
    public function kanban(): View
    {
        $userId = (int) Auth::id();

        $tasks = \App\Models\Task::with('list.owner')
            ->whereIn('list_id', $this->accessibleListIds($userId))
            ->orderByDesc('updated_at')
            ->get();

        return view('lists.kanban', [
            'todo' => $tasks->where('is_completed', false)->values(),
            'done' => $tasks->where('is_completed', true)->values(),
        ]);
    }

    /** Detail daftar + filter/sort/group + progress (F-18). */
    public function show(Request $request, TaskList $list): View
    {
        $this->ensureAccess($list);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'in' => ['all', 'low', 'medium', 'high']],
            'sort' => ['nullable', 'in' => ['dueDate', 'priority', 'name']],
            'group' => ['nullable', 'in' => ['none', 'priority', 'status']],
            'focus' => ['nullable', 'integer'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $priority = $validated['priority'] ?? 'all';
        $sort = $validated['sort'] ?? 'dueDate';
        $group = $validated['group'] ?? 'none';

        $query = $list->tasks()->with('list.owner')
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($priority !== 'all', fn ($q) => $q->where('priority', $priority));

        $tasks = $query->get();

        $tasks = match ($sort) {
            'priority' => $tasks->sortBy(['priority' => 'asc', 'deadline' => 'asc'])->values(),
            'name' => $tasks->sortBy('title')->values(),
            default => $tasks->sortBy(fn ($t) => $t->deadline?->timestamp ?? PHP_INT_MAX)->values(),
        };

        $total = $list->tasks()->count();
        $doneCount = $list->tasks()->where('is_completed', true)->count();

        $groups = match ($group) {
            'priority' => collect(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'])
                ->map(fn ($label, $p) => ['label' => $label, 'items' => $tasks->where('priority', $p)->values()])
                ->filter(fn ($g) => $g['items']->isNotEmpty())->values(),
            'status' => collect([
                ['label' => 'To Do', 'items' => $tasks->where('is_completed', false)->values()],
                ['label' => 'Done', 'items' => $tasks->where('is_completed', true)->values()],
            ])->filter(fn ($g) => $g['items']->isNotEmpty())->values(),
            default => collect([['label' => null, 'items' => $tasks]]),
        };

        $focusedTask = isset($validated['focus'])
            ? $list->tasks()->find($validated['focus'])
            : null;

        return view('lists.show', [
            'list' => $list->load(['owner', 'members.user']),
            'groups' => $groups,
            'search' => $search,
            'priority' => $priority,
            'sort' => $sort,
            'groupBy' => $group,
            'progress' => $total > 0 ? (int) round($doneCount / $total * 100) : 0,
            'total' => $total,
            'doneCount' => $doneCount,
            'focusedTask' => $focusedTask,
        ]);
    }

    /** Buat daftar baru; pembuat otomatis member owner (ERD aturan 3). */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $list = TaskList::create([
            'owner_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        ListMember::firstOrCreate(
            ['list_id' => $list->id, 'user_id' => Auth::id()],
            ['role' => 'owner']
        );

        return redirect()->route('lists.show', $list)->with('status', 'Project created.');
    }

    public function update(Request $request, TaskList $list): RedirectResponse
    {
        $this->ensureOwner($list);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $list->update($validated);

        return back()->with('status', 'Project updated.');
    }

    public function destroy(TaskList $list): RedirectResponse
    {
        $this->ensureOwner($list);

        $list->delete(); // cascade ke tasks + members

        return redirect()->route('lists.index')->with('status', 'Project deleted.');
    }

    private function accessibleListIds(int $userId): array
    {
        $owned = TaskList::where('owner_id', $userId)->pluck('id');
        $member = ListMember::where('user_id', $userId)->pluck('list_id');

        return $owned->merge($member)->unique()->values()->all() ?: [0];
    }

    private function ensureAccess(TaskList $list): void
    {
        $userId = (int) Auth::id();

        if ($list->owner_id !== $userId
            && ! ListMember::where('list_id', $list->id)->where('user_id', $userId)->exists()) {
            abort(403);
        }
    }

    private function ensureOwner(TaskList $list): void
    {
        if ((int) $list->owner_id !== (int) Auth::id()) {
            abort(403, 'Only the owner can manage this list.');
        }
    }
}
