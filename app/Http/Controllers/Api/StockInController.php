<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockIn;
use App\Models\Product;
use App\Models\Warehouse;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class StockInController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of stock in records.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $status = $request->get('status', '');
            $warehouseId = $request->get('warehouse_id', '');
            $productId = $request->get('product_id', '');
            $dateFrom = $request->get('date_from', '');
            $dateTo = $request->get('date_to', '');

            $query = StockIn::query()
                ->select('reference_number', 'warehouse_id', 'supplier_name', 'supplier_invoice', 'received_date', 'notes', 'status', 'created_by', 'approved_by', 'approved_at', 'created_at', 'updated_at')
                ->selectRaw('MIN(id) as id')
                ->selectRaw('COUNT(*) as product_count')
                ->selectRaw('SUM(total_amount) as total_amount')
                ->with(['warehouse', 'creator', 'approver'])
                ->groupBy('reference_number', 'warehouse_id', 'supplier_name', 'supplier_invoice', 'received_date', 'notes', 'status', 'created_by', 'approved_by', 'approved_at', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc');

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply warehouse filter
            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            // Apply product filter
            if ($productId) {
                $query->whereIn('reference_number', function ($subQuery) use ($productId) {
                    $subQuery->select('reference_number')
                        ->from('stock_ins')
                        ->where('product_id', $productId);
                });
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->whereDate('received_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('received_date', '<=', $dateTo);
            }

            $stockIns = $query->paginate($perPage);

            return $this->successResponse($stockIns, 'Stock in records retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get form data for creating stock in.
     */
    public function getFormData(): JsonResponse
    {
        try {
            $products = Product::where('is_active', true)
                ->select('id', 'name', 'sku', 'unit_id')
                ->with('unit:id,name,short_name')
                ->get();

            $warehouses = Warehouse::where('is_active', true)
                ->select('id', 'name', 'code')
                ->get();

            return $this->successResponse([
                'products' => $products,
                'warehouses' => $warehouses,
            ], 'Form data retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created stock in record.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'required|exists:warehouses,id',
                'supplier_name' => 'required|string|max:255',
                'supplier_invoice' => 'nullable|string|max:255',
                'received_date' => 'required|date',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $data = $validator->validated();

            // Generate a unique reference number for this batch
            $referenceNumber = 'SI-' . date('Ymd') . '-' . str_pad(StockIn::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            $createdRecords = [];

            foreach ($data['items'] as $item) {
                $stockInData = [
                    'reference_number' => $referenceNumber,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $item['quantity'] * $item['unit_price'],
                    'supplier_name' => $data['supplier_name'],
                    'supplier_invoice' => $data['supplier_invoice'] ?? null,
                    'received_date' => $data['received_date'],
                    'notes' => $data['notes'] ?? null,
                    'created_by' => Auth::id(),
                    'status' => 'pending',
                ];

                $stockIn = StockIn::create($stockInData);
                $stockIn->load(['product', 'warehouse', 'creator']);
                $createdRecords[] = $stockIn;
            }

            return $this->successResponse([
                'reference_number' => $referenceNumber,
                'records' => $createdRecords,
                'count' => count($createdRecords)
            ], 'Stock in records created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified stock in record.
     */
    public function show($id): JsonResponse
    {
        try {
            // Get first record to access reference number and common fields
            $firstRecord = StockIn::findOrFail($id);

            // Get all records with the same reference number
            $stockIns = StockIn::where('reference_number', $firstRecord->reference_number)
                ->with(['product.unit', 'warehouse', 'creator', 'approver'])
                ->orderBy('id')
                ->get();

            $response = [
                'id' => $firstRecord->id,
                'reference_number' => $firstRecord->reference_number,
                'warehouse_id' => $firstRecord->warehouse_id,
                'warehouse' => $firstRecord->warehouse,
                'supplier_name' => $firstRecord->supplier_name,
                'supplier_invoice' => $firstRecord->supplier_invoice,
                'received_date' => $firstRecord->received_date,
                'notes' => $firstRecord->notes,
                'status' => $firstRecord->status,
                'created_by' => $firstRecord->created_by,
                'creator' => $firstRecord->creator,
                'approved_by' => $firstRecord->approved_by,
                'approver' => $firstRecord->approver,
                'approved_at' => $firstRecord->approved_at,
                'rejection_reason' => $firstRecord->rejection_reason,
                'created_at' => $firstRecord->created_at,
                'updated_at' => $firstRecord->updated_at,
                'items' => $stockIns->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product' => $item->product,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_amount' => $item->total_amount,
                    ];
                }),
                'product_count' => $stockIns->count(),
                'total_amount' => $stockIns->sum('total_amount'),
            ];

            return $this->successResponse($response, 'Stock in record retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified stock in record.
     */
    public function update(Request $request, StockIn $stockIn): JsonResponse
    {
        try {
            // Only allow updating pending records
            if ($stockIn->status !== 'pending') {
                return $this->errorResponse('Only pending records can be updated', 403);
            }

            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'required|exists:warehouses,id',
                'supplier_name' => 'required|string|max:255',
                'supplier_invoice' => 'nullable|string|max:255',
                'received_date' => 'required|date',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $data = $validator->validated();
            $referenceNumber = $stockIn->reference_number;

            // Delete all existing items with this reference number
            StockIn::where('reference_number', $referenceNumber)->delete();

            // Create new items with the same reference number
            $createdRecords = [];
            foreach ($data['items'] as $item) {
                $stockInData = [
                    'reference_number' => $referenceNumber,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $item['quantity'] * $item['unit_price'],
                    'supplier_name' => $data['supplier_name'],
                    'supplier_invoice' => $data['supplier_invoice'] ?? null,
                    'received_date' => $data['received_date'],
                    'notes' => $data['notes'] ?? null,
                    'created_by' => Auth::id(),
                    'status' => 'pending',
                ];

                $newStockIn = StockIn::create($stockInData);
                $newStockIn->load(['product', 'warehouse', 'creator']);
                $createdRecords[] = $newStockIn;
            }

            return $this->successResponse([
                'reference_number' => $referenceNumber,
                'records' => $createdRecords,
                'count' => count($createdRecords)
            ], 'Stock in records updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Approve a stock in record.
     */
    public function approve(StockIn $stockIn): JsonResponse
    {
        try {
            if (!$stockIn->isPending()) {
                return $this->errorResponse('Only pending records can be approved', 403);
            }

            // Approve all items with the same reference number
            $allItems = StockIn::where('reference_number', $stockIn->reference_number)->get();

            foreach ($allItems as $item) {
                if ($item->isPending()) {
                    $item->approve(Auth::id());
                }
            }

            return $this->successResponse(null, 'Stock in records approved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Reject a stock in record.
     */
    public function reject(Request $request, StockIn $stockIn): JsonResponse
    {
        try {
            if (!$stockIn->isPending()) {
                return $this->errorResponse('Only pending records can be rejected', 403);
            }

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            // Reject all items with the same reference number
            $allItems = StockIn::where('reference_number', $stockIn->reference_number)->get();

            foreach ($allItems as $item) {
                if ($item->isPending()) {
                    $item->reject(Auth::id(), $request->rejection_reason);
                }
            }

            return $this->successResponse(null, 'Stock in records rejected successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified stock in record.
     */
    public function destroy(StockIn $stockIn): JsonResponse
    {
        try {
            // Only allow deleting pending or rejected records
            if ($stockIn->status === 'approved') {
                return $this->errorResponse('Approved records cannot be deleted', 403);
            }

            // Delete all items with the same reference number
            StockIn::where('reference_number', $stockIn->reference_number)->delete();

            return $this->successResponse(null, 'Stock in records deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get trashed (soft-deleted) stock in records.
     */
    public function getTrashed(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            $query = StockIn::onlyTrashed()
                ->select('reference_number', 'warehouse_id', 'supplier_name', 'supplier_invoice', 'received_date', 'notes', 'status', 'created_by', 'approved_by', 'approved_at', 'created_at', 'updated_at', 'deleted_at')
                ->selectRaw('MIN(id) as id')
                ->selectRaw('COUNT(*) as product_count')
                ->selectRaw('SUM(total_amount) as total_amount')
                ->with(['warehouse', 'creator', 'approver'])
                ->groupBy('reference_number', 'warehouse_id', 'supplier_name', 'supplier_invoice', 'received_date', 'notes', 'status', 'created_by', 'approved_by', 'approved_at', 'created_at', 'updated_at', 'deleted_at')
                ->orderBy('deleted_at', 'desc');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice', 'like', "%{$search}%");
                });
            }

            $trashedStockIns = $query->paginate($perPage);

            return $this->successResponse($trashedStockIns, 'Trashed stock in records retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Restore a soft-deleted stock in record.
     */
    public function restore($id): JsonResponse
    {
        try {
            $stockIn = StockIn::onlyTrashed()->findOrFail($id);

            // Restore all items with the same reference number
            StockIn::onlyTrashed()
                ->where('reference_number', $stockIn->reference_number)
                ->restore();

            return $this->successResponse(null, 'Stock in records restored successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Permanently delete a stock in record.
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $stockIn = StockIn::onlyTrashed()->findOrFail($id);

            // Permanently delete all items with the same reference number
            StockIn::onlyTrashed()
                ->where('reference_number', $stockIn->reference_number)
                ->forceDelete();

            return $this->successResponse(null, 'Stock in records permanently deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get statistics for stock in records.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->get('warehouse_id');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $query = StockIn::query();

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            if ($dateFrom) {
                $query->whereDate('received_date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('received_date', '<=', $dateTo);
            }

            $statistics = [
                'total_records' => $query->count(),
                'pending_count' => (clone $query)->where('status', 'pending')->count(),
                'approved_count' => (clone $query)->where('status', 'approved')->count(),
                'rejected_count' => (clone $query)->where('status', 'rejected')->count(),
                'total_amount' => (clone $query)->where('status', 'approved')->sum('total_amount'),
                'total_quantity' => (clone $query)->where('status', 'approved')->sum('quantity'),
            ];

            return $this->successResponse($statistics, 'Statistics retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
