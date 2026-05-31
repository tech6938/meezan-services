/**
 * Frontend Utilities for Export Functionality
 * Provides helper functions for file downloads and export management
 */

/**
 * Download file from blob with custom filename
 * @param {Blob} blob - The file blob to download
 * @param {string} filename - The filename for the download
 */
export function downloadFile(blob, filename) {
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(link);
}

/**
 * Build query string from object
 * @param {Object} params - Parameters to convert to query string
 * @returns {string} - Query string
 */
export function buildQueryString(params) {
    const url = new URL('http://example.com');
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
            url.searchParams.append(key, params[key]);
        }
    });
    return url.search.substring(1);
}

/**
 * Format date for API query
 * @param {Date|string} date - Date to format
 * @returns {string} - Formatted date (YYYY-MM-DD)
 */
export function formatDateForAPI(date) {
    if (typeof date === 'string') {
        date = new Date(date);
    }
    return date.toISOString().split('T')[0];
}

/**
 * Get filter values from form or input
 * @param {string} formSelector - CSS selector for the form
 * @returns {Object} - Filter object with key-value pairs
 */
export function getFilterValues(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return {};

    const formData = new FormData(form);
    const filters = {};

    for (let [key, value] of formData.entries()) {
        if (value) {
            filters[key] = value;
        }
    }

    return filters;
}

/**
 * Get current page query parameters
 * @returns {Object} - Current URL search parameters as object
 */
export function getCurrentQueryParams() {
    const params = new URLSearchParams(window.location.search);
    const obj = {};

    for (let [key, value] of params.entries()) {
        obj[key] = value;
    }

    return obj;
}

/**
 * Show notification/toast message
 * @param {string} message - Message to display
 * @param {string} type - Type: 'success', 'error', 'info', 'warning'
 * @param {number} duration - Duration in milliseconds (default: 4000)
 */
export function showNotification(message, type = 'info', duration = 4000) {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duration);
}

/**
 * Create or get toast container
 * @returns {HTMLElement} - Toast container element
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

/**
 * Validate file size before upload
 * @param {File} file - File to validate
 * @param {number} maxSizeInMB - Maximum file size in MB
 * @returns {boolean} - True if valid, false otherwise
 */
export function validateFileSize(file, maxSizeInMB = 50) {
    const maxBytes = maxSizeInMB * 1024 * 1024;
    return file.size <= maxBytes;
}

/**
 * Generate filename with timestamp
 * @param {string} baseName - Base filename without extension
 * @param {string} extension - File extension (default: 'xlsx')
 * @returns {string} - Filename with timestamp
 */
export function generateFilenameWithTimestamp(baseName, extension = 'xlsx') {
    const now = new Date();
    const timestamp = now.toISOString().split('T')[0] + '_' +
                     now.getHours().toString().padStart(2, '0') + '-' +
                     now.getMinutes().toString().padStart(2, '0') + '-' +
                     now.getSeconds().toString().padStart(2, '0');

    return `${baseName}_${timestamp}.${extension}`;
}

/**
 * Debounce function for rate limiting
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} - Debounced function
 */
export function debounce(func, delay = 500) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

/**
 * Check if user has export permission
 * @param {Array} permissions - User permissions array
 * @param {string} resource - Resource name (users, providers, bookings, requests)
 * @returns {boolean} - True if user has permission
 */
export function hasExportPermission(permissions, resource) {
    return permissions && permissions.includes(`export_${resource}`);
}

/**
 * Handle API errors gracefully
 * @param {Error} error - Error object
 * @param {string} context - Context for error message
 * @returns {string} - User-friendly error message
 */
export function handleExportError(error, context = 'export') {
    console.error(`Error during ${context}:`, error);

    if (error.response) {
        // Server responded with error status
        if (error.response.status === 401) {
            return 'Unauthorized. Please log in again.';
        } else if (error.response.status === 403) {
            return 'You do not have permission to export this data.';
        } else if (error.response.status === 404) {
            return 'Resource not found.';
        } else if (error.response.status === 500) {
            return 'Server error. Please try again later.';
        }
        return error.response.data?.message || `Error: ${error.response.status}`;
    } else if (error.request) {
        return 'No response from server. Please check your connection.';
    } else {
        return error.message || 'An unknown error occurred.';
    }
}

export default {
    downloadFile,
    buildQueryString,
    formatDateForAPI,
    getFilterValues,
    getCurrentQueryParams,
    showNotification,
    validateFileSize,
    generateFilenameWithTimestamp,
    debounce,
    hasExportPermission,
    handleExportError,
};
