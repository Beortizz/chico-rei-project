<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_and_access_a_protected_route(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'secret-pass'])->assertOk();

        $this->getJson('/api/user')->assertOk()->assertJsonPath('email', $user->email);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'errada'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/logout')->assertNoContent();

        $this->assertGuest('web');
    }

    public function test_protected_route_requires_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}
