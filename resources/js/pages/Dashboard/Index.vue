<template>
    <div>
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p class="ml-3 text-sm text-red-800 dark:text-red-200">
                    {{ error }}
                </p>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div v-else>
            <!-- Admin/Manager Dashboard -->
            <div v-if="isAdminOrManager">
                <!-- Stats Cards -->
                <StatsCards :stats="adminStats" />

                <!-- Charts and Alerts -->
                <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <!-- Stock Comparison Chart - 6 cols -->
                    <div class="lg:col-span-1">
                        <StockComparisonChart :data="dashboardData.stock_comparison || {}" />
                    </div>

                    <!-- Stock Movements and Low Stock - 6 cols -->
                    <div class="lg:col-span-1 space-y-5">
                        <StockMovements :movements="dashboardData.stock_movements || []" />
                        <LowStockAlerts
                            :count="dashboardData.low_stock_alerts?.count || 0"
                            :products="dashboardData.low_stock_alerts?.products || []"
                        />
                    </div>
                </div>
            </div>

            <!-- Executive Dashboard -->
            <div v-else-if="isExecutive">
                <!-- Stats Cards -->
                <StatsCards :stats="executiveStats" />

                <!-- Recent Activities -->
                <div class="mt-6">
                    <StockMovements
                        :movements="dashboardData.my_recent_activities || []"
                    />
                </div>
            </div>

            <!-- Unknown Role -->
            <div v-else class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">
                    Dashboard not available for your role
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';
import { apiService } from '@/services/api';
import StatsCards from '@/components/Dashboard/StatsCards.vue';
import StockComparisonChart from '@/components/Dashboard/StockComparisonChart.vue';
import StockMovements from '@/components/Dashboard/StockMovements.vue';
import LowStockAlerts from '@/components/Dashboard/LowStockAlerts.vue';

const { user } = useAuth();
const loading = ref(true);
const error = ref(null);
const dashboardData = ref({});

const isAdminOrManager = computed(() => {
    const userType = user.value?.user_type?.slug;
    return userType === 'admin' || userType === 'stock_manager';
});

const isExecutive = computed(() => {
    return user.value?.user_type?.slug === 'stock_executive';
});

const adminStats = computed(() => {
    const stats = dashboardData.value.stats || {};
    return [
        {
            label: 'Products',
            value: stats.products_count || 0,
            icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            color: 'blue'
        },
        {
            label: 'Warehouses',
            value: stats.warehouses_count || 0,
            icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            color: 'green'
        },
        {
            label: 'Stock In',
            value: stats.stock_in_count || 0,
            icon: 'M7 16V4m0 0L3 8m4-4l4 4',
            color: 'indigo'
        },
        {
            label: 'Stock Out',
            value: stats.stock_out_count || 0,
            icon: 'M17 16V4m0 0l4 4m-4-4l-4 4',
            color: 'purple'
        }
    ];
});

const executiveStats = computed(() => {
    const stats = dashboardData.value.stats || {};
    return [
        {
            label: 'Products',
            value: stats.products_count || 0,
            icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            color: 'blue'
        },
        {
            label: 'Warehouses',
            value: stats.warehouses_count || 0,
            icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            color: 'green'
        },
        {
            label: 'My Stock In',
            value: stats.my_stock_in_count || 0,
            icon: 'M7 16V4m0 0L3 8m4-4l4 4',
            color: 'indigo'
        },
        {
            label: 'My Stock Out',
            value: stats.my_stock_out_count || 0,
            icon: 'M17 16V4m0 0l4 4m-4-4l-4 4',
            color: 'purple'
        }
    ];
});

const fetchDashboardData = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await apiService.get('/dashboard');

        console.log('Dashboard API Response:', response);

        if (response.data.success) {
            dashboardData.value = response.data.data;
        } else {
            error.value = response.data.message || 'Failed to load dashboard data';
        }
    } catch (err) {
        console.error('Dashboard error full:', err);
        console.error('Dashboard error response:', err.response);

        // More detailed error message
        if (err.response) {
            error.value = err.response?.data?.message || err.response?.data?.error || 'Failed to load dashboard data';
        } else if (err.request) {
            error.value = 'No response from server. Please check your connection.';
        } else {
            error.value = err.message || 'An error occurred while loading dashboard data';
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchDashboardData();
});
</script>
