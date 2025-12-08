<template>
    <div class="flex-1 p-6 overflow-y-auto">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                <router-link to="/warehouses" class="hover:text-blue-600">Warehouses</router-link>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 font-medium">{{ isEditMode ? 'Edit Warehouse' : 'Create Warehouse' }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">
                {{ isEditMode ? 'Edit Warehouse' : 'Create New Warehouse' }}
            </h1>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form @submit.prevent="submitForm">
                <!-- Basic Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Basic Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Warehouse Name -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Warehouse Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Enter warehouse name"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.name, 'border-gray-300': !errors.name}"
                                required
                            />
                            <p v-if="errors.name" class="mt-1.5 text-xs text-red-600">{{ errors.name[0] }}</p>
                        </div>

                        <!-- Warehouse Code -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Warehouse Code <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.code"
                                type="text"
                                placeholder="e.g., WH-001"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.code, 'border-gray-300': !errors.code}"
                                required
                            />
                            <p v-if="errors.code" class="mt-1.5 text-xs text-red-600">{{ errors.code[0] }}</p>
                        </div>

                        <!-- Manager -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Warehouse Manager
                            </label>
                            <select
                                v-model="form.manager_id"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.manager_id, 'border-gray-300': !errors.manager_id}"
                            >
                                <option value="">Select Manager</option>
                                <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                                    {{ manager.name }} - {{ manager.email }}
                                </option>
                            </select>
                            <p v-if="errors.manager_id" class="mt-1.5 text-xs text-red-600">{{ errors.manager_id[0] }}</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Status
                            </label>
                            <select
                                v-model="form.is_active"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                            >
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Location Details
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Address -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Address
                            </label>
                            <textarea
                                v-model="form.address"
                                rows="3"
                                placeholder="Enter complete address"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.address, 'border-gray-300': !errors.address}"
                            ></textarea>
                            <p v-if="errors.address" class="mt-1.5 text-xs text-red-600">{{ errors.address[0] }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- City -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    City
                                </label>
                                <input
                                    v-model="form.city"
                                    type="text"
                                    placeholder="Enter city"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                />
                            </div>

                            <!-- State -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    State/Province
                                </label>
                                <input
                                    v-model="form.state"
                                    type="text"
                                    placeholder="Enter state/province"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                />
                            </div>

                            <!-- Country -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Country <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.country"
                                    type="text"
                                    placeholder="Enter country"
                                    class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                    :class="{'border-red-500': errors.country, 'border-gray-300': !errors.country}"
                                    required
                                />
                                <p v-if="errors.country" class="mt-1.5 text-xs text-red-600">{{ errors.country[0] }}</p>
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Postal Code
                                </label>
                                <input
                                    v-model="form.postal_code"
                                    type="text"
                                    placeholder="Enter postal code"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Contact Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Phone Number
                            </label>
                            <input
                                v-model="form.phone"
                                type="text"
                                placeholder="Enter phone number"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.phone, 'border-gray-300': !errors.phone}"
                            />
                            <p v-if="errors.phone" class="mt-1.5 text-xs text-red-600">{{ errors.phone[0] }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="Enter email address"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.email, 'border-gray-300': !errors.email}"
                            />
                            <p v-if="errors.email" class="mt-1.5 text-xs text-red-600">{{ errors.email[0] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Capacity Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Capacity Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Capacity -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Total Capacity
                            </label>
                            <input
                                v-model="form.capacity"
                                type="number"
                                step="0.01"
                                placeholder="Enter capacity"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.capacity, 'border-gray-300': !errors.capacity}"
                            />
                            <p v-if="errors.capacity" class="mt-1.5 text-xs text-red-600">{{ errors.capacity[0] }}</p>
                        </div>

                        <!-- Capacity Unit -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Capacity Unit
                            </label>
                            <select
                                v-model="form.capacity_unit"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                            >
                                <option value="">Select Unit</option>
                                <option value="sq ft">Square Feet (sq ft)</option>
                                <option value="sq m">Square Meters (sq m)</option>
                                <option value="cubic ft">Cubic Feet (cubic ft)</option>
                                <option value="cubic m">Cubic Meters (cubic m)</option>
                                <option value="pallets">Pallets</option>
                                <option value="shelves">Shelves</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                    <router-link
                        to="/warehouses"
                        class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </router-link>
                    <button
                        type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="submitting"
                    >
                        <span v-if="submitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span v-else>{{ isEditMode ? 'Update Warehouse' : 'Create Warehouse' }}</span>
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
    name: 'WarehouseForm',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            form: {
                name: '',
                code: '',
                address: '',
                city: '',
                state: '',
                country: 'Bangladesh',
                postal_code: '',
                phone: '',
                email: '',
                manager_id: '',
                capacity: '',
                capacity_unit: '',
                is_active: true
            },
            managers: [],
            errors: {},
            submitting: false,
            loading: false
        };
    },
    computed: {
        isEditMode() {
            return !!this.$route.params.id;
        }
    },
    mounted() {
        this.fetchManagers();
        if (this.isEditMode) {
            this.fetchWarehouse();
        }
    },
    methods: {
        async fetchManagers() {
            try {
                const response = await axios.get('/api/warehouses/managers');
                if (response.data.success) {
                    this.managers = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching managers:', err);
            }
        },
        async fetchWarehouse() {
            this.loading = true;
            try {
                const response = await axios.get(`/api/warehouses/${this.$route.params.id}`);

                if (response.data.success) {
                    const warehouse = response.data.data;
                    this.form = {
                        name: warehouse.name,
                        code: warehouse.code,
                        address: warehouse.address || '',
                        city: warehouse.city || '',
                        state: warehouse.state || '',
                        country: warehouse.country || 'Bangladesh',
                        postal_code: warehouse.postal_code || '',
                        phone: warehouse.phone || '',
                        email: warehouse.email || '',
                        manager_id: warehouse.manager_id || '',
                        capacity: warehouse.capacity || '',
                        capacity_unit: warehouse.capacity_unit || '',
                        is_active: warehouse.is_active
                    };
                }
            } catch (err) {
                console.error('Error fetching warehouse:', err);
                this.toast.error(err.response?.data?.message || 'Failed to load warehouse');
                this.$router.push('/warehouses');
            } finally {
                this.loading = false;
            }
        },
        async submitForm() {
            this.submitting = true;
            this.errors = {};

            try {
                const url = this.isEditMode
                    ? `/api/warehouses/${this.$route.params.id}`
                    : '/api/warehouses';

                const method = this.isEditMode ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                if (response.data.success) {
                    this.toast.success(response.data.message);
                    this.$router.push('/warehouses');
                }
            } catch (err) {
                console.error('Error submitting form:', err);

                if (err.response?.status === 422 && err.response?.data?.errors) {
                    this.errors = err.response.data.errors;
                    this.toast.error('Please fix the validation errors');
                } else {
                    this.toast.error(err.response?.data?.message || 'Failed to save warehouse');
                }
            } finally {
                this.submitting = false;
            }
        }
    }
};
</script>
