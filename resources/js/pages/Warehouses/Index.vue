<template>
    <div class="flex-1 p-6 overflow-y-auto">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Warehouses</h1>
                <p class="text-sm text-gray-600">Manage warehouse locations and inventory</p>
            </div>
            <router-link
                to="/warehouses/create"
                class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
            >
                <i class="fas fa-plus mr-2"></i>
                Add Warehouse
            </router-link>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search by name, code, city, email or phone..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                            @input="debouncedSearch"
                        />
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        v-model="filters.status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                        @change="fetchWarehouses"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div>
                    <button
                        @click="clearFilters"
                        class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Warehouses Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <!-- Table -->
            <div v-else-if="warehouses.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Warehouse Info
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Location
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Contact
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Manager
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Capacity
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="warehouse in warehouses" :key="warehouse.id" class="hover:bg-gray-50 transition-colors">
                            <!-- Warehouse Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg mr-3">
                                        <i class="fas fa-warehouse text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ warehouse.name }}</div>
                                        <div class="text-xs text-gray-500">Code: {{ warehouse.code }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Location -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ warehouse.city || 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ warehouse.country }}</div>
                            </td>

                            <!-- Contact -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ warehouse.phone || 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ warehouse.email || 'N/A' }}</div>
                            </td>

                            <!-- Manager -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="warehouse.manager" class="text-sm text-gray-900">
                                    {{ warehouse.manager.name }}
                                </div>
                                <div v-else class="text-sm text-gray-400 italic">Not assigned</div>
                            </td>

                            <!-- Capacity -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="warehouse.capacity" class="text-sm text-gray-900">
                                    {{ warehouse.capacity }} {{ warehouse.capacity_unit }}
                                </div>
                                <div v-else class="text-sm text-gray-400 italic">Not set</div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    @click="toggleStatus(warehouse)"
                                    :class="[
                                        'px-3 py-1 text-xs font-semibold rounded-full transition-colors',
                                        warehouse.is_active
                                            ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                            : 'bg-red-100 text-red-800 hover:bg-red-200'
                                    ]"
                                >
                                    {{ warehouse.is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <router-link
                                        :to="`/warehouses/${warehouse.id}`"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </router-link>
                                    <router-link
                                        :to="`/warehouses/${warehouse.id}/edit`"
                                        class="text-green-600 hover:text-green-900"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </router-link>
                                    <button
                                        @click="confirmDelete(warehouse)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <i class="fas fa-warehouse text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg">No warehouses found</p>
                <p class="text-gray-400 text-sm mt-1">Start by creating your first warehouse</p>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                    </div>
                    <div class="flex gap-2">
                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            @click="changePage(page)"
                            :disabled="page === pagination.current_page"
                            :class="[
                                'px-3 py-1 text-sm rounded-lg transition-colors',
                                page === pagination.current_page
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-300'
                            ]"
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
    name: 'WarehouseIndex',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            warehouses: [],
            loading: false,
            filters: {
                search: '',
                status: ''
            },
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 10,
                total: 0,
                from: 0,
                to: 0
            },
            searchTimeout: null
        };
    },
    computed: {
        visiblePages() {
            const pages = [];
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;

            if (last <= 7) {
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                if (current <= 4) {
                    for (let i = 1; i <= 5; i++) pages.push(i);
                    pages.push('...');
                    pages.push(last);
                } else if (current >= last - 3) {
                    pages.push(1);
                    pages.push('...');
                    for (let i = last - 4; i <= last; i++) pages.push(i);
                } else {
                    pages.push(1);
                    pages.push('...');
                    for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                    pages.push('...');
                    pages.push(last);
                }
            }

            return pages;
        }
    },
    mounted() {
        this.fetchWarehouses();
    },
    methods: {
        async fetchWarehouses(page = 1) {
            this.loading = true;

            try {
                const params = {
                    page,
                    per_page: this.pagination.per_page,
                    search: this.filters.search,
                    status: this.filters.status
                };

                const response = await axios.get('/api/warehouses', { params });

                if (response.data.success) {
                    this.warehouses = response.data.data.data;
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
                console.error('Error fetching warehouses:', err);
                this.toast.error(err.response?.data?.message || 'Failed to load warehouses');
            } finally {
                this.loading = false;
            }
        },
        debouncedSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.fetchWarehouses();
            }, 500);
        },
        clearFilters() {
            this.filters = {
                search: '',
                status: ''
            };
            this.fetchWarehouses();
        },
        changePage(page) {
            if (page !== '...' && page !== this.pagination.current_page) {
                this.fetchWarehouses(page);
            }
        },
        async toggleStatus(warehouse) {
            try {
                const response = await axios.patch(`/api/warehouses/${warehouse.id}/toggle-status`);

                if (response.data.success) {
                    warehouse.is_active = response.data.data.is_active;
                    this.toast.success(response.data.message);
                }
            } catch (err) {
                console.error('Error toggling status:', err);
                this.toast.error(err.response?.data?.message || 'Failed to update status');
            }
        },
        confirmDelete(warehouse) {
            if (confirm(`Are you sure you want to delete "${warehouse.name}"?\n\nThis action cannot be undone.`)) {
                this.deleteWarehouse(warehouse.id);
            }
        },
        async deleteWarehouse(id) {
            try {
                const response = await axios.delete(`/api/warehouses/${id}`);

                if (response.data.success) {
                    this.toast.success(response.data.message);
                    this.fetchWarehouses(this.pagination.current_page);
                }
            } catch (err) {
                console.error('Error deleting warehouse:', err);
                this.toast.error(err.response?.data?.message || 'Failed to delete warehouse');
            }
        }
    }
};
</script>
