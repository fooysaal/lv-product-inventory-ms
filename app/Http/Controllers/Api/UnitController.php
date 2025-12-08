<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(Request $request)
    {
        try {
            $query = Unit::withCount('products');

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $units = $query->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => $units,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve units: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created unit.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:units,name',
                'short_name' => 'required|string|max:50|unique:units,short_name',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $unit = Unit::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully',
                'data' => $unit,
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
                'message' => 'Failed to create unit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified unit.
     */
    public function show(Unit $unit)
    {
        try {
            $unit->loadCount('products');

            return response()->json([
                'success' => true,
                'data' => $unit,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve unit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified unit.
     */
    public function update(Request $request, Unit $unit)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('units')->ignore($unit->id),
                ],
                'short_name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('units')->ignore($unit->id),
                ],
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $unit->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Unit updated successfully',
                'data' => $unit,
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
                'message' => 'Failed to update unit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified unit.
     */
    public function destroy(Unit $unit)
    {
        try {
            // Check if unit has products
            if ($unit->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete unit with associated products',
                ], 422);
            }

            $unit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unit deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete unit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle unit status.
     */
    public function toggleStatus(Unit $unit)
    {
        try {
            $unit->update(['is_active' => !$unit->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Unit status updated successfully',
                'data' => $unit,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all active units for dropdown.
     */
    public function getAllUnits()
    {
        try {
            $units = Unit::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'short_name']);

            return response()->json([
                'success' => true,
                'data' => $units,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve units: ' . $e->getMessage(),
            ], 500);
        }
    }
}
