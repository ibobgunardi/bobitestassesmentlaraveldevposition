/**
 * Authentication helper functions
 * 
 * Provides consistent methods for handling authentication across the application
 */

const Auth = {
    /**
     * Get the authentication token from local storage
     * 
     * @returns {string|null} The authentication token or null if not found
     */
    getToken() {
        return localStorage.getItem('auth_token');
    },

    /**
     * Set the authentication token in local storage
     * 
     * @param {string} token - The token to store
     */
    setToken(token) {
        localStorage.setItem('auth_token', token);
    },

    /**
     * Remove the authentication token from local storage
     */
    removeToken() {
        localStorage.removeItem('auth_token');
    },

    /**
     * Get the authenticated user from local storage
     * 
     * @returns {Object|null} The user object or null if not found
     */
    getUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },

    /**
     * Set the authenticated user in local storage
     * 
     * @param {Object} user - The user object to store
     */
    setUser(user) {
        localStorage.setItem('user', JSON.stringify(user));
    },

    /**
     * Remove the authenticated user from local storage
     */
    removeUser() {
        localStorage.removeItem('user');
    },

    /**
     * Check if the user is authenticated
     * 
     * @returns {boolean} True if the user is authenticated, false otherwise
     */
    isAuthenticated() {
        return !!this.getToken();
    },

    /**
     * Log the user out by removing token and user data
     */
    logout() {
        this.removeToken();
        this.removeUser();
    },

    /**
     * Get headers for API requests including the authentication token
     * 
     * @param {boolean} includeContentType - Whether to include the Content-Type header
     * @returns {Object} Headers object for fetch API
     */
    getHeaders(includeContentType = true) {
        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        };

        if (includeContentType) {
            headers['Content-Type'] = 'application/json';
        }

        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        return headers;
    },

    /**
     * Make an authenticated API request
     * 
     * @param {string} url - The API endpoint URL
     * @param {Object} options - The fetch API options
     * @returns {Promise} The fetch promise
     */
    async apiRequest(url, options = {}) {
        const defaultOptions = {
            headers: this.getHeaders()
        };
        
        const requestOptions = { ...defaultOptions, ...options };
        
        // If options contains a body object, stringify it
        if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            requestOptions.body = JSON.stringify(options.body);
        }
        
        // If it's a FormData object, remove the Content-Type header (browser will set it with the boundary)
        if (options.body instanceof FormData) {
            delete requestOptions.headers['Content-Type'];
        }

        try {
            const response = await fetch(url, requestOptions);
            
            // If the response is 401 Unauthorized, clear the auth data and redirect to login
            if (response.status === 401) {
                this.logout();
                window.location.href = '/login?session=expired';
                return Promise.reject('Authentication expired');
            }
            
            return response;
        } catch (error) {
            console.error('API request error:', error);
            return Promise.reject(error);
        }
    },

    /**
     * Check if the current user has a specific role
     * 
     * @param {string} role - The role to check
     * @returns {boolean} True if the user has the role, false otherwise
     */
    hasRole(role) {
        const user = this.getUser();
        return user && user.role === role;
    },

    /**
     * Refresh the authentication token
     * 
     * @returns {Promise} A promise with the refresh result
     */
    async refreshToken() {
        if (!this.isAuthenticated()) {
            return Promise.reject('Not authenticated');
        }

        try {
            const response = await this.apiRequest('/api/auth/refresh-token', {
                method: 'POST'
            });
            
            const data = await response.json();
            
            if (data.success && data.token) {
                this.setToken(data.token);
                return data;
            }
            
            return Promise.reject('Failed to refresh token');
        } catch (error) {
            console.error('Token refresh error:', error);
            return Promise.reject(error);
        }
    }
};

export default Auth;