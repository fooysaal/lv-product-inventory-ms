<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Load userType relationship if not loaded
            if (!$user->relationLoaded('userType')) {
                $user->load('userType');
            }

            $userType = $user->userType->slug ?? null;

            // If user type not found, return error
            if (!$userType) {
                return response()->json([
                    'success' => false,
                    'message' => 'User type not found. Please contact administrator.',
                    'data' => null,
                ], 400);
            }

            $data = [];

            if (in_array($userType, ['admin', 'stock_manager'])) {
                $data = $this->getAdminManagerStats($user);
            } elseif ($userType === 'stock_executive') {
                $data = $this->getExecutiveStats($user);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user type: ' . $userType,
                    'data' => null,
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dashboard data retrieved successfully.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Dashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Get statistics for Admin and Stock Manager
     *
     * @param $user
     * @return array
     */
    private function getAdminManagerStats($user): array
    {
        // Basic counts
        $stats = [
            'products_count' => Product::count(),
            'warehouses_count' => Warehouse::count(),
            'stock_in_count' => StockIn::count(),
            'stock_out_count' => StockOut::count(),
        ];

        // Stock In/Out comparison for last 6 months
        $stockComparison = $this->getStockInOutComparison();

        // Recent stock movements (last 10)
        $stockMovements = $this->getRecentStockMovements();

        // Low stock alerts
        $lowStockAlerts = $this->getLowStockAlerts();

        return [
            'stats' => $stats,
            'stock_comparison' => $stockComparison,
            'stock_movements' => $stockMovements,
            'low_stock_alerts' => $lowStockAlerts,
        ];
    }

    /**
     * Get statistics for Stock Executive
     *
     * @param $user
     * @return array
     */
    private function getExecutiveStats($user): array
    {
        $stats = [
            'products_count' => Product::count(),
            'warehouses_count' => Warehouse::count(),
            'my_stock_in_count' => StockIn::where('created_by', $user->id)->count(),
            'my_stock_out_count' => StockOut::where('created_by', $user->id)->count(),
        ];

        // Recent activities by this user
        $myRecentActivities = $this->getUserRecentActivities($user->id);

        return [
            'stats' => $stats,
            'my_recent_activities' => $myRecentActivities,
        ];
    }

    /**
     * Get Stock In/Out comparison for last 6 months
     *
     * @return array
     */
    private function getStockInOutComparison(): array
    {
        $months = [];
        $stockInData = [];
        $stockOutData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');
            $months[] = $monthName;

            // Count stock in for this month
            $stockInCount = StockIn::whereYear('received_date', $date->year)
                ->whereMonth('received_date', $date->month)
                ->where('status', 'approved')
                ->count();

            // Count stock out for this month
            $stockOutCount = StockOut::whereYear('issued_date', $date->year)
                ->whereMonth('issued_date', $date->month)
                ->where('status', 'approved')
                ->count();

            $stockInData[] = $stockInCount;
            $stockOutData[] = $stockOutCount;
        }

        return [
            'labels' => $months,
            'stock_in' => $stockInData,
            'stock_out' => $stockOutData,
        ];
    }

    /**
     * Get recent stock movements
     *
     * @return array
     */
    private function getRecentStockMovements(): array
    {
        $stockIns = StockIn::with(['product', 'warehouse', 'creator'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'in',
                    'reference' => $item->reference_number,
                    'product' => $item->product->name ?? 'N/A',
                    'warehouse' => $item->warehouse->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'status' => $item->status,
                    'date' => $item->received_date->format('Y-m-d'),
                    'created_by' => $item->creator->name ?? 'N/A',
                ];
            });

        $stockOuts = StockOut::with(['product', 'warehouse', 'creator'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'out',
                    'reference' => $item->reference_number,
                    'product' => $item->product->name ?? 'N/A',
                    'warehouse' => $item->warehouse->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'status' => $item->status,
                    'date' => $item->issued_date->format('Y-m-d'),
                    'created_by' => $item->creator->name ?? 'N/A',
                ];
            });

        return $stockIns->concat($stockOuts)
            ->sortByDesc('date')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Get low stock alerts
     *
     * @return array
     */
    private function getLowStockAlerts(): array
    {
        // For now, return empty since we need actual stock data
        // In production, this would check stock_balances aggregated by product
        return [
            'count' => 0,
            'products' => [],
        ];
    }

    /**
     * Get user's recent activities
     *
     * @param int $userId
     * @return array
     */
    private function getUserRecentActivities($userId): array
    {
        $stockIns = StockIn::with(['product', 'warehouse'])
            ->where('created_by', $userId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'in',
                    'reference' => $item->reference_number,
                    'product' => $item->product->name ?? 'N/A',
                    'warehouse' => $item->warehouse->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'status' => $item->status,
                    'date' => $item->received_date->format('Y-m-d'),
                ];
            });

        $stockOuts = StockOut::with(['product', 'warehouse'])
            ->where('created_by', $userId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'out',
                    'reference' => $item->reference_number,
                    'product' => $item->product->name ?? 'N/A',
                    'warehouse' => $item->warehouse->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'status' => $item->status,
                    'date' => $item->issued_date->format('Y-m-d'),
                ];
            });

        return $stockIns->concat($stockOuts)
            ->sortByDesc('date')
            ->take(10)
            ->values()
            ->toArray();
    }
}
