<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test registration validation fails with invalid data
     */
    public function test_registration_validation_fails(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'email', 'password'],
            ]);
    }

    /**
     * Test user can login with correct credentials
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('SecurePass123!@#'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'SecurePass123!@#',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);
    }

    /**
     * Test login fails with incorrect credentials
     */
    public function test_login_fails_with_incorrect_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('SecurePass123!@#'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);
    }

    /**
     * Test authenticated user can get profile
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile retrieved successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                ],
            ]);
    }

    /**
     * Test unauthenticated user cannot get profile
     */
    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/auth/profile');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test user can logout
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        // Token should be revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * Test user can logout from all devices
     */
    public function test_user_can_logout_from_all_devices(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('device-1')->plainTextToken;
        $token2 = $user->createToken('device-2')->plainTextToken;

        // Verify tokens exist
        $this->assertDatabaseCount('personal_access_tokens', 2);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->postJson('/api/auth/logout-all');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out from all devices successfully.',
            ]);

        // All tokens should be revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }
}
