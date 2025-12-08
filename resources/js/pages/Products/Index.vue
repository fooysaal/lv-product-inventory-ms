<template>
    <div class="p-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Products</h1>
                <p class="text-gray-600 mt-1">Manage product inventory</p>
            </div>
            <router-link
                to="/products/create"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
            >
                <i class="fas fa-plus"></i>
                <span>Add Product</span>
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex gap-4 items-center flex-wrap">
                <div class="relative flex-1 max-w-md">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search products..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                        @input="debouncedSearch"
                    />
                </div>
                <div class="flex gap-3">
                    <select
                        v-model="filters.category_id"
                        @change="fetchProducts"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                    >
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <select
                        v-model="filters.unit_id"
                        @change="fetchProducts"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                    >
                        <option value="">All Units</option>
                        <option v-for="unit in units" :key="unit.id" :value="unit.id">
                            {{ unit.name }}
                        </option>
                    </select>
                    <select
                        v-model="filters.status"
                        @change="fetchProducts"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select
                        v-model="filters.stock_status"
                        @change="fetchProducts"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                    >
                        <option value="">All Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="overstock">Overstock</option>
                    </select>
                    <select
                        v-model="filters.perPage"
                        @change="fetchProducts"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                    >
                        <option :value="10">10 per page</option>
                        <option :value="25">25 per page</option>
                        <option :value="50">50 per page</option>
                        <option :value="100">100 per page</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="w-12 h-12 border-3 border-gray-200 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading products...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-exclamation-triangle text-5xl text-red-500"></i>
            <p class="mt-4 text-gray-600">{{ error }}</p>
            <button @click="fetchProducts" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                Retry
            </button>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Image
                            </th>
                            <th
                                @click="sort('name')"
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100"
                            >
                                <div class="flex items-center gap-1">
                                    Name
                                    <i :class="getSortIcon('name')" class="text-xs"></i>
                                </div>
                            </th>
                            <th
                                @click="sort('sku')"
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100"
                            >
                                <div class="flex items-center gap-1">
                                    SKU
                                    <i :class="getSortIcon('sku')" class="text-xs"></i>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Unit
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Cost Price
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Selling Price
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Min Stock
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="products.length === 0">
                            <td colspan="10" class="px-4 py-12 text-center">
                                <i class="fas fa-inbox text-5xl text-gray-300 block mb-4"></i>
                                <p class="text-gray-600">No products found</p>
                            </td>
                        </tr>
                        <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <div class="w-10 h-10 rounded overflow-hidden bg-gray-100 flex items-center justify-center">
                                    <img
                                        v-if="product.image"
                                        :src="product.image"
                                        :alt="product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <i v-else class="fas fa-box text-gray-400 text-lg"></i>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm font-semibold text-gray-900">{{ product.name }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ product.sku }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-block px-3 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                    {{ product.category?.name || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-block px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    {{ product.unit?.name || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ formatPrice(product.cost_price) }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ formatPrice(product.selling_price) }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ product.min_stock_level }}</td>
                            <td class="px-4 py-4">
                                <button
                                    @click="toggleStatus(product)"
                                    :class="[
                                        'px-3.5 py-1.5 text-xs font-medium rounded-full border-0 cursor-pointer transition-colors',
                                        product.is_active
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                            : 'bg-red-100 text-red-700 hover:bg-red-200'
                                    ]"
                                    :disabled="statusLoading[product.id]"
                                >
                                    <span v-if="statusLoading[product.id]" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                                    <span v-else>{{ product.is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <router-link
                                        :to="`/products/${product.id}/edit`"
                                        class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-500 hover:text-white transition-colors"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </router-link>
                                    <button
                                        @click="confirmDelete(product)"
                                        class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-lg hover:bg-red-500 hover:text-white transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                                        title="Delete"
                                        :disabled="deleteLoading[product.id]"
                                    >
                                        <span v-if="deleteLoading[product.id]" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                                        <i v-else class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
                <div class="text-sm text-gray-600">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
                </div>
                <div class="flex gap-2">
                    <button
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <button
                        v-for="page in visiblePages"
                        :key="page"
                        @click="changePage(page)"
                        :class="[
                            'px-3 py-1.5 border rounded-lg text-sm font-medium transition-colors',
                            page === pagination.current_page
                                ? 'bg-blue-500 text-white border-blue-500'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                        ]"
                    >
                        {{ page }}
                    </button>
                    <button
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-lg w-full max-w-md shadow-xl">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800">Confirm Delete</h3>
                    <button @click="showDeleteModal = false" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 mb-4">
                        Are you sure you want to delete "<strong>{{ productToDelete?.name }}</strong>"?
                    </p>
                    <p v-if="productToDelete && productToDelete.has_stock_transactions" class="text-red-600 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>This product has associated stock transactions.</span>
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
                    <button @click="showDeleteModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="deleteProduct"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="deletingInProgress"
                    >
                        <span v-if="deletingInProgress" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span v-else>Delete</span>
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
    name: 'ProductsIndex',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            products: [],
            categories: [],
            units: [],
            loading: false,
            error: null,
            filters: {
                search: '',
                category_id: '',
                unit_id: '',
                status: '',
                stock_status: '',
                perPage: 10,
                sortBy: 'created_at',
                sortOrder: 'desc',
            },
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 10,
                total: 0,
                from: 0,
                to: 0,
            },
            statusLoading: {},
            deleteLoading: {},
            showDeleteModal: false,
            productToDelete: null,
            deletingInProgress: false,
            searchTimeout: null,
        };
    },
    computed: {
        visiblePages() {
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const delta = 2;
            const range = [];
            const rangeWithDots = [];

            for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                range.push(i);
            }

            if (current - delta > 2) {
                rangeWithDots.push(1, '...');
            } else {
                rangeWithDots.push(1);
            }

            rangeWithDots.push(...range);

            if (current + delta < last - 1) {
                rangeWithDots.push('...', last);
            } else if (last !== 1) {
                rangeWithDots.push(last);
            }

            return rangeWithDots;
        },
    },
    mounted() {
        this.fetchFormData();
        this.fetchProducts();
    },
    methods: {
        async fetchFormData() {
            try {
                const response = await axios.get('/api/products/form-data');
                if (response.data.success) {
                    this.categories = response.data.data.categories || [];
                    this.units = response.data.data.units || [];
                }
            } catch (err) {
                console.error('Error fetching form data:', err);
            }
        },
        async fetchProducts() {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    page: this.pagination.current_page,
                    per_page: this.filters.perPage,
                    search: this.filters.search,
                    category_id: this.filters.category_id,
                    unit_id: this.filters.unit_id,
                    status: this.filters.status,
                    stock_status: this.filters.stock_status,
                    sort_by: this.filters.sortBy,
                    sort_order: this.filters.sortOrder,
                };

                const response = await axios.get('/api/products', { params });

                if (response.data.success) {
                    this.products = response.data.data.data;
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total,
                        from: response.data.data.from,
                        to: response.data.data.to,
                    };
                } else {
                    this.error = response.data.message || 'Failed to load products';
                }
            } catch (err) {
                console.error('Error fetching products:', err);
                this.error = err.response?.data?.message || 'An error occurred while loading products';
            } finally {
                this.loading = false;
            }
        },
        debouncedSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.pagination.current_page = 1;
                this.fetchProducts();
            }, 500);
        },
        sort(field) {
            if (this.filters.sortBy === field) {
                this.filters.sortOrder = this.filters.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sortBy = field;
                this.filters.sortOrder = 'asc';
            }
            this.fetchProducts();
        },
        getSortIcon(field) {
            if (this.filters.sortBy !== field) {
                return 'fas fa-sort';
            }
            return this.filters.sortOrder === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== '...') {
                this.pagination.current_page = page;
                this.fetchProducts();
            }
        },
        async toggleStatus(product) {
            this.statusLoading[product.id] = true;

            try {
                const response = await axios.patch(`/api/products/${product.id}/toggle-status`);

                if (response.data.success) {
                    product.is_active = !product.is_active;
                    this.toast.success(response.data.message || 'Status updated successfully');
                } else {
                    this.toast.error(response.data.message || 'Failed to update status');
                }
            } catch (err) {
                console.error('Error toggling status:', err);
                this.toast.error(err.response?.data?.message || 'Failed to update status');
            } finally {
                delete this.statusLoading[product.id];
            }
        },
        confirmDelete(product) {
            this.productToDelete = product;
            this.showDeleteModal = true;
        },
        async deleteProduct() {
            if (!this.productToDelete) return;

            this.deletingInProgress = true;

            try {
                const response = await axios.delete(`/api/products/${this.productToDelete.id}`);

                if (response.data.success) {
                    this.toast.success(response.data.message || 'Product deleted successfully');
                    this.showDeleteModal = false;
                    this.productToDelete = null;
                    this.fetchProducts();
                } else {
                    this.toast.error(response.data.message || 'Failed to delete product');
                }
            } catch (err) {
                console.error('Error deleting product:', err);
                this.toast.error(err.response?.data?.message || 'Failed to delete product');
            } finally {
                this.deletingInProgress = false;
            }
        },
        formatPrice(price) {
            if (!price) return '$0.00';
            return `$${parseFloat(price).toFixed(2)}`;
        },
    },
};
</script>
