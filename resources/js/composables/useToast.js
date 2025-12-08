import { ref } from 'vue';

const toastInstance = ref(null);

export const useToast = () => {
    const success = (message, title = null, duration = 3000) => {
        toastInstance.value?.success(message, title, duration);
    };

    const error = (message, title = null, duration = 5000) => {
        toastInstance.value?.error(message, title, duration);
    };

    const warning = (message, title = null, duration = 4000) => {
        toastInstance.value?.warning(message, title, duration);
    };

    const info = (message, title = null, duration = 3000) => {
        toastInstance.value?.info(message, title, duration);
    };

    return {
        success,
        error,
        warning,
        info,
        setToastInstance: (instance) => {
            toastInstance.value = instance;
        },
    };
};
