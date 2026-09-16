<?php

namespace Tests\Feature;

use App\Models\ListMember;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveDeleteProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_archive_and_restore_a_project(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Archived Project']);

        $archiveResponse = $this->actingAs($owner)->patch(route('lists.archive', $list));

        $archiveResponse->assertRedirect(route('lists.index'));
        $this->assertDatabaseHas('lists', ['id' => $list->id, 'is_archived' => 1]);
        $this->actingAs($owner)->get(route('lists.index'))->assertViewHas('lists', fn ($lists) => $lists->isEmpty());

        $restoreResponse = $this->actingAs($owner)->patch(route('lists.archive', $list));

        $restoreResponse->assertRedirect(route('lists.index'));
        $this->assertDatabaseHas('lists', ['id' => $list->id, 'is_archived' => 0]);
    }

    public function test_member_cannot_archive_a_project(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $member = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Project']);
        ListMember::create(['list_id' => $list->id, 'user_id' => $member->id, 'role' => 'member']);

        $response = $this->actingAs($member)->patch(route('lists.archive', $list));

        $response->assertForbidden();
        $this->assertDatabaseHas('lists', ['id' => $list->id, 'is_archived' => 0]);
    }

    public function test_owner_can_delete_a_project_and_cascaded_data(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Deleted Project']);
        $membership = ListMember::create(['list_id' => $list->id, 'user_id' => $owner->id, 'role' => 'owner']);
        $task = Task::create(['list_id' => $list->id, 'title' => 'Task to delete']);

        $response = $this->actingAs($owner)->delete(route('lists.destroy', $list));

        $response->assertRedirect(route('lists.index'));
        $this->assertDatabaseMissing('lists', ['id' => $list->id]);
        $this->assertDatabaseMissing('list_members', ['id' => $membership->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
