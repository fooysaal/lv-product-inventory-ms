<template>
    <aside
        :class="[
            'fixed top-0 left-0 z-40 h-screen transition-transform',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        ]"
        class="w-64"
    >
        <div class="h-full px-3 py-4 overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
            <!-- Logo Section -->
            <div class="flex items-center justify-between mb-6 px-3">
                <router-link to="/dashboard" class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">
                        Pro Inventory
                    </span>
                </router-link>

                <!-- Mobile close button -->
                <button
                    @click="closeSidebar"
                    class="md:hidden p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Dynamic Menu Component Based on User Type -->
            <component :is="menuComponent" v-if="menuComponent" />

            <!-- Fallback if no menu component -->
            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                <p>No menu available</p>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { ref, computed, defineAsyncComponent, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';

const { user, initAuth } = useAuth();

const props = defineProps({
    sidebarOpen: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close']);

const closeSidebar = () => {
    emit('close');
};

// Initialize auth on mount
onMounted(async () => {
    await initAuth();
});

// Dynamically load menu component based on user type
const menuComponent = computed(() => {
    if (!user.value || !user.value.user_type) {
        return null;
    }

    const userTypeSlug = user.value.user_type.slug;

    // Map user types to their menu components
    const menuComponents = {
        'admin': defineAsyncComponent(() => import('@/components/Menus/AdminMenu.vue')),
        'stock_manager': defineAsyncComponent(() => import('@/components/Menus/StockManagerMenu.vue')),
        'stock_executive': defineAsyncComponent(() => import('@/components/Menus/StockExecutiveMenu.vue'))
    };

    return menuComponents[userTypeSlug] || null;
});
</script>
