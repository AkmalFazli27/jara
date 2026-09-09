<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_create_user_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertStatus(200);
        $response->assertSee('Tambah Akun Pengguna Baru');
    }

    public function test_regular_user_cannot_access_create_user_form(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/users/create');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_a_new_user_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Pengguna Baru',
            'email' => 'pengguna.baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'pengguna.baru@example.com',
            'role' => 'user',
        ]);
    }

    public function test_admin_can_create_a_new_admin_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@example.com',
            'password' => 'adminpass123',
            'password_confirmation' => 'adminpass123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin.baru@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_email_must_be_unique_when_creating_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_all_fields_are_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/users', []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }
}
