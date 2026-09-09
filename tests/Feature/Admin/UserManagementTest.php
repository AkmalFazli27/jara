<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(5)->create(['role' => 'user']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Daftar Pengguna Sistem');
    }

    public function test_regular_user_cannot_view_user_list(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_search_user_by_name_or_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'role' => 'user',
        ]);
        User::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'role' => 'user',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users?search=Budi');

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Siti Aminah');
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Utama']);
        $user = User::factory()->create(['role' => 'user', 'name' => 'User Reguler']);

        $response = $this->actingAs($admin)->get('/admin/users?role=user');

        $response->assertStatus(200);
        $response->assertSee('User Reguler');
    }
}
