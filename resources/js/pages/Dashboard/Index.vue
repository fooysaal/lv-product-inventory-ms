<template>
    <div class="p-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400">Welcome to the Product Inventory System</p>

        <div class="mt-6">
            <button
                @click="handleLogout"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
            >
                Logout
            </button>
        </div>
    </div>
</template>

<script setup>
import { useAuth } from '@/composables/useAuth';

const { logout } = useAuth();

const handleLogout = async () => {
    await logout();
};
</script>
