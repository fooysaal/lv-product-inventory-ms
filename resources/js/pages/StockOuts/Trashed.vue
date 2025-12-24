<template>
    <div class="p-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                    <router-link to="/stock-outs" class="hover:text-blue-600">Stock Out</router-link>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-gray-900 font-medium">Trash</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Trashed Stock Out Records</h1>
                <p class="text-gray-600 mt-1">View and manage deleted stock out transactions</p>
            </div>
            <router-link
                to="/stock-outs"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
                <i class="fas fa-arrow-left"></i>
                <span>Back to Stock Out</span>
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search by reference, customer..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
                        @input="debouncedSearch"
                    />
                </div>
                <select
                    v-model="filters.perPage"
                    @change="fetchTrashedStockOuts"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
                >
                    <option :value="10">10 per page</option>
                    <option :value="25">25 per page</option>
                    <option :value="50">50 per page</option>
                    <option :value="100">100 per page</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div v-if="loading" class="p-12 text-center">
                <div class="w-12 h-12 border-3 border-gray-200 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
                <p class="mt-4 text-gray-600">Loading trashed records...</p>
            </div>

            <div v-else-if="stockOuts.length === 0" class="p-12 text-center">
                <i class="fas fa-trash-restore text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-600 text-lg">No trashed records found</p>
                <router-link to="/stock-outs" class="text-blue-600 hover:text-blue-700 mt-2 inline-block">
                    Go to Stock Out
                </router-link>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Warehouse</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Products</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Deleted At</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="stockOut in stockOuts" :key="stockOut.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ stockOut.reference_number }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ stockOut.customer_name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ stockOut.warehouse?.name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ stockOut.product_count }} item{{ stockOut.product_count !== 1 ? 's' : '' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="getStatusClass(stockOut.status)" class="px-3 py-1 text-xs font-medium rounded-full">
                                    {{ stockOut.status.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">
                                ${{ formatNumber(stockOut.total_amount) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ formatDateTime(stockOut.deleted_at) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        @click="confirmRestore(stockOut)"
                                        class="px-3 py-1.5 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition-colors"
                                        title="Restore"
                                    >
                                        <i class="fas fa-trash-restore mr-1"></i>
                                        Restore
                                    </button>
                                    <button
                                        @click="confirmPermanentDelete(stockOut)"
                                        class="px-3 py-1.5 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition-colors"
                                        title="Permanently Delete"
                                    >
                                        <i class="fas fa-trash mr-1"></i>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                    </p>
                    <div class="flex gap-2">
                        <button
                            v-for="page in paginationPages"
                            :key="page"
                            @click="changePage(page)"
                            :disabled="page === pagination.current_page"
                            class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                            :class="page === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export default {
    name: 'StockOutTrashed',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            stockOuts: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 15,
                total: 0,
                from: 0,
                to: 0
            },
            filters: {
                search: '',
                perPage: 15
            },
            loading: false,
            searchTimeout: null
        };
    },
    computed: {
        paginationPages() {
            const pages = [];
            const currentPage = this.pagination.current_page;
            const lastPage = this.pagination.last_page;
            const delta = 2;

            for (let i = Math.max(1, currentPage - delta); i <= Math.min(lastPage, currentPage + delta); i++) {
                pages.push(i);
            }

            return pages;
        }
    },
    mounted() {
        this.fetchTrashedStockOuts();
    },
    methods: {
        async fetchTrashedStockOuts(page = 1) {
            this.loading = true;
            try {
                const params = {
                    page: page,
                    per_page: this.filters.perPage,
                    search: this.filters.search
                };

                const response = await axios.get('/api/stock-outs/trashed', { params });

                if (response.data.success) {
                    this.stockOuts = response.data.data.data;
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total,
                        from: response.data.data.from,
                        to: response.data.data.to
                    };
                }
            } catch (err) {
                console.error('Error fetching trashed stock outs:', err);
                this.toast.error('Failed to load trashed records');
            } finally {
                this.loading = false;
            }
        },

        debouncedSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.fetchTrashedStockOuts(1);
            }, 500);
        },

        changePage(page) {
            if (page !== this.pagination.current_page) {
                this.fetchTrashedStockOuts(page);
            }
        },

        confirmRestore(stockOut) {
            if (confirm(`Are you sure you want to restore "${stockOut.reference_number}"?`)) {
                this.restoreStockOut(stockOut.id);
            }
        },

        async restoreStockOut(id) {
            try {
                const response = await axios.post(`/api/stock-outs/${id}/restore`);
                if (response.data.success) {
                    this.toast.success('Stock out restored successfully');
                    this.fetchTrashedStockOuts(this.pagination.current_page);
                }
            } catch (err) {
                console.error('Error restoring stock out:', err);
                this.toast.error(err.response?.data?.message || 'Failed to restore stock out');
            }
        },

        confirmPermanentDelete(stockOut) {
            if (confirm(`Are you sure you want to permanently delete "${stockOut.reference_number}"? This action cannot be undone!`)) {
                this.permanentlyDelete(stockOut.id);
            }
        },

        async permanentlyDelete(id) {
            try {
                const response = await axios.delete(`/api/stock-outs/${id}/force-delete`);
                if (response.data.success) {
                    this.toast.success('Stock out permanently deleted');
                    this.fetchTrashedStockOuts(this.pagination.current_page);
                }
            } catch (err) {
                console.error('Error deleting stock out:', err);
                this.toast.error(err.response?.data?.message || 'Failed to delete stock out');
            }
        },

        getStatusClass(status) {
            const classes = {
                pending: 'bg-yellow-100 text-yellow-800',
                approved: 'bg-green-100 text-green-800',
                rejected: 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },

        formatNumber(value) {
            return parseFloat(value).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        formatDateTime(datetime) {
            return new Date(datetime).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
};
</script>
