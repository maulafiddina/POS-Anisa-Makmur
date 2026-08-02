<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_log_in_with_valid_credentials(): void
    {
        $owner = User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'owner@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_kasir_can_log_in_with_valid_credentials(): void
    {
        $kasir = User::factory()->kasir()->create([
            'email' => 'kasir@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'kasir@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($kasir);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'owner@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
