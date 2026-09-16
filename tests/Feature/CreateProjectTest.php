<?php

namespace Tests\Feature;

use App\Models\ListMember;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_project_with_owner_membership(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('lists.store'), [
            'name' => 'Website Redesign',
            'description' => 'Project description',
        ]);

        $list = TaskList::where('name', 'Website Redesign')->first();

        $this->assertNotNull($list);
        $response->assertRedirect(route('lists.show', $list));
        $this->assertDatabaseHas('lists', [
            'id' => $list->id,
            'owner_id' => $user->id,
            'name' => 'Website Redesign',
            'description' => 'Project description',
        ]);
        $this->assertDatabaseHas('list_members', [
            'list_id' => $list->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_project_name_is_required(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('lists.store'), [
            'description' => 'Project description',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('lists', 0);
        $this->assertDatabaseCount('list_members', 0);
    }
}
