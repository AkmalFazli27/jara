<?php

namespace App\Http\Controllers;

use App\Models\ListMember;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function store(Request $request, TaskList $list): RedirectResponse
    {
        $this->ensureAccess($list);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'deadline' => ['nullable', 'date'],
        ], [
            'title.required' => 'Judul tugas wajib diisi.',
            'title.max' => 'Judul tugas maksimal 255 karakter.',
            'description.max' => 'Deskripsi tugas maksimal 2000 karakter.',
            'priority.in' => 'Prioritas yang dipilih tidak valid.',
            'deadline.date' => 'Format deadline tidak valid.',
        ]);

        $list->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'deadline' => $validated['deadline'] ?? null,
        ]);

        return back()->with('status', 'Tugas berhasil dibuat.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->ensureAccess($task->list);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'in' => ['low', 'medium', 'high']],
            'deadline' => ['nullable', 'date'],
        ]);

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? $task->priority,
            'deadline' => $validated['deadline'] ?? $task->deadline,
        ]);

        return back()->with('status', 'Task updated.');
    }

    /** Tandai selesai / belum (F-15): isi completed_at saat selesai. */
    public function toggle(Task $task): RedirectResponse
    {
        $this->ensureAccess($task->list);

        $done = ! $task->is_completed;

        $task->update([
            'is_completed' => $done,
            'completed_at' => $done ? now() : null,
        ]);

        return back()->with('status', $done ? 'Task completed.' : 'Task reopened.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->ensureAccess($task->list);

        $listId = $task->list_id;
        $task->delete();

        return redirect()->route('lists.show', $listId)->with('status', 'Task deleted.');
    }

    private function ensureAccess(TaskList $list): void
    {
        $userId = (int) Auth::id();

        if ($list->owner_id !== $userId
            && ! ListMember::where('list_id', $list->id)->where('user_id', $userId)->exists()) {
            abort(403);
        }
    }
}
