<template>
    <div class="flex-1 p-6 overflow-y-auto">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                <router-link to="/stock-outs" class="hover:text-blue-600">Stock Out</router-link>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 font-medium">{{ isEditMode ? 'Edit Stock Out' : 'Create Stock Out' }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">
                {{ isEditMode ? 'Edit Stock Out' : 'Create New Stock Out' }}
            </h1>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form @submit.prevent="submitForm">
                <!-- Row 1: Warehouse & Date -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Warehouse & Date Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Warehouse -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Warehouse <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.warehouse_id"
                                @change="clearItems"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.warehouse_id, 'border-gray-300': !errors.warehouse_id}"
                                required
                                :disabled="isEditMode"
                            >
                                <option value="">Select Warehouse</option>
                                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                    {{ warehouse.name }} ({{ warehouse.code }})
                                </option>
                            </select>
                            <p v-if="errors.warehouse_id" class="mt-1.5 text-xs text-red-600">{{ errors.warehouse_id[0] }}</p>
                        </div>

                        <!-- Issued Date -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Issued Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.issued_date"
                                type="date"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.issued_date, 'border-gray-300': !errors.issued_date}"
                                required
                            />
                            <p v-if="errors.issued_date" class="mt-1.5 text-xs text-red-600">{{ errors.issued_date[0] }}</p>
                        </div>
                    </div>
                </div>


                <!-- Row 2: Customer Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Customer Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Customer Name -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Customer Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.customer_name"
                                type="text"
                                placeholder="Enter customer name"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.customer_name, 'border-gray-300': !errors.customer_name}"
                                required
                            />
                            <p v-if="errors.customer_name" class="mt-1.5 text-xs text-red-600">{{ errors.customer_name[0] }}</p>
                        </div>

                        <!-- Order Number -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Order Number
                            </label>
                            <input
                                v-model="form.order_number"
                                type="text"
                                placeholder="Enter order number"
                                class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                :class="{'border-red-500': errors.order_number, 'border-gray-300': !errors.order_number}"
                            />
                            <p v-if="errors.order_number" class="mt-1.5 text-xs text-red-600">{{ errors.order_number[0] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Products Table -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Products
                    </h3>

                    <!-- Add Product Section -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            <!-- Product Search -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                                <div class="relative">
                                    <input
                                        v-model="productSearch"
                                        @input="searchProducts"
                                        @focus="showProductDropdown = true"
                                        type="text"
                                        placeholder="Search product..."
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                        :disabled="!form.warehouse_id"
                                    />
                                    <!-- Dropdown -->
                                    <div v-if="showProductDropdown && filteredProducts.length > 0"
                                         class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                        <button
                                            v-for="product in filteredProducts"
                                            :key="product.id"
                                            type="button"
                                            @click="selectProduct(product)"
                                            class="w-full px-4 py-2.5 text-left hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
                                        >
                                            <div class="font-medium text-gray-900">{{ product.name }}</div>
                                            <div class="text-xs text-gray-600">SKU: {{ product.sku }} | Unit: {{ product.unit?.short_name }}</div>
                                        </button>
                                    </div>
                                </div>
                                 <p v-if="!form.warehouse_id" class="mt-1 text-xs text-gray-500">Select warehouse first</p>
                            </div>

                            <!-- Quantity -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <input
                                    v-model="newItem.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                    :class="{'border-red-500': newItem.quantity && newItem.available_stock && parseFloat(newItem.quantity) > parseFloat(newItem.available_stock)}"
                                    @input="calculateNewItemTotal"
                                />
                                <p v-if="newItem.available_stock !== null" class="mt-1 text-xs" :class="newItem.quantity && parseFloat(newItem.quantity) > parseFloat(newItem.available_stock) ? 'text-red-600' : 'text-blue-600'">
                                    Available: {{ formatNumber(newItem.available_stock) }}
                                </p>
                            </div>

                            <!-- Unit Price -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
                                <input
                                    v-model="newItem.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                    @input="calculateNewItemTotal"
                                />
                            </div>

                            <!-- Total -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total</label>
                                <input
                                    :value="newItemTotal"
                                    type="text"
                                    readonly
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-700"
                                />
                            </div>

                            <!-- Add Button -->
                            <div class="md:col-span-2">
                                <button
                                    type="button"
                                    @click="addProduct"
                                    :disabled="!canAddProduct"
                                    class="w-full px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
                                >
                                    <i class="fas fa-plus"></i>
                                    <span>Add</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div v-if="form.items.length > 0" class="overflow-x-auto">
                        <table class="w-full border border-gray-200 rounded-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">SKU</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Unit</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Available</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ item.product_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ item.product_sku }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ item.unit_name }}</td>
                                    <td class="px-4 py-3 text-sm text-blue-600 text-right">{{ formatNumber(item.available_stock) }}</td>
                                    <td class="px-4 py-3 text-sm text-right" :class="parseFloat(item.quantity) > parseFloat(item.available_stock) ? 'text-red-600 font-semibold' : 'text-gray-900'">
                                        {{ formatNumber(item.quantity) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">${{ formatNumber(item.unit_price) }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">${{ formatNumber(item.total) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button
                                            type="button"
                                            @click="removeProduct(index)"
                                            class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                        >
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right font-semibold text-gray-800">Grand Total:</td>
                                    <td class="px-4 py-3 text-right font-bold text-lg text-gray-900">${{ formatNumber(grandTotal) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p v-else class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <br>No products added yet. Search and add products above.
                    </p>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Additional Information
                    </h3>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Notes
                        </label>
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            placeholder="Enter any additional notes..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                        ></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                    <router-link
                        to="/stock-outs"
                        class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </router-link>
                    <button
                        type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="submitting || form.items.length === 0 || hasInsufficientStock"
                    >
                        <span v-if="submitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
                        <span>{{ isEditMode ? 'Update Stock Out' : 'Create Stock Out' }}</span>
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
    name: 'StockOutForm',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            form: {
                warehouse_id: '',
                issued_date: new Date().toISOString().split('T')[0],
                customer_name: '',
                order_number: '',
                notes: '',
                items: []
            },
            newItem: {
                product_id: '',
                product_name: '',
                product_sku: '',
                unit_name: '',
                quantity: '',
                unit_price: '',
                available_stock: null,
                total: 0
            },
            products: [],
            warehouses: [],
            filteredProducts: [],
            productSearch: '',
            showProductDropdown: false,
            errors: {},
            submitting: false,
            loading: false
        };
    },
    computed: {
        isEditMode() {
            return !!this.$route.params.id;
        },
        newItemTotal() {
            const quantity = parseFloat(this.newItem.quantity) || 0;
            const unitPrice = parseFloat(this.newItem.unit_price) || 0;
            return (quantity * unitPrice).toFixed(2);
        },
        grandTotal() {
            return this.form.items.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);
        },
        canAddProduct() {
            return this.newItem.product_id &&
                   this.newItem.quantity &&
                   parseFloat(this.newItem.quantity) > 0 &&
                   this.newItem.unit_price &&
                   parseFloat(this.newItem.unit_price) >= 0 &&
                   (this.newItem.available_stock === null || parseFloat(this.newItem.quantity) <= parseFloat(this.newItem.available_stock));
        },
        hasInsufficientStock() {
            return this.form.items.some(item => parseFloat(item.quantity) > parseFloat(item.available_stock));
        }
    },
    mounted() {
        this.fetchFormData();
        if (this.isEditMode) {
            this.fetchStockOut();
        }
        // Close dropdown when clicking outside
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        async fetchFormData() {
            try {
                const response = await axios.get('/api/stock-outs/form-data');
                if (response.data.success) {
                    this.products = response.data.data.products;
                    this.warehouses = response.data.data.warehouses;
                    this.filteredProducts = this.products;
                }
            } catch (err) {
                console.error('Error fetching form data:', err);
                this.toast.error('Failed to load form data');
            }
        },

        async fetchStockOut() {
            this.loading = true;
            try {
                const response = await axios.get(`/api/stock-outs/${this.$route.params.id}`);

                if (response.data.success) {
                    const stockOut = response.data.data;

                    // Get available stock for each product
                    const itemsWithStock = await Promise.all(
                        stockOut.items.map(async (item) => {
                            try {
                                const stockResponse = await axios.get('/api/stock-outs/available-stock', {
                                    params: {
                                        product_id: item.product_id,
                                        warehouse_id: stockOut.warehouse_id
                                    }
                                });
                                const availableStock = stockResponse.data.success ? stockResponse.data.data.available_quantity : 0;

                                return {
                                    product_id: item.product_id,
                                    product_name: item.product?.name,
                                    product_sku: item.product?.sku,
                                    unit_name: item.product?.unit?.short_name,
                                    quantity: item.quantity,
                                    unit_price: item.unit_price,
                                    available_stock: availableStock,
                                    total: item.total_amount
                                };
                            } catch (err) {
                                console.error('Error fetching stock for product:', item.product_id, err);
                                return {
                                    product_id: item.product_id,
                                    product_name: item.product?.name,
                                    product_sku: item.product?.sku,
                                    unit_name: item.product?.unit?.short_name,
                                    quantity: item.quantity,
                                    unit_price: item.unit_price,
                                    available_stock: 0,
                                    total: item.total_amount
                                };
                            }
                        })
                    );

                    this.form = {
                        warehouse_id: stockOut.warehouse_id,
                        issued_date: stockOut.issued_date,
                        customer_name: stockOut.customer_name,
                        order_number: stockOut.order_number || '',
                        notes: stockOut.notes || '',
                        items: itemsWithStock
                    };
                }
            } catch (err) {
                console.error('Error fetching stock out:', err);
                this.toast.error(err.response?.data?.message || 'Failed to load stock out');
                this.$router.push('/stock-outs');
            } finally {
                this.loading = false;
            }
        },

        searchProducts() {
            const search = this.productSearch.toLowerCase();
            this.filteredProducts = this.products.filter(product =>
                product.name.toLowerCase().includes(search) ||
                product.sku.toLowerCase().includes(search)
            );
            this.showProductDropdown = true;
        },

        async selectProduct(product) {
            this.newItem.product_id = product.id;
            this.newItem.product_name = product.name;
            this.newItem.product_sku = product.sku;
            this.newItem.unit_name = product.unit?.short_name || '';
            this.productSearch = product.name;
            this.showProductDropdown = false;

            // Check available stock
            await this.checkAvailableStock(product.id);
        },

        async checkAvailableStock(productId) {
            if (this.form.warehouse_id && productId) {
                try {
                    const response = await axios.get('/api/stock-outs/available-stock', {
                        params: {
                            product_id: productId,
                            warehouse_id: this.form.warehouse_id
                        }
                    });
                    if (response.data.success) {
                        this.newItem.available_stock = response.data.data.available_quantity;
                    }
                } catch (err) {
                    console.error('Error checking available stock:', err);
                    this.newItem.available_stock = 0;
                }
            }
        },

        calculateNewItemTotal() {
            this.newItem.total = this.newItemTotal;
        },

        addProduct() {
            if (!this.canAddProduct) {
                if (this.newItem.available_stock !== null && parseFloat(this.newItem.quantity) > parseFloat(this.newItem.available_stock)) {
                    this.toast.error('Quantity exceeds available stock');
                }
                return;
            }

            // Check if product already exists
            const exists = this.form.items.find(item => item.product_id === this.newItem.product_id);
            if (exists) {
                this.toast.error('Product already added to the list');
                return;
            }

            this.form.items.push({
                product_id: this.newItem.product_id,
                product_name: this.newItem.product_name,
                product_sku: this.newItem.product_sku,
                unit_name: this.newItem.unit_name,
                quantity: parseFloat(this.newItem.quantity),
                unit_price: parseFloat(this.newItem.unit_price),
                available_stock: this.newItem.available_stock,
                total: parseFloat(this.newItemTotal)
            });

            // Reset new item form
            this.newItem = {
                product_id: '',
                product_name: '',
                product_sku: '',
                unit_name: '',
                quantity: '',
                unit_price: '',
                available_stock: null,
                total: 0
            };
            this.productSearch = '';
            this.filteredProducts = this.products;
        },

        removeProduct(index) {
            this.form.items.splice(index, 1);
        },

        clearItems() {
            if (this.form.items.length > 0) {
                if (confirm('Changing warehouse will clear all added products. Continue?')) {
                    this.form.items = [];
                    this.newItem = {
                        product_id: '',
                        product_name: '',
                        product_sku: '',
                        unit_name: '',
                        quantity: '',
                        unit_price: '',
                        available_stock: null,
                        total: 0
                    };
                    this.productSearch = '';
                } else {
                    // Revert warehouse selection
                    this.$nextTick(() => {
                        const previousWarehouse = this.form.items[0]?.warehouse_id;
                        if (previousWarehouse) {
                            this.form.warehouse_id = previousWarehouse;
                        }
                    });
                }
            }
        },

        formatNumber(value) {
            return parseFloat(value).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        handleClickOutside(event) {
            if (!event.target.closest('.relative')) {
                this.showProductDropdown = false;
            }
        },

        async submitForm() {
            if (this.form.items.length === 0) {
                this.toast.error('Please add at least one product');
                return;
            }

            if (this.hasInsufficientStock) {
                this.toast.error('Some products have insufficient stock');
                return;
            }

            this.submitting = true;
            this.errors = {};

            try {
                const data = {
                    warehouse_id: this.form.warehouse_id,
                    customer_name: this.form.customer_name,
                    order_number: this.form.order_number,
                    issued_date: this.form.issued_date,
                    notes: this.form.notes,
                    items: this.form.items.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        unit_price: item.unit_price
                    }))
                };

                if (this.isEditMode) {
                    await axios.put(`/api/stock-outs/${this.$route.params.id}`, data);
                    this.toast.success('Stock out updated successfully');
                } else {
                    const response = await axios.post('/api/stock-outs', data);
                    this.toast.success(`Stock out created successfully with ${response.data.data.count} product(s)`);
                }

                this.$router.push('/stock-outs');
            } catch (err) {
                console.error('Error submitting form:', err);

                if (err.response?.status === 422 && err.response?.data?.errors) {
                    this.errors = err.response.data.errors;
                    const errorCount = Object.keys(this.errors).length;
                    this.toast.error(`Please fix ${errorCount} validation error${errorCount > 1 ? 's' : ''}`);
                } else {
                    this.toast.error(err.response?.data?.message || 'Failed to save stock out');
                }
            } finally {
                this.submitting = false;
            }
        }
    }
};
</script>
