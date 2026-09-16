<?php

namespace Tests\Feature\Task;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskDeadlineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-09-16 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_invalid_deadline_is_rejected_with_a_clear_message(): void
    {
        [$owner, $list] = $this->createList();

        $response = $this->actingAs($owner)
            ->post(route('tasks.store', $list), [
                'title' => 'Tugas dengan tanggal salah',
                'priority' => 'medium',
                'deadline' => 'besok pagi',
            ]);

        $response->assertSessionHasErrors([
            'deadline' => 'Format deadline harus berupa tanggal yang valid.',
        ]);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_overdue_state_uses_calendar_date_and_ignores_completed_tasks(): void
    {
        [, $list] = $this->createList();
        $overdue = $list->tasks()->create([
            'title' => 'Terlambat',
            'priority' => 'high',
            'deadline' => '2026-09-15',
        ]);
        $today = $list->tasks()->create([
            'title' => 'Hari ini',
            'priority' => 'medium',
            'deadline' => '2026-09-16',
        ]);
        $completed = $list->tasks()->create([
            'title' => 'Selesai terlambat',
            'priority' => 'low',
            'deadline' => '2026-09-15',
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $this->assertTrue($overdue->is_overdue);
        $this->assertFalse($today->is_overdue);
        $this->assertFalse($completed->is_overdue);
    }

    public function test_deadline_calendar_uses_western_indonesian_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-15 18:00:00', 'UTC'));
        [$owner, $list] = $this->createList();
        $task = $list->tasks()->create([
            'title' => 'Lewat tengah malam WIB',
            'priority' => 'high',
            'deadline' => '2026-09-15',
        ]);

        $this->assertTrue($task->is_overdue);
        $this->actingAs($owner)
            ->get(route('lists.show', $list).'?deadline=overdue')
            ->assertOk()
            ->assertSee('Lewat tengah malam WIB');
    }

    public function test_today_filter_only_displays_tasks_due_today(): void
    {
        [$owner, $list] = $this->createList();
        $this->createDeadlineTasks($list);

        $this->actingAs($owner)
            ->get(route('lists.show', $list).'?deadline=today')
            ->assertOk()
            ->assertSee('Tugas hari ini')
            ->assertDontSee('Tugas kemarin')
            ->assertDontSee('Tugas besok');
    }

    public function test_overdue_filter_excludes_today_future_and_completed_tasks(): void
    {
        [$owner, $list] = $this->createList();
        $this->createDeadlineTasks($list);

        $this->actingAs($owner)
            ->get(route('lists.show', $list).'?deadline=overdue')
            ->assertOk()
            ->assertSee('Tugas kemarin')
            ->assertSee('Terlambat')
            ->assertDontSee('Tugas hari ini')
            ->assertDontSee('Tugas besok')
            ->assertDontSee('Tugas lama selesai');
    }

    private function createDeadlineTasks(TaskList $list): void
    {
        $list->tasks()->createMany([
            ['title' => 'Tugas kemarin', 'priority' => 'high', 'deadline' => '2026-09-15'],
            ['title' => 'Tugas hari ini', 'priority' => 'medium', 'deadline' => '2026-09-16'],
            ['title' => 'Tugas besok', 'priority' => 'low', 'deadline' => '2026-09-17'],
            [
                'title' => 'Tugas lama selesai',
                'priority' => 'high',
                'deadline' => '2026-09-14',
                'is_completed' => true,
                'completed_at' => now(),
            ],
        ]);
    }

    /** @return array{User, TaskList} */
    private function createList(): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Daftar Deadline']);

        return [$owner, $list];
    }
}
