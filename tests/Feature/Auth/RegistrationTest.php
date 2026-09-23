<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => Role::Customer->value,
        ]);
    }

    public function test_registration_shows_clear_errors_when_fields_are_missing(): void
    {
        $response = $this->followingRedirects()
            ->from('/register')
            ->post('/register', [
                'name' => '',
                'email' => '',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertOk();
        $response->assertSee('Pendaftaran gagal', false);
        $response->assertSee('nama wajib diisi', false);
        $this->assertGuest();
    }

    public function test_registration_rejects_duplicate_email_with_clear_message(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Another',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $message = session('errors')->first('email');
        $this->assertStringContainsString('sudah digunakan', (string) $message);
        $this->assertGuest();
    }

    public function test_registration_rejects_mismatched_password_with_clear_message(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test',
            'email' => 'mismatch@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
        $message = session('errors')->first('password');
        $this->assertStringContainsString('Konfirmasi', (string) $message);
        $this->assertGuest();
    }
}
