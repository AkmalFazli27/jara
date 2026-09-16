<?php

namespace Tests\Feature;

use App\Models\ListMember;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewSearchProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_owned_and_shared_projects_but_not_unrelated_projects(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);

        $owned = TaskList::create(['owner_id' => $user->id, 'name' => 'Owned Project']);
        $shared = TaskList::create(['owner_id' => $owner->id, 'name' => 'Shared Project']);
        TaskList::create(['owner_id' => $otherUser->id, 'name' => 'Hidden Project']);

        ListMember::create(['list_id' => $shared->id, 'user_id' => $user->id, 'role' => 'member']);

        $response = $this->actingAs($user)->get(route('lists.index'));

        $response->assertOk()
            ->assertSee('My Projects', false)
            ->assertSee('Owned Project', false)
            ->assertSee('Shared Projects', false)
            ->assertSee('Shared Project', false)
            ->assertDontSee('Hidden Project', false);

    }

    public function test_project_search_only_returns_matching_accessible_projects(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $matching = TaskList::create(['owner_id' => $user->id, 'name' => 'Website Redesign']);
        TaskList::create(['owner_id' => $user->id, 'name' => 'Mobile App']);

        $response = $this->actingAs($user)->get(route('lists.index', ['search' => 'Website']));

        $response->assertOk()
            ->assertSee($matching->name, false)
            ->assertViewHas('lists', function ($lists) {
                return $lists->count() === 1 && $lists->first()->name === 'Website Redesign';
            });
    }
}
