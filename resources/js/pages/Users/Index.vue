<template>
    <div class="p-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Users</h1>
                <p class="text-gray-600 mt-1">Manage system users and access</p>
            </div>
            <router-link
                to="/users/create"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
            >
                <i class="fas fa-plus"></i>
                <span>Add User</span>
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
                        placeholder="Search users..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                        @input="debouncedSearch"
                    />
                </div>
                <div class="flex gap-3">
                    <select
                        v-model="filters.user_type_id"
                        @change="fetchUsers"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                    >
                        <option value="">All User Types</option>
                        <option v-for="type in userTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                    <select
                        v-model="filters.status"
                        @change="fetchUsers"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select
                        v-model="filters.perPage"
                        @change="fetchUsers"
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
            <p class="mt-4 text-gray-600">Loading users...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-exclamation-triangle text-5xl text-red-500"></i>
            <p class="mt-4 text-gray-600">{{ error }}</p>
            <button @click="fetchUsers" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
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
                                Email
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Phone
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                User Type
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Activity
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
                        <tr v-if="users.length === 0">
                            <td colspan="8" class="px-4 py-12 text-center">
                                <i class="fas fa-inbox text-5xl text-gray-300 block mb-4"></i>
                                <p class="text-gray-600">No users found</p>
                            </td>
                        </tr>
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-4 py-4 text-sm font-semibold text-gray-900">{{ user.name }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ user.email }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ user.phone || 'N/A' }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-block px-3 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                    {{ user.user_type?.name || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs">
                                        <i class="fas fa-arrow-down text-green-500"></i> {{ user.created_stock_ins_count }} stock ins
                                    </span>
                                    <span class="text-xs">
                                        <i class="fas fa-arrow-up text-red-500"></i> {{ user.created_stock_outs_count }} stock outs
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ formatDate(user.created_at) }}</td>
                            <td class="px-4 py-4">
                                <button
                                    @click="toggleStatus(user)"
                                    :class="[
                                        'px-3.5 py-1.5 text-xs font-medium rounded-full border-0 cursor-pointer transition-colors',
                                        user.is_active
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                            : 'bg-red-100 text-red-700 hover:bg-red-200'
                                    ]"
                                    :disabled="statusLoading[user.id]"
                                >
                                    <span v-if="statusLoading[user.id]" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                                    <span v-else>{{ user.is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <router-link
                                        :to="`/users/${user.id}/edit`"
                                        class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-500 hover:text-white transition-colors"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </router-link>
                                    <button
                                        @click="confirmResetPassword(user)"
                                        class="w-8 h-8 flex items-center justify-center bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-500 hover:text-white transition-colors"
                                        title="Reset Password"
                                    >
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button
                                        @click="confirmDelete(user)"
                                        class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-lg hover:bg-red-500 hover:text-white transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                                        title="Delete"
                                        :disabled="deleteLoading[user.id]"
                                    >
                                        <span v-if="deleteLoading[user.id]" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
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
                        Are you sure you want to delete "<strong>{{ userToDelete?.name }}</strong>"?
                    </p>
                    <p v-if="userToDelete && (userToDelete.created_stock_ins_count > 0 || userToDelete.created_stock_outs_count > 0)" class="text-red-600 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>This user has associated stock transactions.</span>
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
                    <button @click="showDeleteModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="deleteUser"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="deletingInProgress"
                    >
                        <span v-if="deletingInProgress" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span v-else>Delete</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Reset Password Confirmation Modal -->
        <div v-if="showResetPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showResetPasswordModal = false">
            <div class="bg-white rounded-lg w-full max-w-md shadow-xl">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800">Reset Password</h3>
                    <button @click="showResetPasswordModal = false" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 mb-4">
                        Reset password for "<strong>{{ userToResetPassword?.name }}</strong>" to default password: <code class="bg-gray-100 px-2 py-1 rounded">password</code>
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
                    <button @click="showResetPasswordModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="resetPassword"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="resettingPassword"
                    >
                        <span v-if="resettingPassword" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span v-else>Reset Password</span>
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
    name: 'UsersIndex',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            users: [],
            userTypes: [],
            loading: false,
            error: null,
            filters: {
                search: '',
                user_type_id: '',
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
            userToDelete: null,
            deletingInProgress: false,
            showResetPasswordModal: false,
            userToResetPassword: null,
            resettingPassword: false,
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
        this.fetchUsers();
    },
    methods: {
        async fetchUserTypes() {
            try {
                const response = await axios.get('/api/users/user-types');
                if (response.data.success) {
                    this.userTypes = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching user types:', err);
            }
        },
        async fetchUsers() {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    page: this.pagination.current_page,
                    per_page: this.filters.perPage,
                    search: this.filters.search,
                    user_type_id: this.filters.user_type_id,
                    status: this.filters.status,
                    sort_by: this.filters.sortBy,
                    sort_order: this.filters.sortOrder,
                };

                const response = await axios.get('/api/users', { params });

                if (response.data.success) {
                    this.users = response.data.data.data;
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total,
                        from: response.data.data.from,
                        to: response.data.data.to,
                    };
                } else {
                    this.error = response.data.message || 'Failed to load users';
                }
            } catch (err) {
                console.error('Error fetching users:', err);
                this.error = err.response?.data?.message || 'An error occurred while loading users';
            } finally {
                this.loading = false;
            }
        },
        debouncedSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.pagination.current_page = 1;
                this.fetchUsers();
            }, 500);
        },
        sort(field) {
            if (this.filters.sortBy === field) {
                this.filters.sortOrder = this.filters.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sortBy = field;
                this.filters.sortOrder = 'asc';
            }
            this.fetchUsers();
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
                this.fetchUsers();
            }
        },
        async toggleStatus(user) {
            this.statusLoading[user.id] = true;

            try {
                const response = await axios.patch(`/api/users/${user.id}/toggle-status`);

                if (response.data.success) {
                    user.is_active = !user.is_active;
                    this.toast.success(response.data.message || 'Status updated successfully');
                } else {
                    this.toast.error(response.data.message || 'Failed to update status');
                }
            } catch (err) {
                console.error('Error toggling status:', err);
                this.toast.error(err.response?.data?.message || 'Failed to update status');
            } finally {
                delete this.statusLoading[user.id];
            }
        },
        confirmDelete(user) {
            this.userToDelete = user;
            this.showDeleteModal = true;
        },
        async deleteUser() {
            if (!this.userToDelete) return;

            this.deletingInProgress = true;

            try {
                const response = await axios.delete(`/api/users/${this.userToDelete.id}`);

                if (response.data.success) {
                    this.toast.success(response.data.message || 'User deleted successfully');
                    this.showDeleteModal = false;
                    this.userToDelete = null;
                    this.fetchUsers();
                } else {
                    this.toast.error(response.data.message || 'Failed to delete user');
                }
            } catch (err) {
                console.error('Error deleting user:', err);
                this.toast.error(err.response?.data?.message || 'Failed to delete user');
            } finally {
                this.deletingInProgress = false;
            }
        },
        confirmResetPassword(user) {
            this.userToResetPassword = user;
            this.showResetPasswordModal = true;
        },
        async resetPassword() {
            if (!this.userToResetPassword) return;

            this.resettingPassword = true;

            try {
                const response = await axios.post(`/api/users/${this.userToResetPassword.id}/reset-password`);

                if (response.data.success) {
                    this.toast.success(response.data.message || 'Password reset successfully');
                    this.showResetPasswordModal = false;
                    this.userToResetPassword = null;
                } else {
                    this.toast.error(response.data.message || 'Failed to reset password');
                }
            } catch (err) {
                console.error('Error resetting password:', err);
                this.toast.error(err.response?.data?.message || 'Failed to reset password');
            } finally {
                this.resettingPassword = false;
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
