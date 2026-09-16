<?php

namespace Tests\Feature\Task;

use App\Models\ListMember;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_a_task(): void
    {
        [$owner, $list, $task] = $this->createTask();

        $response = $this->actingAs($owner)
            ->from(route('lists.show', $list).'?focus='.$task->id)
            ->put(route('tasks.update', $task), [
                'title' => 'Judul diperbarui',
                'description' => 'Deskripsi diperbarui',
                'priority' => 'high',
                'deadline' => '2026-10-15',
            ]);

        $response->assertRedirect(route('lists.show', $list).'?focus='.$task->id);
        $response->assertSessionHas('status', 'Tugas berhasil diperbarui.');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Judul diperbarui',
            'description' => 'Deskripsi diperbarui',
            'priority' => 'high',
        ]);
    }

    public function test_member_can_update_a_task_in_a_shared_list(): void
    {
        [$owner, $list, $task] = $this->createTask();
        $member = User::factory()->create(['role' => 'user']);
        ListMember::create([
            'list_id' => $list->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->put(route('tasks.update', $task), [
                'title' => 'Diubah anggota',
                'priority' => 'medium',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Diubah anggota',
        ]);
    }

    public function test_deadline_can_be_cleared(): void
    {
        [$owner, $list, $task] = $this->createTask(['deadline' => '2026-10-15']);

        $this->actingAs($owner)
            ->put(route('tasks.update', $task), [
                'title' => $task->title,
                'priority' => $task->priority,
                'deadline' => '',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deadline' => null,
        ]);
    }

    public function test_invalid_update_is_rejected(): void
    {
        [$owner, $list, $task] = $this->createTask();

        $response = $this->actingAs($owner)
            ->from(route('lists.show', $list).'?focus='.$task->id)
            ->put(route('tasks.update', $task), [
                'title' => '',
                'priority' => 'urgent',
            ]);

        $response->assertSessionHasErrors([
            'title' => 'Judul tugas wajib diisi.',
            'priority' => 'Prioritas yang dipilih tidak valid.',
        ], null, 'updateTask');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Tugas awal',
        ]);
    }

    public function test_owner_can_delete_a_task(): void
    {
        [$owner, $list, $task] = $this->createTask();

        $response = $this->actingAs($owner)->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('lists.show', $list));
        $response->assertSessionHas('status', 'Tugas berhasil dihapus.');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_outsider_cannot_update_or_delete_a_task(): void
    {
        [, , $task] = $this->createTask();
        $outsider = User::factory()->create(['role' => 'user']);

        $this->actingAs($outsider)
            ->put(route('tasks.update', $task), [
                'title' => 'Perubahan ilegal',
                'priority' => 'low',
            ])
            ->assertForbidden();

        $this->actingAs($outsider)
            ->delete(route('tasks.destroy', $task))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Tugas awal',
        ]);
    }

    public function test_task_detail_contains_delete_confirmation(): void
    {
        [$owner, $list, $task] = $this->createTask();

        $this->actingAs($owner)
            ->get(route('lists.show', $list).'?focus='.$task->id)
            ->assertOk()
            ->assertSee("confirm('Hapus tugas ini?')", false)
            ->assertSee('Simpan perubahan');
    }

    /** @return array{User, TaskList, Task} */
    private function createTask(array $attributes = []): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Daftar Tugas']);
        $task = $list->tasks()->create(array_merge([
            'title' => 'Tugas awal',
            'description' => 'Deskripsi awal',
            'priority' => 'medium',
        ], $attributes));

        return [$owner, $list, $task];
    }
}
