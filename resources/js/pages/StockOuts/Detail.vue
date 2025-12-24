<template>
    <div class="p-8 max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                    <router-link to="/stock-outs" class="hover:text-blue-600">Stock Out</router-link>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-gray-900 font-medium">Details</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Stock Out Details</h1>
            </div>
            <div class="flex gap-2">
                <router-link
                    v-if="stockOut.status === 'pending'"
                    :to="`/stock-outs/${stockOut.id}/edit`"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    <i class="fas fa-edit mr-2"></i>Edit
                </router-link>
                <router-link
                    to="/stock-outs"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                >
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </router-link>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="w-12 h-12 border-3 border-gray-200 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading details...</p>
        </div>

        <!-- Content -->
        <div v-else-if="stockOut.id">
            <!-- Status and Reference -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Reference Number</p>
                        <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ stockOut.reference_number }}</h2>
                    </div>
                    <span :class="getStatusClass(stockOut.status)" class="px-4 py-2 text-sm font-medium rounded-full">
                        {{ stockOut.status.toUpperCase() }}
                    </span>
                </div>
            </div>

            <!-- Approval Actions (for managers) -->
            <div v-if="stockOut.status === 'pending' && canApprove" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-exclamation-circle text-yellow-600 mr-2"></i>
                    Pending Approval
                </h3>
                <p class="text-sm text-gray-700 mb-4">This stock out transaction requires your approval.</p>
                <div class="flex gap-3">
                    <button
                        @click="handleApprove"
                        class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        :disabled="submitting"
                    >
                        <i class="fas fa-check mr-2"></i>Approve
                    </button>
                    <button
                        @click="showRejectModal = true"
                        class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700"
                        :disabled="submitting"
                    >
                        <i class="fas fa-times mr-2"></i>Reject
                    </button>
                </div>
            </div>

            <!-- Rejection Info -->
            <div v-if="stockOut.status === 'rejected'" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    Rejected
                </h3>
                <p class="text-sm text-gray-700"><strong>Reason:</strong> {{ stockOut.rejection_reason }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Warehouse Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Warehouse Information
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Warehouse Name</p>
                            <p class="text-base font-medium text-gray-900">{{ stockOut.warehouse?.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Warehouse Code</p>
                            <p class="text-base font-medium text-gray-900">{{ stockOut.warehouse?.code }}</p>
                        </div>
                        <div v-if="stockOut.warehouse?.address">
                            <p class="text-sm text-gray-600">Location</p>
                            <p class="text-base font-medium text-gray-900">{{ stockOut.warehouse?.address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        Customer Information
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Customer Name</p>
                            <p class="text-base font-medium text-gray-900">{{ stockOut.customer_name }}</p>
                        </div>
                        <div v-if="stockOut.order_number">
                            <p class="text-sm text-gray-600">Order Number</p>
                            <p class="text-base font-medium text-gray-900">{{ stockOut.order_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Issued Date</p>
                            <p class="text-base font-medium text-gray-900">{{ formatDate(stockOut.issued_date) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Products ({{ stockOut.product_count }} items)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Unit</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(item, index) in stockOut.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ index + 1 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ item.product?.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ item.product?.sku }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ item.product?.unit?.short_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ formatNumber(item.quantity) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ formatNumber(item.unit_price) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">${{ formatNumber(item.total_amount) }}</td>
                            </tr>
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="6" class="px-6 py-4 text-sm text-gray-900 text-right">Grand Total:</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ formatNumber(stockOut.total_amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="stockOut.notes" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Notes</h3>
                <p class="text-gray-700 whitespace-pre-wrap">{{ stockOut.notes }}</p>
            </div>

            <!-- Audit Trail -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                    Audit Trail
                </h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Created By</p>
                        <p class="text-base font-medium text-gray-900">{{ stockOut.creator?.name || 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ formatDateTime(stockOut.created_at) }}</p>
                    </div>
                    <div v-if="stockOut.approved_by">
                        <p class="text-sm text-gray-600">{{ stockOut.status === 'approved' ? 'Approved By' : 'Rejected By' }}</p>
                        <p class="text-base font-medium text-gray-900">{{ stockOut.approver?.name || 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ formatDateTime(stockOut.approved_at) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Reject Stock Out</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                    <textarea
                        v-model="rejectionReason"
                        rows="4"
                        placeholder="Enter rejection reason..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                    ></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button
                        @click="showRejectModal = false"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmReject"
                        :disabled="!rejectionReason || submitting"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                    >
                        Reject
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
    name: 'StockOutDetail',
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            stockOut: {},
            loading: false,
            submitting: false,
            showRejectModal: false,
            rejectionReason: ''
        };
    },
    computed: {
        canApprove() {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            return user.user_type_name === 'Stock Manager' || user.user_type_name === 'Admin';
        }
    },
    mounted() {
        this.fetchStockOut();
    },
    methods: {
        async fetchStockOut() {
            this.loading = true;
            try {
                const response = await axios.get(`/api/stock-outs/${this.$route.params.id}`);
                if (response.data.success) {
                    this.stockOut = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching stock out:', err);
                this.toast.error('Failed to load stock out details');
                this.$router.push('/stock-outs');
            } finally {
                this.loading = false;
            }
        },

        getStatusClass(status) {
            const classes = {
                pending: 'bg-yellow-100 text-yellow-800',
                approved: 'bg-green-100 text-green-800',
                rejected: 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },

        formatNumber(value) {
            return parseFloat(value).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatDateTime(datetime) {
            return new Date(datetime).toLocaleString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        async handleApprove() {
            if (!confirm('Are you sure you want to approve this stock out?')) {
                return;
            }

            this.submitting = true;
            try {
                const response = await axios.post(`/api/stock-outs/${this.stockOut.id}/approve`);
                if (response.data.success) {
                    this.toast.success('Stock out approved successfully');
                    this.fetchStockOut();
                }
            } catch (err) {
                console.error('Error approving stock out:', err);
                this.toast.error(err.response?.data?.message || 'Failed to approve stock out');
            } finally {
                this.submitting = false;
            }
        },

        async confirmReject() {
            this.submitting = true;
            try {
                const response = await axios.post(`/api/stock-outs/${this.stockOut.id}/reject`, {
                    rejection_reason: this.rejectionReason
                });
                if (response.data.success) {
                    this.toast.success('Stock out rejected successfully');
                    this.showRejectModal = false;
                    this.fetchStockOut();
                }
            } catch (err) {
                console.error('Error rejecting stock out:', err);
                this.toast.error(err.response?.data?.message || 'Failed to reject stock out');
            } finally {
                this.submitting = false;
            }
        }
    }
};
</script>
