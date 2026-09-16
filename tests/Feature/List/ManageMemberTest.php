<?php

namespace Tests\Feature\List;

use App\Models\ListMember;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageMemberTest extends TestCase
{
    use RefreshDatabase;

    private function makeListWithMembers(): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $member = User::factory()->create(['role' => 'user', 'email' => 'anggota@jara.local']);

        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Proyek Bersama']);
        ListMember::create(['list_id' => $list->id, 'user_id' => $owner->id, 'role' => 'owner']);
        ListMember::create(['list_id' => $list->id, 'user_id' => $member->id, 'role' => 'member']);

        return [$owner, $member, $list];
    }

    public function test_owner_can_remove_member(): void
    {
        [$owner, $member, $list] = $this->makeListWithMembers();

        $response = $this->actingAs($owner)->delete(route('lists.members.destroy', [$list, $member]));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('list_members', [
            'list_id' => $list->id, 'user_id' => $member->id,
        ]);
    }

    public function test_removed_member_loses_access_to_list(): void
    {
        [$owner, $member, $list] = $this->makeListWithMembers();

        $this->actingAs($owner)->delete(route('lists.members.destroy', [$list, $member]));

        $this->actingAs($member)->get(route('lists.show', $list))->assertForbidden();
        $this->actingAs($member)->get(route('lists.index'))->assertDontSee('Proyek Bersama', false);
    }

    public function test_owner_cannot_remove_owner_membership(): void
    {
        [$owner, $member, $list] = $this->makeListWithMembers();

        $response = $this->actingAs($owner)->delete(route('lists.members.destroy', [$list, $owner]));

        $response->assertSessionHasErrors('member');
        $this->assertDatabaseHas('list_members', [
            'list_id' => $list->id, 'user_id' => $owner->id, 'role' => 'owner',
        ]);
    }

    public function test_non_owner_cannot_remove_members(): void
    {
        [$owner, $member, $list] = $this->makeListWithMembers();
        $other = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($member)->delete(route('lists.members.destroy', [$list, $other]));

        $response->assertForbidden();
    }

    public function test_owner_cannot_remove_member_of_another_list(): void
    {
        [$owner, $member, $list] = $this->makeListWithMembers();

        $otherOwner = User::factory()->create(['role' => 'user']);
        $otherList = TaskList::create(['owner_id' => $otherOwner->id, 'name' => 'Proyek Lain']);
        ListMember::create(['list_id' => $otherList->id, 'user_id' => $otherOwner->id, 'role' => 'owner']);

        // Bukan owner daftar target -> ditolak sebelum cek keanggotaan.
        $response = $this->actingAs($owner)->delete(route('lists.members.destroy', [$otherList, $member]));

        $response->assertForbidden();
        $this->assertDatabaseHas('list_members', [
            'list_id' => $list->id, 'user_id' => $member->id, 'role' => 'member',
        ]);

        // Owner menghapus user yang bukan anggota daftarnya sendiri -> 404.
        $stranger = User::factory()->create(['role' => 'user']);

        $this->actingAs($owner)->delete(route('lists.members.destroy', [$list, $stranger]))
            ->assertNotFound();
    }

    public function test_member_can_leave_list(): void
    {
        [$owner, $member, $list] = $this->makeListWithMembers();

        $response = $this->actingAs($member)->post(route('lists.leave', $list));

        $response->assertRedirect(route('lists.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('list_members', [
            'list_id' => $list->id, 'user_id' => $member->id,
        ]);
    }

    public function test_owner_cannot_leave_without_resolving_ownership(): void
    {
        [$owner, $member, $list] = $this->makeListWithMembers();

        $response = $this->actingAs($owner)->post(route('lists.leave', $list));

        $response->assertSessionHasErrors('member');
        $this->assertDatabaseHas('list_members', [
            'list_id' => $list->id, 'user_id' => $owner->id, 'role' => 'owner',
        ]);
    }

    public function test_non_member_cannot_leave(): void
    {
        [$owner, $member, $list] = $this->makeListWithMembers();
        $outsider = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($outsider)->post(route('lists.leave', $list));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        [$owner, $member, $list] = $this->makeListWithMembers();

        $this->delete(route('lists.members.destroy', [$list, $member]))->assertRedirect(route('login'));
        $this->post(route('lists.leave', $list))->assertRedirect(route('login'));
    }
}
