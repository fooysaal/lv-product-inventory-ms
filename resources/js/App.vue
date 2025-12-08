<template>
    <AppLayout />
    <Toast ref="toastRef" />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Toast from '@/components/Toast/Toast.vue';
import { useToast } from '@/composables/useToast';

const toastRef = ref(null);
const { setToastInstance } = useToast();

onMounted(() => {
    setToastInstance(toastRef.value);
});
</script>
