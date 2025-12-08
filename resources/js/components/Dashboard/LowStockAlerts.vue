<template>
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Low Stock Alerts
            </h3>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                {{ count }} Products
            </span>
        </div>
        <div class="p-6">
            <div v-if="products.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                No low stock alerts
            </div>
            <div v-else class="space-y-4">
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                >
                    <div class="flex-1">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ product.name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    SKU: {{ product.sku }} • {{ product.category }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ product.current_stock }} {{ product.unit }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Threshold: {{ product.low_stock_threshold }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    count: {
        type: Number,
        default: 0
    },
    products: {
        type: Array,
        default: () => []
    }
});
</script>
