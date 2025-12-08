<template>
    <teleport to="body">
        <div class="fixed top-4 right-4 z-50 space-y-3 max-w-sm w-full pointer-events-none">
            <transition-group name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'pointer-events-auto rounded-lg shadow-lg p-4 flex items-start gap-3 transition-all transform',
                        getToastClass(toast.type)
                    ]"
                >
                    <div class="flex-shrink-0">
                        <i :class="getIconClass(toast.type)" class="text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p v-if="toast.title" class="font-semibold text-sm mb-1">{{ toast.title }}</p>
                        <p class="text-sm">{{ toast.message }}</p>
                    </div>
                    <button
                        @click="removeToast(toast.id)"
                        class="flex-shrink-0 text-current opacity-70 hover:opacity-100 transition-opacity"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </transition-group>
        </div>
    </teleport>
</template>

<script setup>
import { ref } from 'vue';

const toasts = ref([]);
let nextId = 0;

const addToast = (message, type = 'info', title = null, duration = 3000) => {
    const id = nextId++;
    const toast = {
        id,
        message,
        type,
        title,
    };

    toasts.value.push(toast);

    if (duration > 0) {
        setTimeout(() => {
            removeToast(id);
        }, duration);
    }

    return id;
};

const removeToast = (id) => {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
};

const getToastClass = (type) => {
    const classes = {
        success: 'bg-green-50 text-green-800 border-l-4 border-green-500',
        error: 'bg-red-50 text-red-800 border-l-4 border-red-500',
        warning: 'bg-yellow-50 text-yellow-800 border-l-4 border-yellow-500',
        info: 'bg-blue-50 text-blue-800 border-l-4 border-blue-500',
    };
    return classes[type] || classes.info;
};

const getIconClass = (type) => {
    const icons = {
        success: 'fas fa-check-circle text-green-500',
        error: 'fas fa-exclamation-circle text-red-500',
        warning: 'fas fa-exclamation-triangle text-yellow-500',
        info: 'fas fa-info-circle text-blue-500',
    };
    return icons[type] || icons.info;
};

// Expose methods to parent
defineExpose({
    addToast,
    removeToast,
    success: (message, title = null, duration = 3000) => addToast(message, 'success', title, duration),
    error: (message, title = null, duration = 5000) => addToast(message, 'error', title, duration),
    warning: (message, title = null, duration = 4000) => addToast(message, 'warning', title, duration),
    info: (message, title = null, duration = 3000) => addToast(message, 'info', title, duration),
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%) scale(0.8);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>
