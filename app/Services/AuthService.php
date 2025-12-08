<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Load the user type relationship
        $user->load('userType');

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate user and generate token
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = User::with('userType')->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing tokens if not remember me
        if (!($credentials['remember'] ?? false)) {
            $user->tokens()->delete();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user (revoke current token)
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        // Revoke the current access token using safe navigation operator
        // @phpstan-ignore-next-line - IDE may not recognize delete() method on PersonalAccessToken
        $user->currentAccessToken()?->delete();
    }

    /**
     * Logout from all devices (revoke all tokens)
     *
     * @param User $user
     * @return void
     */
    public function logoutFromAllDevices(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Get authenticated user profile
     *
     * @param User $user
     * @return User
     */
    public function getProfile(User $user): User
    {
        return $user->load(['userType', 'tokens']);
    }
}
