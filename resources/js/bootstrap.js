import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add auth token to all requests
axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Global error handler for axios responses
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        // Handle 500 Internal Server Error
        if (error.response && error.response.status === 500) {
            // Store error details for debugging
            const errorDetails = error.response.data?.message || 'An unexpected error occurred';

            // Redirect to 500 error page
            window.location.href = `/500?error=${encodeURIComponent(errorDetails)}`;
        }

        // Handle 401 Unauthorized (token expired or invalid)
        if (error.response && error.response.status === 401) {
            // Clear auth data and redirect to login
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }

        return Promise.reject(error);
    }
);

