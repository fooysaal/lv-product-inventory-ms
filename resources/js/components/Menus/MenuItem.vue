<template>
    <router-link
        :to="to"
        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150"
        :class="[
            isActive
                ? 'bg-indigo-100 text-indigo-900'
                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
        ]"
    >
        <svg
            class="mr-3 h-5 w-5 flex-shrink-0"
            :class="[
                isActive
                    ? 'text-indigo-600'
                    : 'text-gray-400 group-hover:text-gray-500'
            ]"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="icon"
            />
        </svg>
        {{ label }}
    </router-link>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
    to: {
        type: String,
        required: true
    },
    icon: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    }
});

const route = useRoute();

const isActive = computed(() => {
    return route.path === props.to || route.path.startsWith(props.to + '/');
});
</script>
