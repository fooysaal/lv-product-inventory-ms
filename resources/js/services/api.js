import axios from 'axios';

// Create axios instance
const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Request interceptor to add auth token
apiClient.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor to handle errors globally
apiClient.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // Handle 401 unauthorized errors
        if (error.response && error.response.status === 401) {
            // Clear auth data
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            // Redirect to login if not already there
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }

        return Promise.reject(error);
    }
);

export const apiService = {
    /**
     * GET request
     */
    get(url, config = {}) {
        return apiClient.get(url, config);
    },

    /**
     * POST request
     */
    post(url, data = {}, config = {}) {
        return apiClient.post(url, data, config);
    },

    /**
     * PUT request
     */
    put(url, data = {}, config = {}) {
        return apiClient.put(url, data, config);
    },

    /**
     * PATCH request
     */
    patch(url, data = {}, config = {}) {
        return apiClient.patch(url, data, config);
    },

    /**
     * DELETE request
     */
    delete(url, config = {}) {
        return apiClient.delete(url, config);
    }
};

export default apiClient;
