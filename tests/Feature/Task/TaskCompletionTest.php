<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_task_can_be_marked_as_completed(): void
    {
        Carbon::setTestNow('2026-09-16 10:00:00');
        [$owner, $list, $task] = $this->createTask();

        $response = $this->actingAs($owner)
            ->from(route('lists.show', $list))
            ->patch(route('tasks.toggle', $task));

        $response->assertRedirect(route('lists.show', $list));
        $response->assertSessionHas('status', 'Tugas ditandai selesai.');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => true,
            'completed_at' => '2026-09-16 10:00:00',
        ]);
    }

    public function test_completed_task_can_be_reopened(): void
    {
        [$owner, $list, $task] = $this->createTask([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($owner)
            ->patch(route('tasks.toggle', $task));

        $response->assertSessionHas('status', 'Tugas dibuka kembali.');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    public function test_toggling_a_task_updates_list_progress(): void
    {
        [$owner, $list, $task] = $this->createTask();
        $list->tasks()->create([
            'title' => 'Sudah selesai',
            'priority' => 'low',
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('lists.show', $list))
            ->assertOk()
            ->assertSee('50%');

        $this->patch(route('tasks.toggle', $task));

        $this->get(route('lists.show', $list))
            ->assertOk()
            ->assertSee('100%');
    }

    public function test_outsider_cannot_toggle_a_task(): void
    {
        [, , $task] = $this->createTask();
        $outsider = User::factory()->create(['role' => 'user']);

        $this->actingAs($outsider)
            ->patch(route('tasks.toggle', $task))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    public function test_task_detail_is_scoped_to_the_accessed_list(): void
    {
        [$owner, $list, $task] = $this->createTask();
        $otherList = TaskList::create(['owner_id' => $owner->id, 'name' => 'Daftar Lain']);
        $otherTask = $otherList->tasks()->create([
            'title' => 'Tugas daftar lain',
            'priority' => 'high',
        ]);

        $this->actingAs($owner)
            ->get(route('lists.show', $list).'?focus='.$task->id)
            ->assertOk()
            ->assertSee('Simpan perubahan');

        $this->get(route('lists.show', $list).'?focus='.$otherTask->id)
            ->assertOk()
            ->assertDontSee('Tugas daftar lain')
            ->assertDontSee('Simpan perubahan');
    }

    /** @return array{User, TaskList, Task} */
    private function createTask(array $attributes = []): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Daftar Tugas']);
        $task = $list->tasks()->create(array_merge([
            'title' => 'Tugas awal',
            'priority' => 'medium',
        ], $attributes));

        return [$owner, $list, $task];
    }
}
