<template>
    <div class="p-8 max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">{{ isEdit ? 'Edit Category' : 'Create Category' }}</h1>
            <p class="text-gray-600 mt-1">{{ isEdit ? 'Update category information' : 'Add a new product category' }}</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="w-12 h-12 border-3 border-gray-200 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading...</p>
        </div>

        <!-- Form -->
        <form v-else @submit.prevent="submitForm" class="bg-white rounded-lg shadow-sm p-6">
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Enter category name"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        :class="{'border-red-500': errors.name, 'border-gray-300': !errors.name}"
                        required
                    />
                    <p v-if="errors.name" class="mt-1.5 text-xs text-red-600">
                        {{ errors.name[0] }}
                    </p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Enter category description"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        :class="{'border-red-500': errors.description, 'border-gray-300': !errors.description}"
                    ></textarea>
                    <p v-if="errors.description" class="mt-1.5 text-xs text-red-600">
                        {{ errors.description[0] }}
                    </p>
                </div>

                <!-- Parent Category -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Parent Category
                    </label>
                    <select
                        v-model="form.parent_id"
                        class="w-full px-4 py-2.5 border rounded-lg bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{'border-red-500': errors.parent_id, 'border-gray-300': !errors.parent_id}"
                    >
                        <option value="">None (Root Category)</option>
                        <optgroup v-for="cat in rootCategories" :key="cat.id" :label="cat.name">
                            <option :value="cat.id">{{ cat.name }}</option>
                            <option
                                v-for="child in cat.children"
                                :key="child.id"
                                :value="child.id"
                                :disabled="isEdit && child.id === parseInt($route.params.id)"
                            >
                                &nbsp;&nbsp;└─ {{ child.name }}
                            </option>
                        </optgroup>
                    </select>
                    <p v-if="errors.parent_id" class="mt-1.5 text-xs text-red-600">
                        {{ errors.parent_id[0] }}
                    </p>
                    <p class="mt-1.5 text-xs text-gray-500">
                        Leave empty to create a root category
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
                        Inactive categories won't be available for product selection
                    </p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                <button
                    type="button"
                    @click="$router.push('/categories')"
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
                    <span v-else>{{ isEdit ? 'Update Category' : 'Create Category' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export default {
    name: 'CategoriesForm',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            form: {
                name: '',
                description: '',
                parent_id: '',
                is_active: true,
            },
            rootCategories: [],
            loading: false,
            submitting: false,
            errors: {},
            isEdit: false,
        };
    },
    mounted() {
        this.isEdit = !!this.$route.params.id;
        this.fetchAllCategories();
        if (this.isEdit) {
            this.fetchCategory();
        }
    },
    methods: {
        async fetchAllCategories() {
            try {
                const response = await axios.get('/api/categories/all');
                if (response.data.success) {
                    this.rootCategories = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching categories:', err);
            }
        },
        async fetchCategory() {
            this.loading = true;

            try {
                const response = await axios.get(`/api/categories/${this.$route.params.id}`);

                if (response.data.success) {
                    const category = response.data.data;
                    this.form = {
                        name: category.name,
                        description: category.description || '',
                        parent_id: category.parent_id || '',
                        is_active: category.is_active,
                    };
                } else {
                    this.toast.error(response.data.message || 'Failed to load category');
                    this.$router.push('/categories');
                }
            } catch (err) {
                console.error('Error fetching category:', err);
                this.toast.error(err.response?.data?.message || 'Failed to load category');
                this.$router.push('/categories');
            } finally {
                this.loading = false;
            }
        },
        async submitForm() {
            this.submitting = true;
            this.errors = {};

            try {
                const url = this.isEdit
                    ? `/api/categories/${this.$route.params.id}`
                    : '/api/categories';

                const method = this.isEdit ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                if (response.data.success) {
                    this.toast.success(response.data.message || `Category ${this.isEdit ? 'updated' : 'created'} successfully`);
                    this.$router.push('/categories');
                } else {
                    this.toast.error(response.data.message || `Failed to ${this.isEdit ? 'update' : 'create'} category`);
                }
            } catch (err) {
                console.error('Error submitting form:', err);

                if (err.response?.status === 422 && err.response?.data?.errors) {
                    this.errors = err.response.data.errors;
                    this.toast.error('Please fix the validation errors');
                } else {
                    this.toast.error(err.response?.data?.message || `Failed to ${this.isEdit ? 'update' : 'create'} category`);
                }
            } finally {
                this.submitting = false;
            }
        },
    },
};
</script>
