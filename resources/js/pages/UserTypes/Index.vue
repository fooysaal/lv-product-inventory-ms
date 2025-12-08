<template>
    <div class="p-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">User Types</h1>
                <p class="text-gray-600 mt-1">Manage user types and permissions</p>
            </div>
            <router-link
                to="/user-types/create"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
            >
                <i class="fas fa-plus"></i>
                <span>Add User Type</span>
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex gap-4 items-center">
                <div class="relative flex-1 max-w-md">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-900"></i>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search user types..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                        @input="debouncedSearch"
                    />
                </div>
                <div class="flex gap-3">
                    <select
                        v-model="filters.status"
                        @change="fetchUserTypes"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select
                        v-model="filters.perPage"
                        @change="fetchUserTypes"
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
            <p class="mt-4 text-gray-600">Loading user types...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-exclamation-triangle text-5xl text-red-500"></i>
            <p class="mt-4 text-gray-600">{{ error }}</p>
            <button @click="fetchUserTypes" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                Retry
            </button>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                @click="sort('name')"
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100"
                            >
                                <div class="flex items-center gap-1">
                                    Name
                                    <i :class="getSortIcon('name')" class="text-xs"></i>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Slug
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Description
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Users
                            </th>
                            <th
                                @click="sort('created_at')"
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100"
                            >
                                <div class="flex items-center gap-1">
                                    Created
                                    <i :class="getSortIcon('created_at')" class="text-xs"></i>
                                </div>
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
                        <tr v-if="userTypes.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <i class="fas fa-inbox text-5xl text-gray-300 block mb-4"></i>
                                <p class="text-gray-600">No user types found</p>
                            </td>
                        </tr>
                        <tr v-for="userType in userTypes" :key="userType.id" class="hover:bg-gray-50">
                            <td class="px-4 py-4 text-sm font-semibold text-gray-900">{{ userType.name }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-block px-3 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                    {{ userType.slug }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ userType.description || 'N/A' }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-block px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    {{ userType.users_count }} {{ userType.users_count === 1 ? 'user' : 'users' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ formatDate(userType.created_at) }}</td>
                            <td class="px-4 py-4">
                                <button
                                    @click="toggleStatus(userType)"
                                    :class="[
                                        'px-3.5 py-1.5 text-xs font-medium rounded-full border-0 cursor-pointer transition-colors',
                                        userType.is_active
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                            : 'bg-red-100 text-red-700 hover:bg-red-200'
                                    ]"
                                    :disabled="statusLoading[userType.id]"
                                    class:disabled="opacity-60 cursor-not-allowed"
                                >
                                    <span v-if="statusLoading[userType.id]" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                                    <span v-else>{{ userType.is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <router-link
                                        :to="`/user-types/${userType.id}/edit`"
                                        class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-500 hover:text-white transition-colors"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </router-link>
                                    <button
                                        @click="confirmDelete(userType)"
                                        class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-lg hover:bg-red-500 hover:text-white transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                                        title="Delete"
                                        :disabled="deleteLoading[userType.id]"
                                    >
                                        <span v-if="deleteLoading[userType.id]" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
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
                        Are you sure you want to delete the user type "<strong>{{ userTypeToDelete?.name }}</strong>"?
                    </p>
                    <p v-if="userTypeToDelete?.users_count > 0" class="text-red-600 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>This user type has {{ userTypeToDelete.users_count }} associated user(s).</span>
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteUserType"
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
    name: 'UserTypesIndex',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            userTypes: [],
            loading: false,
            error: null,
            filters: {
                search: '',
                status: '',
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
            userTypeToDelete: null,
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
        this.fetchUserTypes();
    },
    methods: {
        async fetchUserTypes() {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    page: this.pagination.current_page,
                    per_page: this.filters.perPage,
                    search: this.filters.search,
                    status: this.filters.status,
                    sort_by: this.filters.sortBy,
                    sort_order: this.filters.sortOrder,
                };

                const response = await axios.get('/api/user-types', { params });

                if (response.data.success) {
                    this.userTypes = response.data.data.data;
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total,
                        from: response.data.data.from,
                        to: response.data.data.to,
                    };
                } else {
                    this.error = response.data.message || 'Failed to load user types';
                }
            } catch (err) {
                console.error('Error fetching user types:', err);
                this.error = err.response?.data?.message || 'An error occurred while loading user types';
            } finally {
                this.loading = false;
            }
        },
        debouncedSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.pagination.current_page = 1;
                this.fetchUserTypes();
            }, 500);
        },
        sort(field) {
            if (this.filters.sortBy === field) {
                this.filters.sortOrder = this.filters.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sortBy = field;
                this.filters.sortOrder = 'asc';
            }
            this.fetchUserTypes();
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
                this.fetchUserTypes();
            }
        },
        async toggleStatus(userType) {
            this.statusLoading[userType.id] = true;

            try {
                const response = await axios.patch(`/api/user-types/${userType.id}/toggle-status`);

                if (response.data.success) {
                    userType.is_active = !userType.is_active;
                    this.toast.success(response.data.message || 'Status updated successfully');
                } else {
                    this.toast.error(response.data.message || 'Failed to update status');
                }
            } catch (err) {
                console.error('Error toggling status:', err);
                this.toast.error(err.response?.data?.message || 'Failed to update status');
            } finally {
                delete this.statusLoading[userType.id];
            }
        },
        confirmDelete(userType) {
            this.userTypeToDelete = userType;
            this.showDeleteModal = true;
        },
        async deleteUserType() {
            if (!this.userTypeToDelete) return;

            this.deletingInProgress = true;

            try {
                const response = await axios.delete(`/api/user-types/${this.userTypeToDelete.id}`);

                if (response.data.success) {
                    this.toast.success(response.data.message || 'User type deleted successfully');
                    this.showDeleteModal = false;
                    this.userTypeToDelete = null;
                    this.fetchUserTypes();
                } else {
                    this.toast.error(response.data.message || 'Failed to delete user type');
                }
            } catch (err) {
                console.error('Error deleting user type:', err);
                this.toast.error(err.response?.data?.message || 'Failed to delete user type');
            } finally {
                this.deletingInProgress = false;
            }
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        },
    },
};
</script>
