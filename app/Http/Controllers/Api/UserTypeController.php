<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserTypeController extends Controller
{
    /**
     * Display a listing of user types.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = UserType::query()->withCount('users');

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
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
            $userTypes = $query->paginate($perPage);

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
     * Store a newly created user type.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:user_types,name',
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
                'is_active' => 'boolean',
            ]);

            // Generate slug from name
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure slug is unique
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (UserType::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            $userType = UserType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'User type created successfully.',
                'data' => $userType->load('users'),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating user type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user type.
     */
    public function show(UserType $userType): JsonResponse
    {
        try {
            $userType->loadCount('users');

            return response()->json([
                'success' => true,
                'message' => 'User type retrieved successfully.',
                'data' => $userType,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified user type.
     */
    public function update(Request $request, UserType $userType): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('user_types', 'name')->ignore($userType->id),
                ],
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
                'is_active' => 'boolean',
            ]);

            // Update slug if name changed
            if (isset($validated['name']) && $validated['name'] !== $userType->name) {
                $validated['slug'] = Str::slug($validated['name']);

                // Ensure slug is unique
                $originalSlug = $validated['slug'];
                $counter = 1;
                while (UserType::where('slug', $validated['slug'])->where('id', '!=', $userType->id)->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            $userType->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User type updated successfully.',
                'data' => $userType->load('users'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating user type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user type.
     */
    public function destroy(UserType $userType): JsonResponse
    {
        try {
            // Check if user type has associated users
            if ($userType->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user type with associated users.',
                ], 422);
            }

            $userType->delete();

            return response()->json([
                'success' => true,
                'message' => 'User type deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting user type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle user type status.
     */
    public function toggleStatus(UserType $userType): JsonResponse
    {
        try {
            $userType->update([
                'is_active' => !$userType->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User type status updated successfully.',
                'data' => $userType,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling user type status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user type status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
