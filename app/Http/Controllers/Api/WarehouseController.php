<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Warehouse::with('manager:id,name,email');

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            // Manager filter
            if ($request->filled('manager_id')) {
                $query->where('manager_id', $request->manager_id);
            }

            // City filter
            if ($request->filled('city')) {
                $query->where('city', $request->city);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $warehouses = $query->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => $warehouses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve warehouses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:20|unique:warehouses,code',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:50',
                'state' => 'nullable|string|max:50',
                'country' => 'required|string|max:50',
                'postal_code' => 'nullable|string|max:10',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'manager_id' => 'nullable|exists:users,id',
                'capacity' => 'nullable|numeric|min:0',
                'capacity_unit' => 'nullable|string|max:20',
                'is_active' => 'boolean'
            ]);

            $warehouse = Warehouse::create($validated);
            $warehouse->load('manager:id,name,email');

            return response()->json([
                'success' => true,
                'message' => 'Warehouse created successfully',
                'data' => $warehouse
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create warehouse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        try {
            $warehouse->load([
                'manager:id,name,email,phone',
                'stockBalances' => function ($query) {
                    $query->with('product:id,name,sku')
                        ->where('quantity', '>', 0)
                        ->orderBy('quantity', 'desc')
                        ->limit(10);
                }
            ]);

            // Add statistics
            $warehouse->statistics = [
                'total_stock_ins' => $warehouse->stockIns()->count(),
                'pending_stock_ins' => $warehouse->pendingStockIns()->count(),
                'total_stock_outs' => $warehouse->stockOuts()->count(),
                'pending_stock_outs' => $warehouse->pendingStockOuts()->count(),
                'total_products' => $warehouse->stockBalances()->where('quantity', '>', 0)->count(),
                'total_quantity' => $warehouse->stockBalances()->sum('quantity'),
            ];

            return response()->json([
                'success' => true,
                'data' => $warehouse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve warehouse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified warehouse.
     */
    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'code' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('warehouses', 'code')->ignore($warehouse->id)
                ],
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:50',
                'state' => 'nullable|string|max:50',
                'country' => 'required|string|max:50',
                'postal_code' => 'nullable|string|max:10',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'manager_id' => 'nullable|exists:users,id',
                'capacity' => 'nullable|numeric|min:0',
                'capacity_unit' => 'nullable|string|max:20',
                'is_active' => 'boolean'
            ]);

            $warehouse->update($validated);
            $warehouse->load('manager:id,name,email');

            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully',
                'data' => $warehouse
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update warehouse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        try {
            // Check if warehouse has any stock balances
            if ($warehouse->stockBalances()->where('quantity', '>', 0)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete warehouse with existing stock. Please transfer or clear all stock first.'
                ], 422);
            }

            $warehouse->delete();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete warehouse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle warehouse status.
     */
    public function toggleStatus(Warehouse $warehouse): JsonResponse
    {
        try {
            $warehouse->update(['is_active' => !$warehouse->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse status updated successfully',
                'data' => $warehouse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update warehouse status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available managers for warehouse assignment.
     */
    public function getManagers(): JsonResponse
    {
        try {
            $managers = User::where('is_active', true)
                ->whereHas('userType', function ($query) {
                    $query->where('name', 'Stock Manager');
                })
                ->select('id', 'name', 'email', 'phone')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $managers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve managers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get warehouse statistics.
     */
    public function statistics(Warehouse $warehouse): JsonResponse
    {
        try {
            $stats = [
                'stock_ins' => [
                    'total' => $warehouse->stockIns()->count(),
                    'pending' => $warehouse->pendingStockIns()->count(),
                    'approved' => $warehouse->stockIns()->where('status', 'approved')->count(),
                ],
                'stock_outs' => [
                    'total' => $warehouse->stockOuts()->count(),
                    'pending' => $warehouse->pendingStockOuts()->count(),
                    'approved' => $warehouse->stockOuts()->where('status', 'approved')->count(),
                ],
                'inventory' => [
                    'total_products' => $warehouse->stockBalances()->where('quantity', '>', 0)->count(),
                    'total_quantity' => $warehouse->stockBalances()->sum('quantity'),
                    'low_stock_products' => $warehouse->stockBalances()
                        ->whereColumn('quantity', '<=', DB::raw('(SELECT min_stock_level FROM products WHERE id = stock_balances.product_id)'))
                        ->count(),
                ],
                'capacity' => [
                    'total' => $warehouse->capacity,
                    'unit' => $warehouse->capacity_unit,
                    'utilization_percentage' => $warehouse->capacity > 0
                        ? round(($warehouse->stockBalances()->sum('quantity') / $warehouse->capacity) * 100, 2)
                        : 0
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
