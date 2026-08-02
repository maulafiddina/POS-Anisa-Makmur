<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageKasirTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_kasir_account(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->post('/kasir', [
            'name' => 'Budi Kasir',
            'email' => 'budi@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect('/kasir');
        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'name' => 'Budi Kasir',
            'role' => UserRole::Kasir->value,
        ]);
    }

    public function test_kasir_is_blocked_from_viewing_kasir_accounts(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/kasir');

        $response->assertForbidden();
    }

    public function test_kasir_is_blocked_from_creating_kasir_accounts(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->post('/kasir', [
            'name' => 'Budi Kasir',
            'email' => 'budi@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'budi@example.com',
        ]);
    }
}
