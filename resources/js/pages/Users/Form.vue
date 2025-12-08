<template>
    <div class="p-8 max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">{{ isEdit ? 'Edit User' : 'Create User' }}</h1>
            <p class="text-gray-600 mt-1">{{ isEdit ? 'Update user information' : 'Add a new system user' }}</p>
        </div>

        <!-- Default Password Info -->
        <div v-if="!isEdit" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5"></i>
            <div>
                <h4 class="font-semibold text-blue-800 mb-1">Default Password</h4>
                <p class="text-blue-700 text-sm">
                    All new users will be created with the default password: <code class="bg-blue-100 px-2 py-1 rounded font-mono">password</code>.
                    You can reset any user's password to this default from the users list.
                </p>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="w-12 h-12 border-3 border-gray-200 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading...</p>
        </div>

        <!-- Form -->
        <form v-else @submit.prevent="submitForm" class="bg-white rounded-lg shadow-sm p-6">
            <div class="space-y-6">
                <!-- User Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        User Type <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.user_type_id"
                        class="w-full px-4 py-2.5 border rounded-lg bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{'border-red-500': errors.user_type_id, 'border-gray-300': !errors.user_type_id}"
                        required
                    >
                        <option value="">Select User Type</option>
                        <option v-for="type in userTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                    <p v-if="errors.user_type_id" class="mt-1.5 text-xs text-red-600">
                        {{ errors.user_type_id[0] }}
                    </p>
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Enter full name"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        :class="{'border-red-500': errors.name, 'border-gray-300': !errors.name}"
                        required
                    />
                    <p v-if="errors.name" class="mt-1.5 text-xs text-red-600">
                        {{ errors.name[0] }}
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="Enter email address"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        :class="{'border-red-500': errors.email, 'border-gray-300': !errors.email}"
                        required
                    />
                    <p v-if="errors.email" class="mt-1.5 text-xs text-red-600">
                        {{ errors.email[0] }}
                    </p>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Phone Number
                    </label>
                    <input
                        v-model="form.phone"
                        type="text"
                        placeholder="Enter phone number"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        :class="{'border-red-500': errors.phone, 'border-gray-300': !errors.phone}"
                    />
                    <p v-if="errors.phone" class="mt-1.5 text-xs text-red-600">
                        {{ errors.phone[0] }}
                    </p>
                </div>

                <!-- Status -->
                <div>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer"
                        />
                        <span class="text-sm font-semibold text-gray-700">Active Status</span>
                    </label>
                    <p class="mt-1.5 text-xs text-gray-500 ml-8">
                        Inactive users cannot login to the system
                    </p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                <button
                    type="button"
                    @click="$router.push('/users')"
                    class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="submitting"
                >
                    <span v-if="submitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span v-else>{{ isEdit ? 'Update User' : 'Create User' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export default {
    name: 'UsersForm',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            form: {
                user_type_id: '',
                name: '',
                email: '',
                phone: '',
                is_active: true,
            },
            userTypes: [],
            loading: false,
            submitting: false,
            errors: {},
            isEdit: false,
        };
    },
    mounted() {
        this.isEdit = !!this.$route.params.id;
        this.fetchUserTypes();
        if (this.isEdit) {
            this.fetchUser();
        }
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
                this.toast.error('Failed to load user types');
            }
        },
        async fetchUser() {
            this.loading = true;

            try {
                const response = await axios.get(`/api/users/${this.$route.params.id}`);

                if (response.data.success) {
                    const user = response.data.data;
                    this.form = {
                        user_type_id: user.user_type_id,
                        name: user.name,
                        email: user.email,
                        phone: user.phone || '',
                        is_active: user.is_active,
                    };
                } else {
                    this.toast.error(response.data.message || 'Failed to load user');
                    this.$router.push('/users');
                }
            } catch (err) {
                console.error('Error fetching user:', err);
                this.toast.error(err.response?.data?.message || 'Failed to load user');
                this.$router.push('/users');
            } finally {
                this.loading = false;
            }
        },
        async submitForm() {
            this.submitting = true;
            this.errors = {};

            try {
                const url = this.isEdit
                    ? `/api/users/${this.$route.params.id}`
                    : '/api/users';

                const method = this.isEdit ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                if (response.data.success) {
                    this.toast.success(response.data.message || `User ${this.isEdit ? 'updated' : 'created'} successfully`);
                    this.$router.push('/users');
                } else {
                    this.toast.error(response.data.message || `Failed to ${this.isEdit ? 'update' : 'create'} user`);
                }
            } catch (err) {
                console.error('Error submitting form:', err);

                if (err.response?.status === 422 && err.response?.data?.errors) {
                    this.errors = err.response.data.errors;
                    this.toast.error('Please fix the validation errors');
                } else {
                    this.toast.error(err.response?.data?.message || `Failed to ${this.isEdit ? 'update' : 'create'} user`);
                }
            } finally {
                this.submitting = false;
            }
        },
    },
};
</script>
