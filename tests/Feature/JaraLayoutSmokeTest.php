<?php

namespace Tests\Feature;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JaraLayoutSmokeTest extends TestCase
{
    use RefreshDatabase;
    public function test_guest_sees_login(): void
    {
        $this->get('/login')->assertOk()->assertSee('Welcome back', false);
    }

    public function test_authenticated_empty_states_render(): void
    {
        $user = User::factory()->make(['id' => 999999]);

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/lists')->assertOk();
        $this->actingAs($user)->get('/kanban')->assertOk();
    }

    public function test_list_show_and_task_detail_render(): void
    {
        DB::beginTransaction();

        try {
            $user = User::factory()->create();
            $this->actingAs($user);

            $list = TaskList::create(['owner_id' => $user->id, 'name' => 'Smoke List']);
            \App\Models\ListMember::create(['list_id' => $list->id, 'user_id' => $user->id, 'role' => 'owner']);
            $task = $list->tasks()->create([
                'title' => 'Smoke Task',
                'priority' => 'high',
                'deadline' => now()->addDays(2),
            ]);

            $this->get(route('lists.show', $list))->assertOk()->assertSee('Smoke Task', false);
            $this->get(route('lists.show', $list).'?focus='.$task->id)->assertOk()->assertSee('Save changes', false);
            $this->get('/kanban')->assertOk()->assertSee('Smoke Task', false);
            $this->get('/dashboard')->assertOk()->assertSee('Smoke Task', false);
        } finally {
            DB::rollBack();
        }
    }
}
