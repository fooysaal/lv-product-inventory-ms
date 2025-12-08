<template>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard
            v-for="stat in stats"
            :key="stat.label"
            :label="stat.label"
            :value="stat.value"
            :icon="stat.icon"
            :color="stat.color"
            :trend="stat.trend"
        />
    </div>
</template>

<script setup>
import StatCard from './StatCard.vue';

defineProps({
    stats: {
        type: Array,
        required: true,
        default: () => []
    }
});
</script>
