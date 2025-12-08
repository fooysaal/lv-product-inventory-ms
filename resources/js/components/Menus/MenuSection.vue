<template>
    <div class="pt-4">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            {{ title }}
        </h3>
        <div class="mt-2 space-y-1">
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true
    }
});
</script>
