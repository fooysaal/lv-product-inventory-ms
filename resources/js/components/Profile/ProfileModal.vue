<template>
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="handleClose"
        >
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-if="show"
                    class="bg-white rounded-lg w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto"
                >
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 sticky top-0 bg-white z-10">
                        <h2 class="text-2xl font-bold text-gray-800">Update Profile Info</h2>
                        <button
                            @click="handleClose"
                            class="w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="p-6">
                        <!-- Tabs -->
                        <div class="flex gap-2 mb-6 border-b border-gray-200">
                            <button
                                @click="activeTab = 'profile'"
                                :class="[
                                    'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'profile'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                <i class="fas fa-user mr-2"></i>
                                Profile Information
                            </button>
                            <button
                                @click="activeTab = 'password'"
                                :class="[
                                    'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'password'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                <i class="fas fa-lock mr-2"></i>
                                Change Password
                            </button>
                        </div>

                        <!-- Profile Tab -->
                        <div v-show="activeTab === 'profile'">
                            <form @submit.prevent="updateProfile">
                                <!-- User Avatar -->
                                <div class="flex justify-center mb-6">
                                    <div class="relative">
                                        <div class="flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full text-3xl font-bold text-white shadow-lg">
                                            {{ userInitials }}
                                        </div>
                                        <div class="absolute bottom-0 right-0 w-8 h-8 bg-green-500 border-4 border-white rounded-full"></div>
                                    </div>
                                </div>

                                <!-- User Role Badge -->
                                <div class="flex justify-center mb-6">
                                    <span class="inline-block px-4 py-2 text-sm font-medium bg-indigo-100 text-indigo-800 rounded-full">
                                        <i class="fas fa-user-tag mr-2"></i>
                                        {{ user?.user_type?.name || 'User' }}
                                    </span>
                                </div>

                                <div class="space-y-4">
                                    <!-- Name -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Full Name <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="profileForm.name"
                                            type="text"
                                            placeholder="Enter your full name"
                                            class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700"
                                            :class="{'border-red-500': profileErrors.name, 'border-gray-300': !profileErrors.name}"
                                            required
                                        />
                                        <p v-if="profileErrors.name" class="mt-1.5 text-xs text-red-600">
                                            {{ profileErrors.name[0] }}
                                        </p>
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Email Address <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="profileForm.email"
                                            type="email"
                                            placeholder="Enter your email"
                                            class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700"
                                            :class="{'border-red-500': profileErrors.email, 'border-gray-300': !profileErrors.email}"
                                            required
                                        />
                                        <p v-if="profileErrors.email" class="mt-1.5 text-xs text-red-600">
                                            {{ profileErrors.email[0] }}
                                        </p>
                                    </div>

                                    <!-- Phone -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Phone Number
                                        </label>
                                        <input
                                            v-model="profileForm.phone"
                                            type="text"
                                            placeholder="Enter your phone number"
                                            class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700"
                                            :class="{'border-red-500': profileErrors.phone, 'border-gray-300': !profileErrors.phone}"
                                        />
                                        <p v-if="profileErrors.phone" class="mt-1.5 text-xs text-red-600">
                                            {{ profileErrors.phone[0] }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Profile Actions -->
                                <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                                    <button
                                        type="button"
                                        @click="handleClose"
                                        class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                                        :disabled="updatingProfile"
                                    >
                                        <span v-if="updatingProfile" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                        <span v-else>Update Profile</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Password Tab -->
                        <div v-show="activeTab === 'password'">
                            <form @submit.prevent="updatePassword">
                                <!-- Password Info -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5"></i>
                                        <div>
                                            <h4 class="font-semibold text-blue-800 mb-1">Password Requirements</h4>
                                            <ul class="text-sm text-blue-700 space-y-1">
                                                <li>• Minimum 8 characters long</li>
                                                <li>• Must be different from current password</li>
                                                <li>• Use a strong and unique password</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <!-- Current Password -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Current Password <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="passwordForm.current_password"
                                                :type="showCurrentPassword ? 'text' : 'password'"
                                                placeholder="Enter current password"
                                                class="w-full px-4 py-2.5 pr-12 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700"
                                                :class="{'border-red-500': passwordErrors.current_password, 'border-gray-300': !passwordErrors.current_password}"
                                                required
                                            />
                                            <button
                                                type="button"
                                                @click="showCurrentPassword = !showCurrentPassword"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            >
                                                <i :class="showCurrentPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                            </button>
                                        </div>
                                        <p v-if="passwordErrors.current_password" class="mt-1.5 text-xs text-red-600">
                                            {{ passwordErrors.current_password[0] }}
                                        </p>
                                    </div>

                                    <!-- New Password -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            New Password <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="passwordForm.new_password"
                                                :type="showNewPassword ? 'text' : 'password'"
                                                placeholder="Enter new password"
                                                class="w-full px-4 py-2.5 pr-12 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700"
                                                :class="{'border-red-500': passwordErrors.new_password, 'border-gray-300': !passwordErrors.new_password}"
                                                required
                                            />
                                            <button
                                                type="button"
                                                @click="showNewPassword = !showNewPassword"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            >
                                                <i :class="showNewPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                            </button>
                                        </div>
                                        <p v-if="passwordErrors.new_password" class="mt-1.5 text-xs text-red-600">
                                            {{ passwordErrors.new_password[0] }}
                                        </p>
                                    </div>

                                    <!-- Confirm New Password -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Confirm New Password <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="passwordForm.new_password_confirmation"
                                                :type="showConfirmPassword ? 'text' : 'password'"
                                                placeholder="Confirm new password"
                                                class="w-full px-4 py-2.5 pr-12 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700"
                                                :class="{'border-red-500': passwordErrors.new_password_confirmation, 'border-gray-300': !passwordErrors.new_password_confirmation}"
                                                required
                                            />
                                            <button
                                                type="button"
                                                @click="showConfirmPassword = !showConfirmPassword"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            >
                                                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                            </button>
                                        </div>
                                        <p v-if="passwordForm.new_password && passwordForm.new_password_confirmation && passwordForm.new_password !== passwordForm.new_password_confirmation" class="mt-1.5 text-xs text-red-600">
                                            Passwords do not match
                                        </p>
                                    </div>
                                </div>

                                <!-- Password Actions -->
                                <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                                    <button
                                        type="button"
                                        @click="resetPasswordForm"
                                        class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                                    >
                                        Reset
                                    </button>
                                    <button
                                        type="submit"
                                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                                        :disabled="updatingPassword || passwordForm.new_password !== passwordForm.new_password_confirmation"
                                    >
                                        <span v-if="updatingPassword" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                        <span v-else>Change Password</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>

<script>
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export default {
    name: 'ProfileModal',
    props: {
        show: {
            type: Boolean,
            default: false
        },
        user: {
            type: Object,
            default: null
        }
    },
    emits: ['close', 'updated'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            activeTab: 'profile',
            profileForm: {
                name: '',
                email: '',
                phone: ''
            },
            passwordForm: {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            },
            profileErrors: {},
            passwordErrors: {},
            updatingProfile: false,
            updatingPassword: false,
            showCurrentPassword: false,
            showNewPassword: false,
            showConfirmPassword: false
        };
    },
    computed: {
        userInitials() {
            if (!this.user?.name) return 'U';
            const names = this.user.name.split(' ');
            if (names.length >= 2) {
                return (names[0][0] + names[1][0]).toUpperCase();
            }
            return this.user.name.substring(0, 2).toUpperCase();
        }
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.loadProfile();
                this.activeTab = 'profile';
                this.resetPasswordForm();
            }
        }
    },
    methods: {
        loadProfile() {
            if (this.user) {
                this.profileForm = {
                    name: this.user.name || '',
                    email: this.user.email || '',
                    phone: this.user.phone || ''
                };
            }
        },
        async updateProfile() {
            this.updatingProfile = true;
            this.profileErrors = {};

            try {
                const response = await axios.put('/api/profile', this.profileForm);

                if (response.data.success) {
                    this.toast.success(response.data.message || 'Profile updated successfully');
                    this.$emit('updated', response.data.data);
                    // Keep modal open to show success
                } else {
                    this.toast.error(response.data.message || 'Failed to update profile');
                }
            } catch (err) {
                console.error('Error updating profile:', err);

                if (err.response?.status === 422 && err.response?.data?.errors) {
                    this.profileErrors = err.response.data.errors;
                    this.toast.error('Please fix the validation errors');
                } else {
                    this.toast.error(err.response?.data?.message || 'Failed to update profile');
                }
            } finally {
                this.updatingProfile = false;
            }
        },
        async updatePassword() {
            this.updatingPassword = true;
            this.passwordErrors = {};

            try {
                const response = await axios.put('/api/profile/password', this.passwordForm);

                if (response.data.success) {
                    this.toast.success(response.data.message || 'Password changed successfully');
                    this.resetPasswordForm();
                    // Optionally close modal after password change
                    setTimeout(() => {
                        this.handleClose();
                    }, 1500);
                } else {
                    this.toast.error(response.data.message || 'Failed to change password');
                }
            } catch (err) {
                console.error('Error updating password:', err);

                if (err.response?.status === 422 && err.response?.data?.errors) {
                    this.passwordErrors = err.response.data.errors;
                    this.toast.error(err.response.data.message || 'Please fix the validation errors');
                } else {
                    this.toast.error(err.response?.data?.message || 'Failed to change password');
                }
            } finally {
                this.updatingPassword = false;
            }
        },
        resetPasswordForm() {
            this.passwordForm = {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            };
            this.passwordErrors = {};
            this.showCurrentPassword = false;
            this.showNewPassword = false;
            this.showConfirmPassword = false;
        },
        handleClose() {
            this.profileErrors = {};
            this.passwordErrors = {};
            this.resetPasswordForm();
            this.$emit('close');
        }
    }
};
</script>
