<template>
    <div class="p-8 max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ isEdit ? 'Edit User Type' : 'Create User Type' }}</h1>
                <p class="text-gray-600 mt-1">{{ isEdit ? 'Update user type details' : 'Add a new user type' }}</p>
            </div>
            <router-link to="/user-types" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left"></i> Back to List
            </router-link>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="w-12 h-12 border-3 border-gray-200 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading user type...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="loadError" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-exclamation-triangle text-5xl text-red-500"></i>
            <p class="mt-4 text-gray-600">{{ loadError }}</p>
            <router-link to="/user-types" class="mt-4 inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                Back to List
            </router-link>
        </div>

        <!-- Form -->
        <div v-else class="bg-white rounded-lg shadow-sm p-8">
            <form @submit.prevent="handleSubmit">
                <!-- Basic Information -->
                <div class="mb-8 pb-8 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Basic Information</h2>
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                :class="[
                                    'w-full px-4 py-2.5 border rounded-lg text-sm transition-all text-gray-900',
                                    errors.name
                                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                                        : 'border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                                ]"
                                placeholder="e.g., Stock Manager"
                            />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                :class="[
                                    'w-full px-4 py-2.5 border rounded-lg text-sm transition-all resize-vertical text-gray-900',
                                    errors.description
                                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                                        : 'border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                                ]"
                                placeholder="Describe the user type and its responsibilities..."
                                rows="4"
                            ></textarea>
                            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="mb-8 pb-8 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Permissions</h2>
                    <p class="text-sm text-gray-600 mb-6">Select the modules and actions this user type can access</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="module in availableModules" :key="module.key" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center gap-3 pb-3 mb-3 border-b border-gray-200">
                                <input
                                    :id="`module-${module.key}`"
                                    type="checkbox"
                                    :checked="isModuleFullySelected(module.key)"
                                    :indeterminate.prop="isModulePartiallySelected(module.key)"
                                    @change="toggleModule(module.key)"
                                    class="w-4.5 h-4.5 cursor-pointer"
                                />
                                <label :for="`module-${module.key}`" class="flex-1 flex items-center gap-2 font-semibold text-gray-800 cursor-pointer m-0">
                                    <i :class="module.icon" class="text-blue-500"></i>
                                    {{ module.name }}
                                </label>
                            </div>
                            <div class="space-y-2">
                                <label
                                    v-for="action in module.actions"
                                    :key="action"
                                    class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-white transition-colors cursor-pointer"
                                >
                                    <input
                                        v-model="form.permissions[module.key]"
                                        type="checkbox"
                                        :value="action"
                                        class="w-4 h-4 cursor-pointer"
                                    />
                                    <span class="text-sm text-gray-700">{{ formatAction(action) }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Status</h2>
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer w-fit">
                            <input v-model="form.is_active" type="checkbox" class="sr-only peer" />
                            <div class="relative w-12 h-6 bg-gray-300 rounded-full peer peer-checked:bg-blue-500 transition-colors">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-6"></div>
                            </div>
                            <span class="font-medium text-gray-700">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                        </label>
                        <p class="mt-2 text-sm text-gray-600">Active user types can be assigned to users</p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <router-link to="/user-types" class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Cancel
                    </router-link>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="submitting"
                    >
                        <span v-if="submitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span v-else>{{ isEdit ? 'Update User Type' : 'Create User Type' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export default {
    name: 'UserTypeForm',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            isEdit: false,
            loading: false,
            loadError: null,
            submitting: false,
            form: {
                name: '',
                description: '',
                permissions: {},
                is_active: true,
            },
            errors: {},
            availableModules: [
                {
                    key: 'dashboard',
                    name: 'Dashboard',
                    icon: 'fas fa-tachometer-alt',
                    actions: ['view'],
                },
                {
                    key: 'users',
                    name: 'Users',
                    icon: 'fas fa-users',
                    actions: ['view', 'create', 'edit', 'delete'],
                },
                {
                    key: 'user_types',
                    name: 'User Types',
                    icon: 'fas fa-user-tag',
                    actions: ['view', 'create', 'edit', 'delete'],
                },
                {
                    key: 'products',
                    name: 'Products',
                    icon: 'fas fa-box',
                    actions: ['view', 'create', 'edit', 'delete'],
                },
                {
                    key: 'categories',
                    name: 'Categories',
                    icon: 'fas fa-tags',
                    actions: ['view', 'create', 'edit', 'delete'],
                },
                {
                    key: 'units',
                    name: 'Units',
                    icon: 'fas fa-ruler',
                    actions: ['view', 'create', 'edit', 'delete'],
                },
                {
                    key: 'warehouses',
                    name: 'Warehouses',
                    icon: 'fas fa-warehouse',
                    actions: ['view', 'create', 'edit', 'delete'],
                },
                // {
                //     key: 'suppliers',
                //     name: 'Suppliers',
                //     icon: 'fas fa-truck',
                //     actions: ['view', 'create', 'edit', 'delete'],
                // },
                // {
                //     key: 'customers',
                //     name: 'Customers',
                //     icon: 'fas fa-user-friends',
                //     actions: ['view', 'create', 'edit', 'delete'],
                // },
                {
                    key: 'stock_in',
                    name: 'Stock In',
                    icon: 'fas fa-arrow-down',
                    actions: ['view', 'create', 'edit', 'delete', 'approve'],
                },
                {
                    key: 'stock_out',
                    name: 'Stock Out',
                    icon: 'fas fa-arrow-up',
                    actions: ['view', 'create', 'edit', 'delete', 'approve'],
                },
                {
                    key: 'reports',
                    name: 'Reports',
                    icon: 'fas fa-chart-bar',
                    actions: ['view', 'export'],
                },
            ],
        };
    },
    mounted() {
        this.isEdit = !!this.$route.params.id;
        this.initializePermissions();

        if (this.isEdit) {
            this.fetchUserType();
        }
    },
    methods: {
        initializePermissions() {
            const permissions = {};
            this.availableModules.forEach((module) => {
                permissions[module.key] = [];
            });
            this.form.permissions = permissions;
        },
        async fetchUserType() {
            this.loading = true;
            this.loadError = null;

            try {
                const response = await axios.get(`/api/user-types/${this.$route.params.id}`);

                if (response.data.success) {
                    const userType = response.data.data;
                    this.form.name = userType.name;
                    this.form.description = userType.description || '';
                    this.form.is_active = userType.is_active;

                    if (userType.permissions) {
                        const permissions = { ...this.form.permissions };
                        Object.keys(userType.permissions).forEach((module) => {
                            permissions[module] = userType.permissions[module];
                        });
                        this.form.permissions = permissions;
                    }
                } else {
                    this.loadError = response.data.message || 'Failed to load user type';
                }
            } catch (err) {
                console.error('Error fetching user type:', err);
                this.loadError = err.response?.data?.message || 'An error occurred while loading user type';
            } finally {
                this.loading = false;
            }
        },
        async handleSubmit() {
            this.submitting = true;
            this.errors = {};

            try {
                const cleanedPermissions = {};
                Object.keys(this.form.permissions).forEach((module) => {
                    if (this.form.permissions[module].length > 0) {
                        cleanedPermissions[module] = this.form.permissions[module];
                    }
                });

                const payload = {
                    name: this.form.name,
                    description: this.form.description,
                    permissions: cleanedPermissions,
                    is_active: this.form.is_active,
                };

                let response;
                if (this.isEdit) {
                    response = await axios.put(`/api/user-types/${this.$route.params.id}`, payload);
                } else {
                    response = await axios.post('/api/user-types', payload);
                }

                if (response.data.success) {
                    this.toast.success(response.data.message || 'User type saved successfully');
                    this.$router.push('/user-types');
                } else {
                    this.toast.error(response.data.message || 'Failed to save user type');
                }
            } catch (err) {
                console.error('Error saving user type:', err);

                if (err.response?.status === 422) {
                    this.errors = err.response.data.errors || {};
                    this.toast.error('Please fix the validation errors');
                } else {
                    this.toast.error(err.response?.data?.message || 'An error occurred while saving');
                }
            } finally {
                this.submitting = false;
            }
        },
        isModuleFullySelected(moduleKey) {
            const module = this.availableModules.find((m) => m.key === moduleKey);
            if (!module) return false;

            const selectedActions = this.form.permissions[moduleKey] || [];
            return module.actions.length > 0 && selectedActions.length === module.actions.length;
        },
        isModulePartiallySelected(moduleKey) {
            const selectedActions = this.form.permissions[moduleKey] || [];
            return selectedActions.length > 0 && !this.isModuleFullySelected(moduleKey);
        },
        toggleModule(moduleKey) {
            const module = this.availableModules.find((m) => m.key === moduleKey);
            if (!module) return;

            if (this.isModuleFullySelected(moduleKey)) {
                this.form.permissions[moduleKey] = [];
            } else {
                this.form.permissions[moduleKey] = [...module.actions];
            }
        },
        formatAction(action) {
            return action.charAt(0).toUpperCase() + action.slice(1).replace('_', ' ');
        },
    },
};
</script>
