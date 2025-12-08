<template>
    <div id="app">
        <component :is="layout">
            <router-view />
        </component>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AuthLayout from '@/layouts/AuthLayout.vue';
import DefaultLayout from '@/layouts/DefaultLayout.vue';

const route = useRoute();

const layout = computed(() => {
    // If layout is explicitly set in route meta, use it
    // Otherwise, default to AuthLayout for safety (guest pages)
    return route.meta.layout || AuthLayout;
});
</script>
