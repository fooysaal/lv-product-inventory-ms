<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with(['category', 'unit'])
                ->withCount(['stockIns', 'stockOuts']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by unit
            if ($request->filled('unit_id')) {
                $query->where('unit_id', $request->unit_id);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            // Filter by stock status
            if ($request->filled('stock_status')) {
                if ($request->stock_status === 'low_stock') {
                    $query->whereHas('stockBalances', function ($q) {
                        $q->whereColumn('available_quantity', '<', 'products.min_stock_level');
                    });
                } elseif ($request->stock_status === 'overstock') {
                    $query->whereNotNull('max_stock_level')
                        ->whereHas('stockBalances', function ($q) {
                            $q->whereColumn('quantity', '>', 'products.max_stock_level');
                        });
                }
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $products = $query->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:100|unique:products,sku',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'unit_id' => 'required|exists:units,id',
                'cost_price' => 'required|numeric|min:0',
                'selling_price' => 'required|numeric|min:0',
                'min_stock_level' => 'required|numeric|min:0',
                'max_stock_level' => 'nullable|numeric|min:0|gte:min_stock_level',
                'barcode' => 'nullable|string|max:255|unique:products,barcode',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'boolean',
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'unit']),
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
                'message' => 'Failed to create product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        try {
            $product->load(['category', 'unit', 'stockBalances.warehouse'])
                ->loadCount(['stockIns', 'stockOuts']);

            return response()->json([
                'success' => true,
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('products')->ignore($product->id),
                ],
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'unit_id' => 'required|exists:units,id',
                'cost_price' => 'required|numeric|min:0',
                'selling_price' => 'required|numeric|min:0',
                'min_stock_level' => 'required|numeric|min:0',
                'max_stock_level' => 'nullable|numeric|min:0|gte:min_stock_level',
                'barcode' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('products')->ignore($product->id),
                ],
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'boolean',
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load(['category', 'unit']),
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
                'message' => 'Failed to update product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        try {
            // Check if product has stock transactions
            if ($product->stockIns()->count() > 0 || $product->stockOuts()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with stock transaction history',
                ], 422);
            }

            // Delete image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle product status.
     */
    public function toggleStatus(Product $product)
    {
        try {
            $product->update(['is_active' => !$product->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully',
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get dropdown data for product form.
     */
    public function getFormData()
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id']);

            $units = Unit::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'short_name']);

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'units' => $units,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve form data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
