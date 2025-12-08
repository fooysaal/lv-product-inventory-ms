<template>
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Recent Stock Movements
            </h3>
        </div>
        <div class="p-6">
            <div v-if="movements.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                No stock movements found
            </div>
            <div v-else class="flow-root">
                <ul role="list" class="-mb-8">
                    <li v-for="(movement, index) in movements" :key="movement.id">
                        <div class="relative pb-8">
                            <span v-if="index !== movements.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                            <div class="relative flex space-x-3">
                                <div>
                                    <span :class="[
                                        'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800',
                                        movement.type === 'in' ? 'bg-green-500' : 'bg-red-500'
                                    ]">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="movement.type === 'in'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4" />
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16V4m0 0l4 4m-4-4l-4 4" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-900 dark:text-white font-medium">
                                            {{ movement.product }}
                                            <span :class="[
                                                'ml-2 px-2 py-0.5 text-xs rounded-full',
                                                movement.type === 'in' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                            ]">
                                                {{ movement.type === 'in' ? 'IN' : 'OUT' }}
                                            </span>
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ movement.warehouse }} • {{ movement.quantity }} units • {{ movement.reference }}
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            by {{ movement.created_by }}
                                        </p>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                        <time :datetime="movement.date">{{ formatDate(movement.date) }}</time>
                                        <div>
                                            <span :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                                getStatusClass(movement.status)
                                            ]">
                                                {{ movement.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    movements: {
        type: Array,
        default: () => []
    }
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    return date.toLocaleDateString();
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'approved': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'rejected': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};
</script>
