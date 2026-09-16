<?php

namespace App\Http\Controllers;

use App\Models\ListMember;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ListController extends Controller
{
    /** Daftar milik + shared + pencarian (F-08). */
    public function index(Request $request): View
    {
        $userId = (int) Auth::id();
        $search = trim((string) $request->query('search', ''));
        $showArchived = $request->boolean('archived');

        $lists = TaskList::with(['owner', 'tasks'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn ($q) => $q->where('is_completed', true)])
            ->where(fn ($q) => $q
                ->where('owner_id', $userId)
                ->orWhereIn('id', ListMember::where('user_id', $userId)->select('list_id')))
            ->where('is_archived', $showArchived)
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc('updated_at')
            ->get();

        $ownedLists = $lists->where('owner_id', $userId)->values();
        $sharedLists = $lists->where('owner_id', '!=', $userId)->values();

        $tasks = \App\Models\Task::with('list.owner')
            ->whereIn('list_id', $lists->pluck('id')->all() ?: [0])
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('lists.index', compact('lists', 'ownedLists', 'sharedLists', 'tasks', 'search', 'showArchived'));
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
            'priority' => ['nullable', 'in:all,low,medium,high'],
            'status' => ['nullable', 'in:all,todo,done'],
            'deadline' => ['nullable', 'in:all,today,overdue'],
            'sort' => ['nullable', 'in:dueDate,priority,name'],
            'group' => ['nullable', 'in:none,priority,status'],
            'focus' => ['nullable', 'integer'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $priority = $validated['priority'] ?? 'all';
        $status = $validated['status'] ?? 'all';
        $deadline = $validated['deadline'] ?? 'all';
        $sort = $validated['sort'] ?? 'dueDate';
        $group = $validated['group'] ?? 'none';

        $query = $list->tasks()->with('list.owner')
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($priority !== 'all', fn ($q) => $q->where('priority', $priority))
            ->when($status === 'todo', fn ($q) => $q->where('is_completed', false))
            ->when($status === 'done', fn ($q) => $q->where('is_completed', true))
            ->when($deadline === 'today', fn ($q) => $q->whereDate('deadline', today(\App\Models\Task::CALENDAR_TIMEZONE)))
            ->when($deadline === 'overdue', fn ($q) => $q
                ->where('is_completed', false)
                ->whereNotNull('deadline')
                ->whereDate('deadline', '<', today(\App\Models\Task::CALENDAR_TIMEZONE)));

        $query = match ($sort) {
            'priority' => $query
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
                ->orderByRaw('deadline IS NULL')
                ->orderBy('deadline')
                ->orderBy('title'),
            'name' => $query->orderBy('title'),
            default => $query->orderByRaw('deadline IS NULL')->orderBy('deadline')->orderBy('title'),
        };

        $tasks = $query->get();

        $total = $list->tasks()->count();
        $doneCount = $list->tasks()->where('is_completed', true)->count();

        $groups = match ($group) {
            'priority' => collect(['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'])
                ->map(fn ($label, $p) => ['label' => $label, 'items' => $tasks->where('priority', $p)->values()])
                ->filter(fn ($g) => $g['items']->isNotEmpty())->values(),
            'status' => collect([
                ['label' => 'Belum selesai', 'items' => $tasks->where('is_completed', false)->values()],
                ['label' => 'Selesai', 'items' => $tasks->where('is_completed', true)->values()],
            ])->filter(fn ($g) => $g['items']->isNotEmpty())->values(),
            default => collect([['label' => null, 'items' => $tasks]]),
        };

        $focusedTask = isset($validated['focus'])
            ? $list->tasks()->find($validated['focus'])
            : null;

        $memberIds = $list->members()->pluck('user_id')->all();
        $candidateUsers = \App\Models\User::whereNotIn('id', $memberIds ?: [0])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('lists.show', [
            'list' => $list->load(['owner', 'members.user']),
            'candidateUsers' => $candidateUsers,
            'groups' => $groups,
            'search' => $search,
            'priority' => $priority,
            'statusFilter' => $status,
            'deadlineFilter' => $deadline,
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

        $list = DB::transaction(function () use ($validated): TaskList {
            $list = TaskList::create([
                'owner_id' => Auth::id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            ListMember::create([
                'list_id' => $list->id,
                'user_id' => Auth::id(),
                'role' => 'owner',
            ]);

            return $list;
        });

        return redirect()->route('lists.show', $list)->with('status', 'Project created.');
    }

    /** Undang anggota via dropdown (F-11). Hanya owner. */
    public function invite(Request $request, TaskList $list): RedirectResponse
    {
        $this->ensureOwner($list);

        $validated = $request->validate(
            ['user_id' => ['required', 'integer', 'exists:users,id']],
            [
                'user_id.required' => 'Pilih pengguna yang akan diundang.',
                'user_id.exists' => 'Pengguna tidak ditemukan.',
            ]
        );

        if (ListMember::where('list_id', $list->id)->where('user_id', $validated['user_id'])->exists()) {
            return back()->withErrors(['user_id' => 'Pengguna ini sudah menjadi anggota daftar.']);
        }

        ListMember::create([
            'list_id' => $list->id,
            'user_id' => $validated['user_id'],
            'role' => 'member',
        ]);

        return back()->with('status', 'Anggota berhasil ditambahkan.');
    }

    /** Hapus anggota dari daftar (F-12, AC-01). Hanya owner; baris owner tak bisa dihapus. */
    public function removeMember(Request $request, TaskList $list, User $user): RedirectResponse
    {
        $this->ensureOwner($list);

        $membership = ListMember::where('list_id', $list->id)->where('user_id', $user->id)->first();

        if ($membership === null) {
            abort(404);
        }

        if ($membership->role === 'owner') {
            return back()->withErrors(['member' => 'Keanggotaan pemilik tidak bisa dihapus. Hapus/arsipkan daftar untuk menutupnya.']);
        }

        $membership->delete();

        return back()->with('status', 'Anggota berhasil dihapus dari daftar.');
    }

    /** Keluar dari daftar (F-12, AC-02). Owner ditolak sampai kepemilikan diselesaikan eksplisit. */
    public function leave(Request $request, TaskList $list): RedirectResponse
    {
        $userId = (int) Auth::id();

        $membership = ListMember::where('list_id', $list->id)->where('user_id', $userId)->first();

        if ($membership === null) {
            abort(403);
        }

        if ($membership->role === 'owner' || (int) $list->owner_id === $userId) {
            return back()->withErrors(['member' => 'Pemilik tidak bisa keluar begitu saja. Alihkan kepemilikan atau hapus/arsipkan daftar terlebih dahulu.']);
        }

        $membership->delete();

        return redirect()->route('lists.index')->with('status', 'Anda keluar dari daftar.');
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

    public function archive(TaskList $list): RedirectResponse
    {
        $this->ensureOwner($list);

        $list->update(['is_archived' => ! $list->is_archived]);

        return redirect()->route('lists.index')->with(
            'status',
            $list->is_archived ? 'Project archived.' : 'Project restored.'
        );
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
