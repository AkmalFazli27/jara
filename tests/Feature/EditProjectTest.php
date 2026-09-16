<?php

namespace Tests\Feature;

use App\Models\ListMember;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_edit_a_project(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create([
            'owner_id' => $owner->id,
            'name' => 'Old Name',
            'description' => 'Old description',
        ]);

        $response = $this->actingAs($owner)->put(route('lists.update', $list), [
            'name' => 'Updated Project',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('lists', [
            'id' => $list->id,
            'name' => 'Updated Project',
            'description' => 'Updated description',
        ]);
    }

    public function test_member_cannot_edit_a_project(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $member = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Project']);
        ListMember::create(['list_id' => $list->id, 'user_id' => $member->id, 'role' => 'member']);

        $response = $this->actingAs($member)->put(route('lists.update', $list), [
            'name' => 'Changed by member',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('lists', [
            'id' => $list->id,
            'name' => 'Project',
        ]);
    }

    public function test_project_name_is_required_when_editing(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Project']);

        $response = $this->actingAs($owner)->put(route('lists.update', $list), [
            'name' => '',
            'description' => 'Updated description',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('lists', [
            'id' => $list->id,
            'name' => 'Project',
        ]);
    }
}
