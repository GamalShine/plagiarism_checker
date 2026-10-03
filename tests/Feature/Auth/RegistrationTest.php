<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
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
        $response->assertRedirect(route('user.dashboard', absolute: false));
    }

    public function test_new_users_with_a_selected_package_are_redirected_to_checkout(): void
    {
        $response = $this->withSession(['selected_package' => 'hemat-3'])
            ->post('/register', [
                'email' => 'package-user@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'package-user@example.com',
            'pending_package_key' => 'hemat-3',
        ]);
        $response->assertRedirect(route('user.payment.package', 'hemat-3', absolute: false));
        $this->get(route('user.payment.package', 'hemat-3', absolute: false))->assertOk();
    }
}
