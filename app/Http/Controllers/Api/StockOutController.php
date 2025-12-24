<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockOut;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockBalance;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class StockOutController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of stock out records.
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

            $query = StockOut::query()
                ->select('reference_number', 'warehouse_id', 'customer_name', 'order_number', 'issued_date', 'notes', 'status', 'created_by', 'approved_by', 'approved_at', 'created_at', 'updated_at')
                ->selectRaw('MIN(id) as id')
                ->selectRaw('COUNT(*) as product_count')
                ->selectRaw('SUM(total_amount) as total_amount')
                ->with(['warehouse', 'creator', 'approver'])
                ->groupBy('reference_number', 'warehouse_id', 'customer_name', 'order_number', 'issued_date', 'notes', 'status', 'created_by', 'approved_by', 'approved_at', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc');

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('order_number', 'like', "%{$search}%");
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
                        ->from('stock_outs')
                        ->where('product_id', $productId);
                });
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->whereDate('issued_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('issued_date', '<=', $dateTo);
            }

            $stockOuts = $query->paginate($perPage);

            return $this->successResponse($stockOuts, 'Stock out records retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get form data for creating stock out.
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
     * Get available stock for a product in a warehouse.
     */
    public function getAvailableStock(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'warehouse_id' => 'required|exists:warehouses,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $balance = StockBalance::where('product_id', $request->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();

            $availableStock = $balance ? $balance->available_quantity : 0;

            return $this->successResponse([
                'available_quantity' => $availableStock,
                'total_quantity' => $balance ? $balance->quantity : 0,
                'reserved_quantity' => $balance ? $balance->reserved_quantity : 0,
            ], 'Stock availability retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created stock out record.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'quantity' => 'required|numeric|min:0.01',
                'unit_price' => 'required|numeric|min:0',
                'customer_name' => 'required|string|max:255',
                'order_number' => 'nullable|string|max:255',
                'issued_date' => 'required|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            // Check available stock
            $balance = StockBalance::where('product_id', $request->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();

            if (!$balance || $balance->available_quantity < $request->quantity) {
                return $this->errorResponse('Insufficient stock available. Available quantity: ' . ($balance ? $balance->available_quantity : 0), 422);
            }

            $data = $validator->validated();
            $data['created_by'] = Auth::id();
            $data['status'] = 'pending';
            $data['total_amount'] = $data['quantity'] * $data['unit_price'];

            $stockOut = StockOut::create($data);
            $stockOut->load(['product', 'warehouse', 'creator']);

            return $this->successResponse($stockOut, 'Stock out record created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified stock out record.
     */
    public function show($id): JsonResponse
    {
        try {
            // Get first record to access reference number and common fields
            $firstRecord = StockOut::findOrFail($id);

            // Get all records with the same reference number
            $stockOuts = StockOut::where('reference_number', $firstRecord->reference_number)
                ->with(['product.unit', 'warehouse', 'creator', 'approver'])
                ->orderBy('id')
                ->get();

            $response = [
                'id' => $firstRecord->id,
                'reference_number' => $firstRecord->reference_number,
                'warehouse_id' => $firstRecord->warehouse_id,
                'warehouse' => $firstRecord->warehouse,
                'customer_name' => $firstRecord->customer_name,
                'order_number' => $firstRecord->order_number,
                'issued_date' => $firstRecord->issued_date,
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
                'items' => $stockOuts->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product' => $item->product,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_amount' => $item->total_amount,
                    ];
                }),
                'product_count' => $stockOuts->count(),
                'total_amount' => $stockOuts->sum('total_amount'),
            ];

            return $this->successResponse($response, 'Stock out record retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified stock out record.
     */
    public function update(Request $request, StockOut $stockOut): JsonResponse
    {
        try {
            // Only allow updating pending records
            if ($stockOut->status !== 'pending') {
                return $this->errorResponse('Only pending records can be updated', 403);
            }

            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'required|exists:warehouses,id',
                'customer_name' => 'required|string|max:255',
                'order_number' => 'nullable|string|max:255',
                'issued_date' => 'required|date',
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
            $referenceNumber = $stockOut->reference_number;

            // Check available stock for all items
            foreach ($data['items'] as $item) {
                $balance = StockBalance::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->first();

                if (!$balance || $balance->available_quantity < $item['quantity']) {
                    $product = Product::find($item['product_id']);
                    return $this->errorResponse(
                        'Insufficient stock for product: ' . ($product ? $product->name : 'ID ' . $item['product_id']) .
                            '. Available: ' . ($balance ? $balance->available_quantity : 0),
                        422
                    );
                }
            }

            // Delete all existing items with this reference number
            StockOut::where('reference_number', $referenceNumber)->delete();

            // Create new items with the same reference number
            $createdRecords = [];
            foreach ($data['items'] as $item) {
                $stockOutData = [
                    'reference_number' => $referenceNumber,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $item['quantity'] * $item['unit_price'],
                    'customer_name' => $data['customer_name'],
                    'order_number' => $data['order_number'] ?? null,
                    'issued_date' => $data['issued_date'],
                    'notes' => $data['notes'] ?? null,
                    'created_by' => Auth::id(),
                    'status' => 'pending',
                ];

                $newStockOut = StockOut::create($stockOutData);
                $newStockOut->load(['product', 'warehouse', 'creator']);
                $createdRecords[] = $newStockOut;
            }

            return $this->successResponse([
                'reference_number' => $referenceNumber,
                'records' => $createdRecords,
                'count' => count($createdRecords)
            ], 'Stock out records updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Approve a stock out record.
     */
    public function approve(StockOut $stockOut): JsonResponse
    {
        try {
            if (!$stockOut->isPending()) {
                return $this->errorResponse('Only pending records can be approved', 403);
            }

            // Approve all items with the same reference number
            $allItems = StockOut::where('reference_number', $stockOut->reference_number)->get();

            foreach ($allItems as $item) {
                if ($item->isPending()) {
                    $item->approve(Auth::id());
                }
            }

            return $this->successResponse(null, 'Stock out records approved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Reject a stock out record.
     */
    public function reject(Request $request, StockOut $stockOut): JsonResponse
    {
        try {
            if (!$stockOut->isPending()) {
                return $this->errorResponse('Only pending records can be rejected', 403);
            }

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            // Reject all items with the same reference number
            $allItems = StockOut::where('reference_number', $stockOut->reference_number)->get();

            foreach ($allItems as $item) {
                if ($item->isPending()) {
                    $item->reject(Auth::id(), $request->rejection_reason);
                }
            }

            return $this->successResponse(null, 'Stock out records rejected successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified stock out record.
     */
    public function destroy(StockOut $stockOut): JsonResponse
    {
        try {
            // Only allow deleting pending or rejected records
            if ($stockOut->status === 'approved') {
                return $this->errorResponse('Approved records cannot be deleted', 403);
            }

            // Delete all items with the same reference number
            StockOut::where('reference_number', $stockOut->reference_number)->delete();

            return $this->successResponse(null, 'Stock out records deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get trashed (soft-deleted) stock out records.
     */
    public function getTrashed(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            $query = StockOut::onlyTrashed()
                ->select('reference_number', 'warehouse_id', 'customer_name', 'order_number', 'issued_date', 'notes', 'status', 'created_by', 'approved_by', 'approved_at', 'created_at', 'updated_at', 'deleted_at')
                ->selectRaw('MIN(id) as id')
                ->selectRaw('COUNT(*) as product_count')
                ->selectRaw('SUM(total_amount) as total_amount')
                ->with(['warehouse', 'creator', 'approver'])
                ->groupBy('reference_number', 'warehouse_id', 'customer_name', 'order_number', 'issued_date', 'notes', 'status', 'created_by', 'approved_by', 'approved_at', 'created_at', 'updated_at', 'deleted_at')
                ->orderBy('deleted_at', 'desc');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('order_number', 'like', "%{$search}%");
                });
            }

            $trashedStockOuts = $query->paginate($perPage);

            return $this->successResponse($trashedStockOuts, 'Trashed stock out records retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Restore a soft-deleted stock out record.
     */
    public function restore($id): JsonResponse
    {
        try {
            $stockOut = StockOut::onlyTrashed()->findOrFail($id);

            // Restore all items with the same reference number
            StockOut::onlyTrashed()
                ->where('reference_number', $stockOut->reference_number)
                ->restore();

            return $this->successResponse(null, 'Stock out records restored successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Permanently delete a stock out record.
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $stockOut = StockOut::onlyTrashed()->findOrFail($id);

            // Permanently delete all items with the same reference number
            StockOut::onlyTrashed()
                ->where('reference_number', $stockOut->reference_number)
                ->forceDelete();

            return $this->successResponse(null, 'Stock out records permanently deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get statistics for stock out records.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->get('warehouse_id');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $query = StockOut::query();

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            if ($dateFrom) {
                $query->whereDate('issued_date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('issued_date', '<=', $dateTo);
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
