import axios from 'axios';

// Configure global defaults
axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
};

// Handle session expiration (419 errors)
axios.interceptors.response.use(
    response => response,
    async (error) => {
        if (error.response?.status === 419) {
            await refreshCsrfToken();
            error.config.headers['X-CSRF-TOKEN'] = getCsrfToken();
            return axios.request(error.config);
        }
        return Promise.reject(error);
    }
);

// Helper functions
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function refreshCsrfToken() {
    try {
        await axios.get('/sanctum/csrf-cookie');
    } catch (e) {
        window.location.reload();
    }
}

// Make axios globally available (optional)
window.axios = axios;

export { axios, getCsrfToken, refreshCsrfToken };