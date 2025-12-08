import { useToast } from './useToast';
import router from '@/router/index';

/**
 * Composable for centralized error handling
 */
export function useErrorHandler() {
    const toast = useToast();

    /**
     * Handle API errors with appropriate user feedback
     * @param {Error} error - The error object from axios
     * @param {Object} options - Configuration options
     */
    const handleError = (error, options = {}) => {
        const {
            showToast = true,
            redirectOn500 = false,
            redirectOn404 = false,
            customMessage = null,
            logToConsole = true
        } = options;

        if (logToConsole) {
            console.error('Error occurred:', error);
        }

        // Network error (no response from server)
        if (!error.response) {
            if (showToast) {
                toast.error('Network error. Please check your internet connection.');
            }
            return {
                type: 'network',
                message: 'Network error',
                error
            };
        }

        const status = error.response.status;
        const data = error.response.data;

        // Handle different HTTP status codes
        switch (status) {
            case 400:
                // Bad Request
                if (showToast) {
                    toast.error(customMessage || data?.message || 'Invalid request. Please check your input.');
                }
                return {
                    type: 'bad_request',
                    status: 400,
                    message: data?.message,
                    errors: data?.errors,
                    error
                };

            case 401:
                // Unauthorized - handled by axios interceptor
                if (showToast) {
                    toast.error('Session expired. Please login again.');
                }
                return {
                    type: 'unauthorized',
                    status: 401,
                    message: 'Unauthorized',
                    error
                };

            case 403:
                // Forbidden
                if (showToast) {
                    toast.error(customMessage || 'You do not have permission to perform this action.');
                }
                return {
                    type: 'forbidden',
                    status: 403,
                    message: data?.message || 'Access denied',
                    error
                };

            case 404:
                // Not Found
                if (redirectOn404) {
                    router.push('/404');
                } else if (showToast) {
                    toast.error(customMessage || data?.message || 'Resource not found.');
                }
                return {
                    type: 'not_found',
                    status: 404,
                    message: data?.message || 'Not found',
                    error
                };

            case 422:
                // Validation Error
                if (showToast) {
                    const validationMessage = customMessage || 'Please fix the validation errors.';
                    toast.error(validationMessage);
                }
                return {
                    type: 'validation',
                    status: 422,
                    message: data?.message || 'Validation failed',
                    errors: data?.errors || {},
                    error
                };

            case 429:
                // Too Many Requests
                if (showToast) {
                    toast.error('Too many requests. Please slow down and try again later.');
                }
                return {
                    type: 'rate_limit',
                    status: 429,
                    message: 'Rate limit exceeded',
                    error
                };

            case 500:
                // Internal Server Error - handled by axios interceptor
                if (redirectOn500) {
                    const errorMessage = data?.message || 'Internal server error';
                    router.push(`/500?error=${encodeURIComponent(errorMessage)}`);
                } else if (showToast) {
                    toast.error(customMessage || 'Server error. Please try again later.');
                }
                return {
                    type: 'server_error',
                    status: 500,
                    message: data?.message || 'Internal server error',
                    error
                };

            case 503:
                // Service Unavailable
                if (showToast) {
                    toast.error('Service temporarily unavailable. Please try again later.');
                }
                return {
                    type: 'service_unavailable',
                    status: 503,
                    message: 'Service unavailable',
                    error
                };

            default:
                // Other errors
                if (showToast) {
                    toast.error(customMessage || data?.message || 'An unexpected error occurred.');
                }
                return {
                    type: 'unknown',
                    status,
                    message: data?.message || 'Unknown error',
                    error
                };
        }
    };

    /**
     * Extract validation errors for form display
     * @param {Object} errorResponse - Error response from handleError
     * @returns {Object} - Validation errors object
     */
    const getValidationErrors = (errorResponse) => {
        if (errorResponse?.type === 'validation' && errorResponse?.errors) {
            return errorResponse.errors;
        }
        return {};
    };

    /**
     * Check if error is a specific type
     * @param {Object} errorResponse - Error response from handleError
     * @param {string} type - Error type to check
     * @returns {boolean}
     */
    const isErrorType = (errorResponse, type) => {
        return errorResponse?.type === type;
    };

    return {
        handleError,
        getValidationErrors,
        isErrorType
    };
}
