<?php

namespace Tests\Feature\Task;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskFilteringAndProgressTest extends TestCase
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

    public function test_status_filter_displays_matching_tasks(): void
    {
        [$owner, $list] = $this->createList();
        $list->tasks()->createMany([
            ['title' => 'Belum dikerjakan', 'priority' => 'medium'],
            [
                'title' => 'Sudah dikerjakan',
                'priority' => 'medium',
                'is_completed' => true,
                'completed_at' => now(),
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('lists.show', $list).'?status=todo')
            ->assertOk()
            ->assertSee('Belum dikerjakan')
            ->assertDontSee('Sudah dikerjakan');

        $this->get(route('lists.show', $list).'?status=done')
            ->assertOk()
            ->assertSee('Sudah dikerjakan')
            ->assertDontSee('Belum dikerjakan');
    }

    public function test_search_and_filters_work_in_combination(): void
    {
        [$owner, $list] = $this->createList();
        $list->tasks()->createMany([
            ['title' => 'Laporan akhir target', 'priority' => 'high', 'deadline' => '2026-09-15'],
            ['title' => 'Laporan prioritas sedang', 'priority' => 'medium', 'deadline' => '2026-09-15'],
            ['title' => 'Laporan tenggat depan', 'priority' => 'high', 'deadline' => '2026-09-20'],
            [
                'title' => 'Laporan yang selesai',
                'priority' => 'high',
                'deadline' => '2026-09-15',
                'is_completed' => true,
                'completed_at' => now(),
            ],
            ['title' => 'Presentasi target', 'priority' => 'high', 'deadline' => '2026-09-15'],
        ]);

        $query = http_build_query([
            'search' => 'Laporan',
            'priority' => 'high',
            'status' => 'todo',
            'deadline' => 'overdue',
            'sort' => 'priority',
            'group' => 'status',
        ]);

        $this->actingAs($owner)
            ->get(route('lists.show', $list).'?'.$query)
            ->assertOk()
            ->assertSee('Laporan akhir target')
            ->assertDontSee('Laporan prioritas sedang')
            ->assertDontSee('Laporan tenggat depan')
            ->assertDontSee('Laporan yang selesai')
            ->assertDontSee('Presentasi target');
    }

    public function test_task_detail_link_preserves_active_filters(): void
    {
        [$owner, $list] = $this->createList();
        $list->tasks()->create([
            'title' => 'Laporan aktif',
            'priority' => 'high',
            'deadline' => '2026-09-15',
        ]);
        $query = http_build_query([
            'search' => 'Laporan',
            'priority' => 'high',
            'status' => 'todo',
            'deadline' => 'overdue',
            'sort' => 'priority',
            'group' => 'status',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('lists.show', $list).'?'.$query)
            ->assertOk();

        $response->assertSee('search=Laporan', false)
            ->assertSee('priority=high', false)
            ->assertSee('status=todo', false)
            ->assertSee('deadline=overdue', false)
            ->assertSee('focus=', false);
    }

    public function test_progress_uses_all_tasks_instead_of_filtered_results(): void
    {
        [$owner, $list] = $this->createList();
        $list->tasks()->createMany([
            ['title' => 'Tugas aktif', 'priority' => 'high'],
            [
                'title' => 'Tugas selesai',
                'priority' => 'low',
                'is_completed' => true,
                'completed_at' => now(),
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('lists.show', $list).'?status=done')
            ->assertOk()
            ->assertSee('50%')
            ->assertSee('Tugas selesai')
            ->assertDontSee('Tugas aktif');
    }

    public function test_progress_is_zero_for_empty_list_and_full_when_all_tasks_are_done(): void
    {
        [$owner, $list] = $this->createList();

        $this->actingAs($owner)
            ->get(route('lists.show', $list))
            ->assertOk()
            ->assertSee('0%');

        $list->tasks()->create([
            'title' => 'Selesai',
            'priority' => 'medium',
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $this->get(route('lists.show', $list))
            ->assertOk()
            ->assertSee('100%');
    }

    /** @return array{User, TaskList} */
    private function createList(): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Daftar Filter']);

        return [$owner, $list];
    }
}
