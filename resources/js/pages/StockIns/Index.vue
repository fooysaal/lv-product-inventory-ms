<template>
    <div class="p-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Stock In</h1>
                <p class="text-gray-600 mt-1">Manage incoming stock transactions</p>
            </div>
            <div class="flex gap-3">
                <router-link
                    to="/stock-ins/trashed"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    <i class="fas fa-trash"></i>
                    <span>Trash</span>
                </router-link>
                <router-link
                    to="/stock-ins/create"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                >
                    <i class="fas fa-plus"></i>
                    <span>Create Stock In</span>
                </router-link>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Records</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ statistics.total_records || 0 }}</h3>
                    </div>
                    <i class="fas fa-box text-blue-500 text-3xl"></i>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-yellow-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Pending</p>
                        <h3 class="text-2xl font-bold text-yellow-600 mt-1">{{ statistics.pending_count || 0 }}</h3>
                    </div>
                    <i class="fas fa-clock text-yellow-500 text-3xl"></i>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Approved</p>
                        <h3 class="text-2xl font-bold text-green-600 mt-1">{{ statistics.approved_count || 0 }}</h3>
                    </div>
                    <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Rejected</p>
                        <h3 class="text-2xl font-bold text-red-600 mt-1">{{ statistics.rejected_count || 0 }}</h3>
                    </div>
                    <i class="fas fa-times-circle text-red-500 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search by reference, supplier..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
                        @input="debouncedSearch"
                    />
                </div>
                <select
                    v-model="filters.status"
                    @change="fetchStockIns"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
                >
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select
                    v-model="filters.warehouse_id"
                    @change="fetchStockIns"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
                >
                    <option value="">All Warehouses</option>
                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                        {{ warehouse.name }}
                    </option>
                </select>
                <select
                    v-model="filters.perPage"
                    @change="fetchStockIns"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
                >
                    <option :value="10">10 per page</option>
                    <option :value="25">25 per page</option>
                    <option :value="50">50 per page</option>
                    <option :value="100">100 per page</option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="w-12 h-12 border-3 border-gray-200 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading stock in records...</p>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Products</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Warehouse</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="stockIn in stockIns.data" :key="stockIn.reference_number" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ stockIn.reference_number }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ stockIn.product_count }} {{ stockIn.product_count === 1 ? 'item' : 'items' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ stockIn.warehouse?.name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                ${{ formatNumber(stockIn.total_amount) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div>{{ stockIn.supplier_name }}</div>
                                <div v-if="stockIn.supplier_invoice" class="text-xs text-gray-500">{{ stockIn.supplier_invoice }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ formatDate(stockIn.received_date) }}
                            </td>
                            <td class="px-6 py-4">
                                <span :class="getStatusClass(stockIn.status)" class="px-2.5 py-1 text-xs font-medium rounded-full">
                                    {{ stockIn.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <router-link
                                        :to="`/stock-ins/${stockIn.id}`"
                                        class="text-blue-600 hover:text-blue-800"
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </router-link>
                                    <router-link
                                        v-if="stockIn.status === 'pending'"
                                        :to="`/stock-ins/${stockIn.id}/edit`"
                                        class="text-green-600 hover:text-green-800"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </router-link>
                                    <button
                                        v-if="stockIn.status === 'pending' && canApprove"
                                        @click="handleApprove(stockIn)"
                                        class="text-teal-600 hover:text-teal-800"
                                        title="Approve"
                                    >
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button
                                        v-if="stockIn.status === 'pending' && canApprove"
                                        @click="handleReject(stockIn)"
                                        class="text-red-600 hover:text-red-800"
                                        title="Reject"
                                    >
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    <button
                                        v-if="stockIn.status !== 'approved'"
                                        @click="handleDelete(stockIn)"
                                        class="text-red-600 hover:text-red-800"
                                        title="Delete"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!stockIns.data || stockIns.data.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 text-gray-400"></i>
                                <p>No stock in records found</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="stockIns.data && stockIns.data.length > 0" class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Showing {{ stockIns.from }} to {{ stockIns.to }} of {{ stockIns.total }} results
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="goToPage(stockIns.current_page - 1)"
                            :disabled="!stockIns.prev_page_url"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700"
                        >
                            Previous
                        </button>
                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            @click="goToPage(page)"
                            :class="page === stockIns.current_page ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg"
                        >
                            {{ page }}
                        </button>
                        <button
                            @click="goToPage(stockIns.current_page + 1)"
                            :disabled="!stockIns.next_page_url"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Reject Stock In</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                    <textarea
                        v-model="rejectionReason"
                        rows="4"
                        placeholder="Enter rejection reason..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                    ></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button
                        @click="showRejectModal = false"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmReject"
                        :disabled="!rejectionReason || submitting"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                    >
                        Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export default {
    name: 'StockInIndex',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            stockIns: {
                data: [],
                current_page: 1,
                last_page: 1,
                per_page: 15,
                total: 0
            },
            warehouses: [],
            statistics: {},
            filters: {
                search: '',
                status: '',
                warehouse_id: '',
                perPage: 15
            },
            loading: false,
            submitting: false,
            showRejectModal: false,
            selectedStockIn: null,
            rejectionReason: '',
            searchTimeout: null
        };
    },
    computed: {
        visiblePages() {
            const current = this.stockIns.current_page;
            const last = this.stockIns.last_page;
            const delta = 2;
            const range = [];

            for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                range.push(i);
            }

            if (current - delta > 2) range.unshift('...');
            if (current + delta < last - 1) range.push('...');

            range.unshift(1);
            if (last > 1) range.push(last);

            return range;
        },
        canApprove() {
            // Check if user has manager role
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            return user.user_type_name === 'Stock Manager' || user.user_type_name === 'Admin';
        }
    },
    mounted() {
        this.fetchWarehouses();
        this.fetchStockIns();
        this.fetchStatistics();
    },
    methods: {
        debouncedSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.fetchStockIns();
            }, 500);
        },

        async fetchWarehouses() {
            try {
                const response = await axios.get('/api/warehouses', {
                    params: { per_page: 100 }
                });
                if (response.data.success) {
                    this.warehouses = response.data.data.data;
                }
            } catch (err) {
                console.error('Error fetching warehouses:', err);
            }
        },

        async fetchStockIns() {
            this.loading = true;
            try {
                const response = await axios.get('/api/stock-ins', {
                    params: {
                        search: this.filters.search,
                        status: this.filters.status,
                        warehouse_id: this.filters.warehouse_id,
                        per_page: this.filters.perPage,
                        page: this.stockIns.current_page
                    }
                });

                if (response.data.success) {
                    this.stockIns = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching stock ins:', err);
                this.toast.error('Failed to fetch stock in records');
            } finally {
                this.loading = false;
            }
        },

        async fetchStatistics() {
            try {
                const response = await axios.get('/api/stock-ins/statistics', {
                    params: {
                        warehouse_id: this.filters.warehouse_id
                    }
                });
                if (response.data.success) {
                    this.statistics = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching statistics:', err);
            }
        },

        goToPage(page) {
            if (page >= 1 && page <= this.stockIns.last_page) {
                this.stockIns.current_page = page;
                this.fetchStockIns();
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

        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        async handleApprove(stockIn) {
            if (!confirm(`Are you sure you want to approve this stock in (${stockIn.reference_number})?`)) {
                return;
            }

            this.submitting = true;
            try {
                const response = await axios.post(`/api/stock-ins/${stockIn.id}/approve`);
                if (response.data.success) {
                    this.toast.success('Stock in approved successfully');
                    this.fetchStockIns();
                    this.fetchStatistics();
                }
            } catch (err) {
                console.error('Error approving stock in:', err);
                this.toast.error(err.response?.data?.message || 'Failed to approve stock in');
            } finally {
                this.submitting = false;
            }
        },

        handleReject(stockIn) {
            this.selectedStockIn = stockIn;
            this.rejectionReason = '';
            this.showRejectModal = true;
        },

        async confirmReject() {
            this.submitting = true;
            try {
                const response = await axios.post(`/api/stock-ins/${this.selectedStockIn.id}/reject`, {
                    rejection_reason: this.rejectionReason
                });
                if (response.data.success) {
                    this.toast.success('Stock in rejected successfully');
                    this.showRejectModal = false;
                    this.fetchStockIns();
                    this.fetchStatistics();
                }
            } catch (err) {
                console.error('Error rejecting stock in:', err);
                this.toast.error(err.response?.data?.message || 'Failed to reject stock in');
            } finally {
                this.submitting = false;
            }
        },

        async handleDelete(stockIn) {
            if (!confirm(`Are you sure you want to delete this stock in (${stockIn.reference_number})?`)) {
                return;
            }

            this.submitting = true;
            try {
                const response = await axios.delete(`/api/stock-ins/${stockIn.id}`);
                if (response.data.success) {
                    this.toast.success('Stock in deleted successfully');
                    this.fetchStockIns();
                    this.fetchStatistics();
                }
            } catch (err) {
                console.error('Error deleting stock in:', err);
                this.toast.error(err.response?.data?.message || 'Failed to delete stock in');
            } finally {
                this.submitting = false;
            }
        }
    }
};
</script>
