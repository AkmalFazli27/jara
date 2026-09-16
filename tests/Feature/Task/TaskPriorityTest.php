<?php

namespace Tests\Feature\Task;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_can_be_sorted_by_priority_and_deadline(): void
    {
        [$owner, $list] = $this->createList();

        $list->tasks()->createMany([
            ['title' => 'Prioritas rendah', 'priority' => 'low', 'deadline' => '2026-09-17'],
            ['title' => 'Prioritas tinggi nanti', 'priority' => 'high', 'deadline' => '2026-09-20'],
            ['title' => 'Prioritas sedang', 'priority' => 'medium', 'deadline' => '2026-09-16'],
            ['title' => 'Prioritas tinggi dulu', 'priority' => 'high', 'deadline' => '2026-09-18'],
        ]);

        $this->actingAs($owner)
            ->get(route('lists.show', $list).'?sort=priority')
            ->assertOk()
            ->assertSeeInOrder([
                'Prioritas tinggi dulu',
                'Prioritas tinggi nanti',
                'Prioritas sedang',
                'Prioritas rendah',
            ]);
    }

    public function test_priority_labels_are_displayed_in_indonesian(): void
    {
        [$owner, $list] = $this->createList();
        $list->tasks()->create(['title' => 'Tugas penting', 'priority' => 'high']);

        $this->actingAs($owner)
            ->get(route('lists.show', $list))
            ->assertOk()
            ->assertSee('Tinggi')
            ->assertSee('Sedang')
            ->assertSee('Rendah');
    }

    /** @return array{User, TaskList} */
    private function createList(): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Daftar Prioritas']);

        return [$owner, $list];
    }
}
