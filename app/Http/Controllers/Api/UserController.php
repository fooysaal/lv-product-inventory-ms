<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Default password for new users
     */
    const DEFAULT_PASSWORD = 'password';

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with('userType')->withCount(['createdStockIns', 'createdStockOuts']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Filter by user type
            if ($request->filled('user_type_id')) {
                $query->where('user_type_id', $request->user_type_id);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully.',
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all user types for dropdown.
     */
    public function getUserTypes(): JsonResponse
    {
        try {
            $userTypes = UserType::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            return response()->json([
                'success' => true,
                'message' => 'User types retrieved successfully.',
                'data' => $userTypes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user types: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user types.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_type_id' => 'required|exists:user_types,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'is_active' => 'boolean',
            ]);

            // Use default password
            $validated['password'] = Hash::make(self::DEFAULT_PASSWORD);

            $user = User::create($validated);
            $user->load('userType');

            return response()->json([
                'success' => true,
                'message' => 'User created successfully with default password: ' . self::DEFAULT_PASSWORD,
                'data' => $user,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        try {
            $user->load(['userType', 'managedWarehouses'])
                ->loadCount(['createdStockIns', 'createdStockOuts']);

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully.',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_type_id' => 'required|exists:user_types,id',
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
                'phone' => 'nullable|string|max:20',
                'is_active' => 'boolean',
            ]);

            $user->update($validated);
            $user->load('userType');

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data' => $user,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset user password to default.
     */
    public function resetPassword(User $user): JsonResponse
    {
        try {
            $user->update([
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset to default: ' . self::DEFAULT_PASSWORD,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Error resetting password: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            // Check if user has created any stock transactions
            $hasStockIns = $user->createdStockIns()->count() > 0;
            $hasStockOuts = $user->createdStockOuts()->count() > 0;

            if ($hasStockIns || $hasStockOuts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user with associated stock transactions.',
                ], 422);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user): JsonResponse
    {
        try {
            $user->update([
                'is_active' => !$user->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully.',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling user status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
