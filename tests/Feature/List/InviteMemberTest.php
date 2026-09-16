<?php

namespace Tests\Feature\List;

use App\Models\ListMember;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteMemberTest extends TestCase
{
    use RefreshDatabase;

    private function makeListWithOwner(): array
    {
        $owner = User::factory()->create(['role' => 'user']);

        $list = TaskList::create([
            'owner_id' => $owner->id,
            'name' => 'Proyek Bersama',
            'description' => null,
        ]);
        ListMember::create(['list_id' => $list->id, 'user_id' => $owner->id, 'role' => 'owner']);

        return [$owner, $list];
    }

    public function test_owner_can_invite_user_from_dropdown(): void
    {
        [$owner, $list] = $this->makeListWithOwner();
        $friend = User::factory()->create(['role' => 'user', 'email' => 'teman@jara.local']);

        $response = $this->actingAs($owner)->post(route('lists.members.store', $list), [
            'user_id' => $friend->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('list_members', [
            'list_id' => $list->id,
            'user_id' => $friend->id,
            'role' => 'member',
        ]);
    }

    public function test_invited_user_sees_list_as_shared(): void
    {
        [$owner, $list] = $this->makeListWithOwner();
        $friend = User::factory()->create(['role' => 'user', 'email' => 'teman@jara.local']);

        $this->actingAs($owner)->post(route('lists.members.store', $list), [
            'user_id' => $friend->id,
        ]);

        $response = $this->actingAs($friend)->get(route('lists.index'));

        $response->assertOk();
        $response->assertSee('Proyek Bersama');
    }

    public function test_dropdown_excludes_existing_members(): void
    {
        [$owner, $list] = $this->makeListWithOwner();
        $member = User::factory()->create(['role' => 'user', 'name' => 'Anggota Lama', 'email' => 'lama@jara.local']);
        ListMember::create(['list_id' => $list->id, 'user_id' => $member->id, 'role' => 'member']);
        $outsider = User::factory()->create(['role' => 'user', 'name' => 'Calon Baru', 'email' => 'baru@jara.local']);

        $response = $this->actingAs($owner)->get(route('lists.show', $list));

        $response->assertOk();
        // Anggota lama tetap tampil di daftar anggota, tapi tidak di dropdown.
        $response->assertSee('lama@jara.local', false);
        $response->assertDontSee('<option value="'.$member->id.'"', false);
        $response->assertSee('baru@jara.local', false);
    }

    public function test_existing_member_cannot_be_invited_twice(): void
    {
        [$owner, $list] = $this->makeListWithOwner();
        $friend = User::factory()->create(['role' => 'user', 'email' => 'teman@jara.local']);
        ListMember::create(['list_id' => $list->id, 'user_id' => $friend->id, 'role' => 'member']);

        $response = $this->actingAs($owner)->post(route('lists.members.store', $list), [
            'user_id' => $friend->id,
        ]);

        $response->assertSessionHasErrors('user_id');
        $this->assertEquals(1, ListMember::where('list_id', $list->id)->where('user_id', $friend->id)->count());
    }

    public function test_non_owner_cannot_invite(): void
    {
        [$owner, $list] = $this->makeListWithOwner();
        $member = User::factory()->create(['role' => 'user']);
        ListMember::create(['list_id' => $list->id, 'user_id' => $member->id, 'role' => 'member']);
        $target = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($member)->post(route('lists.members.store', $list), [
            'user_id' => $target->id,
        ]);

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'X']);

        $response = $this->post(route('lists.members.store', $list), [
            'user_id' => $owner->id,
        ]);

        $response->assertRedirect(route('login'));
    }
}
