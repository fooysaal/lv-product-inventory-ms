<template>
    <div class="p-8 max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h1>
            <p class="text-gray-600 mt-1">{{ isEdit ? 'Update product information' : 'Add a new product to inventory' }}</p>
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
                        Product Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Enter product name"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        :class="{'border-red-500': errors.name, 'border-gray-300': !errors.name}"
                        required
                    />
                    <p v-if="errors.name" class="mt-1.5 text-xs text-red-600">
                        {{ errors.name[0] }}
                    </p>
                </div>

                <!-- SKU -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        SKU <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.sku"
                        type="text"
                        placeholder="Enter SKU"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        :class="{'border-red-500': errors.sku, 'border-gray-300': !errors.sku}"
                        required
                    />
                    <p v-if="errors.sku" class="mt-1.5 text-xs text-red-600">
                        {{ errors.sku[0] }}
                    </p>
                </div>

                <!-- Category and Unit Row -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.category_id"
                            class="w-full px-4 py-2.5 border rounded-lg bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{'border-red-500': errors.category_id, 'border-gray-300': !errors.category_id}"
                            required
                        >
                            <option value="">Select Category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <p v-if="errors.category_id" class="mt-1.5 text-xs text-red-600">
                            {{ errors.category_id[0] }}
                        </p>
                    </div>

                    <!-- Unit -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Unit <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.unit_id"
                            class="w-full px-4 py-2.5 border rounded-lg bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{'border-red-500': errors.unit_id, 'border-gray-300': !errors.unit_id}"
                            required
                        >
                            <option value="">Select Unit</option>
                            <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                {{ unit.name }}
                            </option>
                        </select>
                        <p v-if="errors.unit_id" class="mt-1.5 text-xs text-red-600">
                            {{ errors.unit_id[0] }}
                        </p>
                    </div>
                </div>

                <!-- Cost Price and Selling Price Row -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Cost Price -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Cost Price <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.cost_price"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                            :class="{'border-red-500': errors.cost_price, 'border-gray-300': !errors.cost_price}"
                            required
                        />
                        <p v-if="errors.cost_price" class="mt-1.5 text-xs text-red-600">
                            {{ errors.cost_price[0] }}
                        </p>
                    </div>

                    <!-- Selling Price -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Selling Price <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.selling_price"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                            :class="{'border-red-500': errors.selling_price, 'border-gray-300': !errors.selling_price}"
                            required
                        />
                        <p v-if="errors.selling_price" class="mt-1.5 text-xs text-red-600">
                            {{ errors.selling_price[0] }}
                        </p>
                    </div>
                </div>

                <!-- Min Stock Level and Max Stock Level Row -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Min Stock Level -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Min Stock Level <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.min_stock_level"
                            type="number"
                            placeholder="0"
                            class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                            :class="{'border-red-500': errors.min_stock_level, 'border-gray-300': !errors.min_stock_level}"
                            required
                        />
                        <p v-if="errors.min_stock_level" class="mt-1.5 text-xs text-red-600">
                            {{ errors.min_stock_level[0] }}
                        </p>
                    </div>

                    <!-- Max Stock Level -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Max Stock Level
                        </label>
                        <input
                            v-model="form.max_stock_level"
                            type="number"
                            placeholder="0"
                            class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                            :class="{'border-red-500': errors.max_stock_level, 'border-gray-300': !errors.max_stock_level}"
                        />
                        <p v-if="errors.max_stock_level" class="mt-1.5 text-xs text-red-600">
                            {{ errors.max_stock_level[0] }}
                        </p>
                    </div>
                </div>

                <!-- Barcode -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Barcode
                    </label>
                    <input
                        v-model="form.barcode"
                        type="text"
                        placeholder="Enter barcode"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        :class="{'border-red-500': errors.barcode, 'border-gray-300': !errors.barcode}"
                    />
                    <p v-if="errors.barcode" class="mt-1.5 text-xs text-red-600">
                        {{ errors.barcode[0] }}
                    </p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        placeholder="Enter product description"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800 resize-none"
                        :class="{'border-red-500': errors.description, 'border-gray-300': !errors.description}"
                    ></textarea>
                    <p v-if="errors.description" class="mt-1.5 text-xs text-red-600">
                        {{ errors.description[0] }}
                    </p>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Product Image
                    </label>
                    <input
                        type="file"
                        @change="handleImageChange"
                        accept="image/*"
                        class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                        :class="{'border-red-500': errors.image, 'border-gray-300': !errors.image}"
                    />
                    <p v-if="errors.image" class="mt-1.5 text-xs text-red-600">
                        {{ errors.image[0] }}
                    </p>

                    <!-- Image Preview -->
                    <div v-if="imagePreview || form.current_image" class="mt-4">
                        <div class="relative inline-block">
                            <img
                                :src="imagePreview || form.current_image"
                                alt="Product preview"
                                class="w-32 h-32 object-cover rounded-lg border border-gray-300"
                            />
                            <button
                                v-if="imagePreview || form.current_image"
                                type="button"
                                @click="removeImage"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors"
                            >
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
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
                        Inactive products will not be available for stock transactions
                    </p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                <button
                    type="button"
                    @click="$router.push('/products')"
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
                    <span v-else>{{ isEdit ? 'Update Product' : 'Create Product' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export default {
    name: 'ProductsForm',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            form: {
                name: '',
                sku: '',
                category_id: '',
                unit_id: '',
                cost_price: '',
                selling_price: '',
                min_stock_level: '',
                max_stock_level: '',
                barcode: '',
                description: '',
                is_active: true,
                current_image: null,
            },
            imageFile: null,
            imagePreview: null,
            categories: [],
            units: [],
            loading: false,
            submitting: false,
            errors: {},
            isEdit: false,
        };
    },
    mounted() {
        this.isEdit = !!this.$route.params.id;
        this.fetchFormData();
        if (this.isEdit) {
            this.fetchProduct();
        }
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
                this.toast.error('Failed to load form data');
            }
        },
        async fetchProduct() {
            this.loading = true;

            try {
                const response = await axios.get(`/api/products/${this.$route.params.id}`);

                if (response.data.success) {
                    const product = response.data.data;
                    this.form = {
                        name: product.name,
                        sku: product.sku,
                        category_id: product.category_id,
                        unit_id: product.unit_id,
                        cost_price: product.cost_price,
                        selling_price: product.selling_price,
                        min_stock_level: product.min_stock_level,
                        max_stock_level: product.max_stock_level || '',
                        barcode: product.barcode || '',
                        description: product.description || '',
                        is_active: product.is_active,
                        current_image: product.image || null,
                    };
                } else {
                    this.toast.error(response.data.message || 'Failed to load product');
                    this.$router.push('/products');
                }
            } catch (err) {
                console.error('Error fetching product:', err);
                this.toast.error(err.response?.data?.message || 'Failed to load product');
                this.$router.push('/products');
            } finally {
                this.loading = false;
            }
        },
        handleImageChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.imageFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        removeImage() {
            this.imageFile = null;
            this.imagePreview = null;
            this.form.current_image = null;
            // Clear the file input
            const fileInput = document.querySelector('input[type="file"]');
            if (fileInput) {
                fileInput.value = '';
            }
        },
        async submitForm() {
            this.submitting = true;
            this.errors = {};

            try {
                const url = this.isEdit
                    ? `/api/products/${this.$route.params.id}`
                    : '/api/products';

                let formData;
                let headers = {};

                // If there's an image file, use FormData for multipart/form-data
                if (this.imageFile) {
                    formData = new FormData();
                    formData.append('name', this.form.name);
                    formData.append('sku', this.form.sku);
                    formData.append('category_id', this.form.category_id);
                    formData.append('unit_id', this.form.unit_id);
                    formData.append('cost_price', this.form.cost_price);
                    formData.append('selling_price', this.form.selling_price);
                    formData.append('min_stock_level', this.form.min_stock_level);
                    if (this.form.max_stock_level) formData.append('max_stock_level', this.form.max_stock_level);
                    if (this.form.barcode) formData.append('barcode', this.form.barcode);
                    if (this.form.description) formData.append('description', this.form.description);
                    formData.append('is_active', this.form.is_active ? '1' : '0');
                    formData.append('image', this.imageFile);

                    if (this.isEdit) {
                        formData.append('_method', 'PUT');
                    }

                    headers = {
                        'Content-Type': 'multipart/form-data',
                    };
                } else {
                    // Otherwise, send as JSON
                    formData = {
                        name: this.form.name,
                        sku: this.form.sku,
                        category_id: this.form.category_id,
                        unit_id: this.form.unit_id,
                        cost_price: this.form.cost_price,
                        selling_price: this.form.selling_price,
                        min_stock_level: this.form.min_stock_level,
                        max_stock_level: this.form.max_stock_level || null,
                        barcode: this.form.barcode || null,
                        description: this.form.description || null,
                        is_active: this.form.is_active,
                    };
                }

                const method = this.isEdit && !this.imageFile ? 'put' : 'post';

                const response = await axios[method](url, formData, { headers });

                if (response.data.success) {
                    this.toast.success(response.data.message || `Product ${this.isEdit ? 'updated' : 'created'} successfully`);
                    this.$router.push('/products');
                } else {
                    this.toast.error(response.data.message || `Failed to ${this.isEdit ? 'update' : 'create'} product`);
                }
            } catch (err) {
                console.error('Error submitting form:', err);

                if (err.response?.status === 422 && err.response?.data?.errors) {
                    this.errors = err.response.data.errors;
                    this.toast.error('Please fix the validation errors');
                } else {
                    this.toast.error(err.response?.data?.message || `Failed to ${this.isEdit ? 'update' : 'create'} product`);
                }
            } finally {
                this.submitting = false;
            }
        },
    },
};
</script>
