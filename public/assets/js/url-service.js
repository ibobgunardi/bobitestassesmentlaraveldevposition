// public/js/url-service.js
class UrlService {
    constructor() {
        this.baseUrl = window.AppConfig?.baseUrl || document.location.origin;
        this.currentUrl = window.AppConfig?.currentUrl || window.location.href;
        this.assetUrl = window.AppConfig?.assetUrl || this.baseUrl;
    }
    
    /**
     * Get the application's base URL
     * @returns {string} The base URL
     */
    getBaseUrl() {
        return this.baseUrl;
    }
    
    /**
     * Create a fully qualified URL that preserves SEO context
     * @param {string} path - The path to append to the base URL
     * @param {Object} params - Query parameters to include
     * @returns {string} The complete URL
     */
    url(path, params = {}) {
        // Ensure path starts with a slash but doesn't duplicate one
        const cleanPath = path.startsWith('/') ? path : `/${path}`;
        
        // Start with base URL and append path
        let url = this.baseUrl + cleanPath;
        
        // Add query parameters if provided
        const queryString = this._buildQueryString(params);
        if (queryString) {
            url += (url.includes('?') ? '&' : '?') + queryString;
        }
        
        return url;
    }
    
    /**
     * Create a URL for static assets
     * @param {string} path - Path to the asset
     * @returns {string} The asset URL
     */
    asset(path) {
        const cleanPath = path.startsWith('/') ? path.substring(1) : path;
        return `${this.assetUrl}/${cleanPath}`;
    }
    
    /**
     * Build a query string from an object of parameters
     * @param {Object} params - The parameters
     * @returns {string} The query string without the leading '?'
     * @private
     */
    _buildQueryString(params) {
        return Object.keys(params)
            .filter(key => params[key] !== null && params[key] !== undefined)
            .map(key => {
                return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
            })
            .join('&');
    }
}

// Initialize and expose globally
window.urlService = new UrlService();