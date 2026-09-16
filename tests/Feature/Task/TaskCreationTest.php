<?php

namespace Tests\Feature\Task;

use App\Models\ListMember;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_task_in_their_list(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Proyek Skripsi']);

        $response = $this->actingAs($owner)
            ->from(route('lists.show', $list))
            ->post(route('tasks.store', $list), [
                'title' => 'Menyusun proposal',
                'description' => 'Menyelesaikan bab pendahuluan.',
                'priority' => 'high',
                'deadline' => '2026-09-30',
            ]);

        $response->assertRedirect(route('lists.show', $list));
        $response->assertSessionHas('status', 'Tugas berhasil dibuat.');
        $this->assertDatabaseHas('tasks', [
            'list_id' => $list->id,
            'title' => 'Menyusun proposal',
            'description' => 'Menyelesaikan bab pendahuluan.',
            'priority' => 'high',
        ]);

        $this->get(route('lists.show', $list))
            ->assertOk()
            ->assertSee('Tambah tugas');
    }

    public function test_member_can_create_a_task_in_a_shared_list(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $member = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Proyek Bersama']);
        ListMember::create([
            'list_id' => $list->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($member)
            ->from(route('lists.show', $list))
            ->post(route('tasks.store', $list), [
                'title' => 'Tugas anggota',
                'priority' => 'medium',
            ]);

        $response->assertRedirect(route('lists.show', $list));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', 'Tugas berhasil dibuat.');

        $this->assertDatabaseHas('tasks', [
            'list_id' => $list->id,
            'title' => 'Tugas anggota',
        ]);
    }

    public function test_user_cannot_create_a_task_in_an_inaccessible_list(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $outsider = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Daftar Privat']);

        $this->actingAs($outsider)
            ->post(route('tasks.store', $list), [
                'title' => 'Tugas ilegal',
                'priority' => 'medium',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('tasks', ['title' => 'Tugas ilegal']);
    }

    public function test_title_is_required_when_creating_a_task(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Proyek Skripsi']);

        $response = $this->actingAs($owner)
            ->from(route('lists.show', $list))
            ->post(route('tasks.store', $list), [
                'title' => '',
                'priority' => 'medium',
            ]);

        $response->assertRedirect(route('lists.show', $list));
        $response->assertSessionHasErrors([
            'title' => 'Judul tugas wajib diisi.',
        ]);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_invalid_priority_is_rejected_and_input_is_preserved(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $list = TaskList::create(['owner_id' => $owner->id, 'name' => 'Proyek Skripsi']);

        $response = $this->actingAs($owner)
            ->from(route('lists.show', $list))
            ->post(route('tasks.store', $list), [
                'title' => 'Tugas valid',
                'description' => 'Input tetap tersedia.',
                'priority' => 'urgent',
            ]);

        $response->assertRedirect(route('lists.show', $list));
        $response->assertSessionHasErrors([
            'priority' => 'Prioritas yang dipilih tidak valid.',
        ]);
        $response->assertSessionHasInput('title', 'Tugas valid');
        $response->assertSessionHasInput('description', 'Input tetap tersedia.');
        $this->assertDatabaseCount('tasks', 0);
    }
}
