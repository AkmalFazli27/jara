<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_other_user_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'user', 'email' => 'hapus.saya@example.com']);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $target));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_deleted_account_can_no_longer_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create([
            'role' => 'user',
            'email' => 'korban@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $target));

        $this->post('/logout');
        $login = $this->post('/login', [
            'email' => 'korban@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $login->assertSessionHasErrors(['email']);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_regular_user_cannot_delete_accounts(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $target = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->delete(route('admin.users.destroy', $target));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_guest_is_redirected_to_login_when_deleting(): void
    {
        $target = User::factory()->create(['role' => 'user']);

        $response = $this->delete(route('admin.users.destroy', $target));

        $response->assertRedirect(route('login'));
    }
}