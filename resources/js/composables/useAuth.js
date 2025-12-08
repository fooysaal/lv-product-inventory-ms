import { ref, reactive } from 'vue';
import { apiService } from '@/services/api';

// Shared state across all instances
const storedUser = localStorage.getItem('user');
const user = ref(storedUser ? JSON.parse(storedUser) : null);
const token = ref(localStorage.getItem('auth_token') || null);

export function useAuth() {
    const form = reactive({
        email: '',
        password: '',
        remember: false
    });

    const errors = reactive({
        email: '',
        password: '',
        general: ''
    });

    const showPassword = ref(false);
    const loading = ref(false);

    /**
     * Validate form inputs
     */
    const validateForm = () => {
        let isValid = true;

        // Reset errors
        errors.email = '';
        errors.password = '';
        errors.general = '';

        // Email validation
        if (!form.email) {
            errors.email = 'Email is required';
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
            errors.email = 'Please enter a valid email address';
            isValid = false;
        }

        // Password validation
        if (!form.password) {
            errors.password = 'Password is required';
            isValid = false;
        } else if (form.password.length < 6) {
            errors.password = 'Password must be at least 6 characters';
            isValid = false;
        }

        return isValid;
    };

    /**
     * Login user
     */
    const login = async () => {
        if (!validateForm()) {
            return false;
        }

        loading.value = true;

        try {
            const response = await apiService.post('/auth/login', {
                email: form.email,
                password: form.password,
                remember: form.remember
            });

            if (response.data.success) {
                // Store token and user data
                token.value = response.data.data.token;
                user.value = response.data.data.user;

                localStorage.setItem('auth_token', response.data.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.data.user));

                // Redirect to dashboard
                window.location.href = '/dashboard';
                return true;
            }
        } catch (error) {
            handleError(error);
            return false;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Register new user
     */
    const register = async (userData) => {
        loading.value = true;

        try {
            const response = await apiService.post('/auth/register', userData);

            if (response.data.success) {
                token.value = response.data.data.token;
                user.value = response.data.data.user;

                localStorage.setItem('auth_token', response.data.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.data.user));

                return true;
            }
        } catch (error) {
            handleError(error);
            return false;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Logout user
     */
    const logout = async () => {
        try {
            await apiService.post('/auth/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // Clear local storage regardless of API response
            token.value = null;
            user.value = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            window.location.href = '/';
        }
    };

    /**
     * Get user profile
     */
    const getProfile = async () => {
        try {
            const response = await apiService.get('/auth/profile');

            if (response.data.success) {
                user.value = response.data.data.user;
                localStorage.setItem('user', JSON.stringify(response.data.data.user));
                return response.data.data.user;
            }
        } catch (error) {
            console.error('Get profile error:', error);
            return null;
        }
    };

    /**
     * Initialize auth - fetch user profile if token exists but user data is incomplete
     */
    const initAuth = async () => {
        if (token.value && (!user.value || !user.value.user_type)) {
            await getProfile();
        }
    };

    /**
     * Check if user is authenticated
     */
    const isAuthenticated = () => {
        return !!token.value;
    };

    /**
     * Handle API errors
     */
    const handleError = (error) => {
        if (error.response) {
            // Handle validation errors
            if (error.response.status === 422) {
                const apiErrors = error.response.data.errors;
                if (apiErrors) {
                    errors.email = apiErrors.email?.[0] || '';
                    errors.password = apiErrors.password?.[0] || '';
                }
                errors.general = error.response.data.message || 'Invalid credentials.';
            } else if (error.response.status === 401) {
                errors.general = 'Invalid email or password.';
            } else {
                errors.general = error.response.data.message || 'An error occurred. Please try again.';
            }
        } else {
            errors.general = 'Network error. Please check your connection and try again.';
        }
        console.error('Auth error:', error);
    };

    /**
     * Reset form
     */
    const resetForm = () => {
        form.email = '';
        form.password = '';
        form.remember = false;
        errors.email = '';
        errors.password = '';
        errors.general = '';
    };

    return {
        form,
        errors,
        showPassword,
        loading,
        user,
        token,
        login,
        register,
        logout,
        getProfile,
        initAuth,
        isAuthenticated,
        resetForm
    };
}
