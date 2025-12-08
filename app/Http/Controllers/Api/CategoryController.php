<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        try {
            $query = Category::with(['parent', 'children'])
                ->withCount('products');

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by parent
            if ($request->filled('parent_id')) {
                if ($request->parent_id === 'root') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $categories = $query->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:categories,id',
                'is_active' => 'boolean',
            ]);

            // Generate slug
            $validated['slug'] = Str::slug($validated['name']);

            // Check for slug uniqueness
            $slugCount = Category::where('slug', $validated['slug'])->count();
            if ($slugCount > 0) {
                $validated['slug'] = $validated['slug'] . '-' . time();
            }

            $category = Category::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category->load(['parent', 'children']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        try {
            $category->load(['parent', 'children', 'products'])
                ->loadCount('products');

            return response()->json([
                'success' => true,
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories')->ignore($category->id),
                ],
                'description' => 'nullable|string',
                'parent_id' => [
                    'nullable',
                    'exists:categories,id',
                    function ($attribute, $value, $fail) use ($category) {
                        // Prevent setting self as parent
                        if ($value == $category->id) {
                            $fail('A category cannot be its own parent.');
                        }
                        // Prevent circular reference
                        if ($value) {
                            $parent = Category::find($value);
                            if ($parent && $parent->parent_id == $category->id) {
                                $fail('Cannot create circular category reference.');
                            }
                        }
                    },
                ],
                'is_active' => 'boolean',
            ]);

            // Update slug if name changed
            if ($validated['name'] !== $category->name) {
                $validated['slug'] = Str::slug($validated['name']);

                // Check for slug uniqueness
                $slugCount = Category::where('slug', $validated['slug'])
                    ->where('id', '!=', $category->id)
                    ->count();
                if ($slugCount > 0) {
                    $validated['slug'] = $validated['slug'] . '-' . time();
                }
            }

            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category->load(['parent', 'children']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        try {
            // Check if category has products
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with associated products',
                ], 422);
            }

            // Check if category has children
            if ($category->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with subcategories',
                ], 422);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus(Category $category)
    {
        try {
            $category->update(['is_active' => !$category->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Category status updated successfully',
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all categories for dropdown (hierarchical).
     */
    public function getAllCategories()
    {
        try {
            $categories = Category::where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories: ' . $e->getMessage(),
            ], 500);
        }
    }
}
