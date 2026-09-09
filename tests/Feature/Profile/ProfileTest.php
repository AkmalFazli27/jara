<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Profil');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Nama Baru User',
            'email' => 'namabaru@example.com',
        ]);

        $response->assertRedirect('/profile');
        $user->refresh();

        $this->assertSame('Nama Baru User', $user->name);
        $this->assertSame('namabaru@example.com', $user->email);
    }

    public function test_email_uniqueness_validation_prevents_duplicate_emails(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'User Updated',
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertSame('user@example.com', $user->fresh()->email);
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password-lama'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'password-lama',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $response->assertRedirect('/profile');
        $this->assertTrue(Hash::check('password-baru-123', $user->fresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password-asli'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'password-salah',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }
}
