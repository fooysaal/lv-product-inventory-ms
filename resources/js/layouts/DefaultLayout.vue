<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile Sidebar Backdrop -->
        <div
            v-if="isSidebarOpen"
            @click="isSidebarOpen = false"
            class="fixed inset-0 z-30 bg-gray-900 bg-opacity-50 md:hidden"
        ></div>

        <!-- Sidebar -->
        <Sidebar
            :sidebar-open="isSidebarOpen"
            @close="isSidebarOpen = false"
        />

        <!-- Main Content Area -->
        <div class="md:pl-64 flex flex-col flex-1">
            <!-- Header -->
            <Header
                :sidebar-open="isSidebarOpen"
                @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
            />

            <!-- Page Content -->
            <main class="flex-1 pt-16">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        <router-view :key="$route.fullPath" />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import Sidebar from '@/components/Layout/Sidebar.vue';
import Header from '@/components/Layout/Header.vue';

// Sidebar state
const isSidebarOpen = ref(false);
</script>
